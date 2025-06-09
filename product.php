<?php
include 'config/db.php';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ✅ Handle insert from modal POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["Goods_id"])) {
    $id = trim($_POST["Goods_id"]);
    $name = trim($_POST["Goods_name"]);
    $cost = floatval($_POST["cost_unit"]);

    if (strlen($id) != 10) {
        http_response_code(400); echo "รหัสสินค้าต้องมีความยาว 10 หลักเท่านั้น"; exit;
    }
    if (empty($name)) {
        http_response_code(400); echo "ชื่อสินค้าห้ามว่าง"; exit;
    }
    if ($cost <= 0 || $cost > 999999.99) {
        http_response_code(400); echo "ราคาต้อง > 0 และ ≤ 999999.99"; exit;
    }

    // ถ้ามี original_id แสดงว่าเป็นการแก้ไข
    if (isset($_POST["original_id"])) {
        $original_id = $_POST["original_id"];

        // ตรวจสอบกรณีที่เปลี่ยนรหัสสินค้าแล้วชนกับของเดิม
        if ($id !== $original_id) {
            $check = $conn->prepare("SELECT Goods_id FROM goods_name WHERE Goods_id = ?");
            $check->bind_param("s", $id);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                http_response_code(409); echo "รหัสสินค้า '$id' มีอยู่แล้ว"; exit;
            }
        }

        // อัปเดตข้อมูล
        $stmt = $conn->prepare("UPDATE goods_name SET Goods_id = ?, Goods_name = ?, cost_unit = ? WHERE Goods_id = ?");
        $stmt->bind_param("ssds", $id, $name, $cost, $original_id);
        $stmt->execute();
        echo "success";
        exit;
    } else {
        // เพิ่มใหม่
        $check = $conn->prepare("SELECT Goods_id FROM goods_name WHERE Goods_id = ?");
        $check->bind_param("s", $id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            http_response_code(409); echo "รหัสสินค้านี้มีอยู่แล้ว กรุณาใช้รหัสอื่น"; exit;
        }

        $stmt = $conn->prepare("INSERT INTO goods_name (Goods_id, Goods_name, cost_unit) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $id, $name, $cost);
        $stmt->execute();
        echo "success";
        exit;
    }
}


if (isset($_GET["delete"])) {
    $delete_id = $_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM goods_name WHERE Goods_id = ?");
    $stmt->bind_param("s", $delete_id);
    $stmt->execute();
    header("Location: product.php");
    exit;
}

$sql = "SELECT * FROM goods_name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ข้อมูลสินค้า</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background-color: #f7f7f7;
    }
    h2 { margin-bottom: 5px; }
    .top-bar {
      display: flex;
      justify-content: space-between;
      margin: 15px 0;
    }
    .btn-add {
      display: inline-block;
      padding: 8px 16px;
      background-color: #007bff;
      color: white;
      text-decoration: none;
      border: none;
      border-radius: 4px;
      font-size: 14px;
      cursor: pointer;
    }
    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 8px;
      background: white;
    }
    th { text-align: left; padding: 10px; color: #555; }
    td { padding: 10px; vertical-align: middle; }
    input[type="text"], input[type="number"] {
      border: none;
      border-bottom: 1px solid #ccc;
      background: transparent;
      padding: 6px 4px;
      width: 100%;
    }
    input:disabled { background: transparent; color: #333; }
    button.btn-link {
      background: none;
      border: none;
      color: #007bff;
      font-size: 14px;
      cursor: pointer;
      padding: 4px 8px;
    }
    .btn-save {
      padding: 4px 8px;
      font-size: 14px;
      background-color: #f0f8ff;
      color: #007bff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .btn-delete {
      color: red;
      text-decoration: none;
      font-size: 14px;
    }
    #addModal {
      display: none;
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.5);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }
    #addModal > div {
      background: white;
      padding: 20px;
      border-radius: 8px;
      width: 400px;
      max-width: 90%;
    }
  </style>
</head>
<body>

<h2>ข้อมูลสินค้า</h2>
<p>ข้อมูลสินค้า ณ วันที่ <?= date("d/m/Y") ?></p>

<div class="top-bar">
  <a href="dashboard.php" class="btn-add" style="background-color: #6c757d;">🏠 หน้าหลัก</a>
  <button class="btn-add" onclick="openModal()">➕ เพิ่มสินค้า</button>
</div>

<table>
  <tr>
    <th>รหัสสินค้า</th>
    <th>ชื่อสินค้า</th>
    <th>ราคาต่อหน่วย (บาท)</th>
    <th>การจัดการ</th>
  </tr>
  <?php while($row = $result->fetch_assoc()): ?>
    <tr data-id="<?= $row["Goods_id"] ?>">
      <td><input type="text" value="<?= htmlspecialchars($row["Goods_id"]) ?>" disabled></td>
      <td><input type="text" value="<?= htmlspecialchars($row["Goods_name"]) ?>" disabled></td>
      <td><input type="number" value="<?= number_format($row["cost_unit"], 2, '.', '') ?>" disabled></td>
      <td>
        <button class="btn-link" onclick="this.closest('tr').querySelectorAll('input').forEach(i=>i.disabled=false); this.style.display='none'; this.nextElementSibling.style.display='inline-block';">✏️ Edit</button>
        <button class="btn-save" style="display:none;" onclick="saveRow(this)">💾 Save</button>
        <a href="?delete=<?= $row["Goods_id"] ?>" onclick="return confirm('ลบรายการนี้หรือไม่?')" class="btn-delete">❌</a>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<!-- Modal ฟอร์มเพิ่มสินค้า -->
<div id="addModal">
  <div>
    <h3>เพิ่มสินค้าใหม่</h3>
    <form id="addForm">
      <label>รหัสสินค้า (10 หลัก)</label>
      <input type="text" name="Goods_id" maxlength="10" required>
      <label>ชื่อสินค้า</label>
      <input type="text" name="Goods_name" required>
      <label>ราคาต่อหน่วย</label>
      <input type="number" name="cost_unit" step="0.01" min="0.01" max="999999.99" required>
      <div style="text-align:right; margin-top:10px;">
        <button type="button" onclick="closeModal()">ยกเลิก</button>
        <button type="submit">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<script>

function saveRow(btn) {
  const row = btn.closest("tr");
  const originalId = row.dataset.id;
  const id = row.querySelector("input[type='text']").value;
  const name = row.querySelectorAll("input")[1].value;
  const cost = row.querySelectorAll("input")[2].value;

  fetch("product.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `Goods_id=${encodeURIComponent(id)}&Goods_name=${encodeURIComponent(name)}&cost_unit=${encodeURIComponent(cost)}&original_id=${encodeURIComponent(originalId)}`
  })
  .then(res => res.text())
  .then(response => {
    if (response === "success") {
      alert("บันทึกสำเร็จ");
      window.location.reload();
    } else {
      alert("ผิดพลาด: " + response);
    }
  })
  .catch(err => alert("Error: " + err));
}



function openModal() {
  document.getElementById("addModal").style.display = "flex";
}
function closeModal() {
  document.getElementById("addModal").style.display = "none";
}
document.getElementById("addForm").addEventListener("submit", function(e) {
  e.preventDefault();
  const form = e.target;
  const data = new URLSearchParams(new FormData(form)).toString();
  fetch("product.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: data
  })
  .then(res => {
    if (res.ok) {
      alert("เพิ่มสินค้าสำเร็จ");
      window.location.reload();
    } else {
      return res.text().then(t => { throw new Error(t); });
    }
  })
  .catch(err => alert("เกิดข้อผิดพลาด: " + err.message));
});
</script>

</body>
</html>
