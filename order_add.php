<?php
include 'config/db.php';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ส่วนนี้คือการบันทึกข้อมูล (แทน order_add_save.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cus_id = $_POST['cus_id'] ?? '';
    $order_date = $_POST['order_date'] ?? '';

    if (empty($cus_id) || empty($order_date)) {
        die("ข้อมูลไม่ครบ กรุณาระบุให้ครบ");
    }

    $sql_get_max = "SELECT MAX(ORDER_NO) AS max_order_no FROM H_ORDER";
    $result_max = $conn->query($sql_get_max);
    $row_max = $result_max->fetch_assoc();

    $new_order_no = $row_max['max_order_no'] ? str_pad($row_max['max_order_no'] + 1, 5, '0', STR_PAD_LEFT) : '00001';

    $sql_insert = "INSERT INTO H_ORDER (ORDER_NO, CUS_ID, ORDER_DATE) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("sss", $new_order_no, $cus_id, $order_date);

    if ($stmt->execute()) {
        header("Location: order_detail_add.php?order_no=$new_order_no");
        exit;
    } else {
        echo "ผิดพลาดในการเพิ่มข้อมูล: " . $stmt->error;
    }
    $stmt->close();
}

// ดึงข้อมูลลูกค้า
$sql_cus = "SELECT CUS_ID, CUS_NAME FROM CUS_NAME";
$result_cus = $conn->query($sql_cus);

// วันที่ปัจจุบันรูปแบบ DD/MM/YYYY
$current_date = date("d/m/Y");
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>การบันทึก/แก้ไข การสั่งซื้อสินค้า</title>
<style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background-color: #f7f7f7;
    }
    h2 { margin-bottom: 10px; }
    form {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      width: 400px;
      margin: auto;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    label {
      display: block;
      margin-bottom: 6px;
      color: #333;
    }
    select, input[type="text"] {
      width: 100%;
      padding: 8px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .btn {
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      font-size: 14px;
      cursor: pointer;
      margin-right: 8px;
    }
    .btn-save { background-color: #28a745; color: #fff; }
    .btn-cancel { background-color: #dc3545; color: #fff; }
</style>
</head>
<body>

<h2>การบันทึก/แก้ไข การสั่งซื้อสินค้า</h2>

<form action="" method="post" onsubmit="return convertDateFormat()">
    <h3>การเพิ่มคำสั่งซื้อสินค้า</h3>
    <label for="cus_id">รหัสลูกค้า :</label>
    <select name="cus_id" id="cus_id" required onchange="updateCusName(this.value)">
        <option value="">-- เลือกลูกค้า --</option>
        <?php
        if ($result_cus->num_rows > 0) {
            while($row = $result_cus->fetch_assoc()) {
                echo "<option value='{$row['CUS_ID']}' data-name='{$row['CUS_NAME']}'>{$row['CUS_ID']}</option>";
            }
        }
        ?>
    </select>

    <label>ชื่อลูกค้า :</label>
    <input type="text" id="cus_name" disabled>

    <label for="order_date">วันที่สั่งสินค้า (DD/MM/YYYY) :</label>
    <input type="text" name="order_date_display" id="order_date_display" value="<?php echo $current_date; ?>" required placeholder="DD/MM/YYYY">
    <input type="hidden" name="order_date" id="order_date">

    <button type="submit" class="btn btn-save">บันทึกและเพิ่มรายการสินค้าต่อ</button>
    <a href="order.php" class="btn btn-cancel">ยกเลิก</a>
</form>

<script>
// แสดงชื่อเมื่อลูกค้าเปลี่ยน
function updateCusName(cus_id) {
    var select = document.getElementById("cus_id");
    var selected = select.options[select.selectedIndex];
    document.getElementById("cus_name").value = selected.getAttribute("data-name") || "";
}

// แปลงวันที่จาก DD/MM/YYYY เป็น YYYY-MM-DD ก่อนส่งไป PHP
function convertDateFormat() {
    var dateDisplay = document.getElementById('order_date_display').value;
    var parts = dateDisplay.split('/');
    if (parts.length === 3) {
        var newDate = parts[2] + '-' + parts[1] + '-' + parts[0]; // YYYY-MM-DD
        document.getElementById('order_date').value = newDate;
        return true;
    } else {
        alert('กรุณาใส่วันที่ในรูปแบบ DD/MM/YYYY');
        return false;
    }
}
</script>

</body>
</html>

<?php $conn->close(); ?>
