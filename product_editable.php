<?php
include 'config/db.php';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
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
    body { font-family: Arial, sans-serif; padding: 20px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
    th { background-color: #f2f2f2; }
    tr:hover { background-color: #f9f9f9; }
    input[type="text"], input[type="number"] { width: 100%; box-sizing: border-box; }
    button { padding: 5px 10px; margin: 0 2px; }
  </style>
</head>
<body>

<h2>ข้อมูลสินค้า</h2>
<p>ข้อมูลสินค้า ณ วันที่ <?= date("d/m/Y") ?></p>

<table>
  <tr>
    <th>รหัสสินค้า</th>
    <th>ชื่อสินค้า</th>
    <th>ราคาต่อหน่วย (บาท)</th>
    <th>การจัดการ</th>
  </tr>
  <?php while($row = $result->fetch_assoc()): ?>
    <tr data-id="<?= $row["Goods_id"] ?>">
      <td>
        <input type="text" name="Goods_id" value="<?= htmlspecialchars($row["Goods_id"]) ?>" disabled maxlength="10">
      </td>
      <td>
        <input type="text" name="Goods_name" value="<?= htmlspecialchars($row["Goods_name"]) ?>" disabled>
      </td>
      <td>
        <input type="number" name="cost_unit" value="<?= number_format($row["cost_unit"], 2) ?>" step="0.01" min="0.01" max="999999.99" disabled>
      </td>
      <td>
        <button onclick="enableEdit(this)">✏️ แก้ไข</button>
        <button onclick="saveRow(this)" style="display:none;">💾 บันทึก</button>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<script>
function enableEdit(btn) {
  const row = btn.closest("tr");
  row.querySelectorAll("input").forEach(i => i.disabled = false);
  row.querySelector('button[onclick^="saveRow"]').style.display = "inline-block";
  btn.style.display = "none";
}

function saveRow(btn) {
  const row = btn.closest("tr");
  const Goods_id = row.querySelector('[name="Goods_id"]').value.trim();
  const Goods_name = row.querySelector('[name="Goods_name"]').value.trim();
  const cost_unit = row.querySelector('[name="cost_unit"]').value.trim();

  fetch("update_product.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `Goods_id=${encodeURIComponent(Goods_id)}&field=Goods_name&value=${encodeURIComponent(Goods_name)}`
  })
  .then(() => {
    return fetch("update_product.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `Goods_id=${encodeURIComponent(Goods_id)}&field=cost_unit&value=${encodeURIComponent(cost_unit)}`
    });
  })
  .then(() => {
    row.querySelectorAll("input").forEach(i => i.disabled = true);
    row.querySelector('button[onclick^="enableEdit"]').style.display = "inline-block";
    btn.style.display = "none";
  })
  .catch(() => alert("เกิดข้อผิดพลาด"));
}
</script>

</body>
</html>

<?php $conn->close(); ?>
