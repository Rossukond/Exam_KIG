<?php
include 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $order_no = $_POST['order_no'];
    $goods_id = $_POST['goods_id'];
    $ord_date = $_POST['ord_date'];
    $fin_date = $_POST['fin_date'];
    $amount = floatval($_POST['amount']);

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // ดึง COST_UNIT
    $sqlPrice = "SELECT COST_UNIT FROM D_ORDER WHERE ORDER_NO = ? AND Goods_id = ?";
    $stmtPrice = $conn->prepare($sqlPrice);
    $stmtPrice->bind_param("ss", $order_no, $goods_id);
    $stmtPrice->execute();
    $result = $stmtPrice->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        die("ไม่พบรายการสินค้าในระบบ");
    }

    $cost_unit = floatval($row['COST_UNIT']);
    $total_price = $cost_unit * $amount;

    // อัปเดตข้อมูล
    $sqlUpdate = "UPDATE D_ORDER 
                  SET Ord_date = ?, Fin_date = ?, Amount = ?, TOT_PRC = ?
                  WHERE ORDER_NO = ? AND Goods_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ssddss", $ord_date, $fin_date, $amount, $total_price, $order_no, $goods_id);

    if ($stmtUpdate->execute()) {
        header("Location: order_edit.php?order_no=" . urlencode($order_no));
        exit;
    } else {
        echo "เกิดข้อผิดพลาดในการอัปเดต: " . $conn->error;
        exit;
    }
}

// ==== หากไม่ใช่ POST ให้แสดงฟอร์ม ====
$order_no = $_GET['order_no'] ?? '';
$goods_id = $_GET['goods_id'] ?? '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Header
$sqlHeader = "SELECT H.ORDER_NO, H.CUS_ID, C.CUS_NAME, H.ORDER_DATE
              FROM H_ORDER H
              JOIN CUS_NAME C ON H.CUS_ID = C.CUS_ID
              WHERE H.ORDER_NO = ?";
$stmtHeader = $conn->prepare($sqlHeader);
$stmtHeader->bind_param("s", $order_no);
$stmtHeader->execute();
$header = $stmtHeader->get_result()->fetch_assoc();

// Detail
$sqlDetail = "SELECT D.*, G.Goods_name
              FROM D_ORDER D
              JOIN GOODS_NAME G ON D.Goods_id = G.Goods_id
              WHERE D.ORDER_NO = ? AND D.Goods_id = ?";
$stmtDetail = $conn->prepare($sqlDetail);
$stmtDetail->bind_param("ss", $order_no, $goods_id);
$stmtDetail->execute();
$detail = $stmtDetail->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขรายการสินค้า</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f9f9f9; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { padding: 10px; border-bottom: 1px solid #ccc; }
        th { background: #eee; text-align: left; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="date"], input[type="number"] {
            width: 100%; padding: 8px; box-sizing: border-box;
        }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;}
        .btn-submit { color: white; 
            text-decoration: none; display: inline-block; margin-right: 20px;}
        .btn-back {color: white; 
            text-decoration: none; padding: 10px 20px; display: inline-block; border-radius: 4px; }
    </style>
</head>
<body>

<h2>การบันทึก/แก้ไข การสั่งซื้อสินค้า</h2>
<p><strong>สถานะ:</strong> แก้ไขรายการส่วน Detail การรับคำสั่งซื้อสินค้า</p>

<form method="post">
    <input type="hidden" name="order_no" value="<?= $order_no ?>">
    <input type="hidden" name="goods_id" value="<?= $goods_id ?>">

    <h3>ข้อมูลคำสั่งซื้อ</h3>
    <table>
        <tr><th>รหัสลูกค้า</th><td><?= $header['CUS_ID'] ?></td><th>ชื่อลูกค้า</th><td><?= $header['CUS_NAME'] ?></td></tr>
        <tr><th>วันที่สั่ง</th><td><?= date("d/m/Y", strtotime($header['ORDER_DATE'])) ?></td><th>เลขที่คำสั่งซื้อ</th><td><?= $header['ORDER_NO'] ?></td></tr>
        <tr><th>รหัสสินค้า</th><td colspan="3"><?= $detail['Goods_id'] ?> - <?= $detail['Goods_name'] ?></td></tr>
    </table>

    <h3>แก้ไขข้อมูลสินค้า</h3>
    <div class="form-group">
        <label for="ord_date">วันกำหนดส่ง</label>
        <input type="date" name="ord_date" value="<?= $detail['Ord_date'] ?>">
    </div>
    <div class="form-group">
        <label for="fin_date">วันที่ส่งสินค้าจริง</label>
        <input type="date" name="fin_date" value="<?= $detail['Fin_date'] ?>">
    </div>
    <div class="form-group">
        <label for="amount">จำนวนสั่ง</label>
        <input type="number" name="amount" id="amount" value="<?= $detail['Amount'] ?>" step="0.01" required oninput="updateTotalPrice()">
    </div>
    <div class="form-group">
        <label>ราคา/หน่วย</label>
        <input type="text" id="unit_price" value="<?= number_format($detail['COST_UNIT'], 2, '.', '') ?>" readonly>
    </div>
    <div class="form-group">
        <label>ราคารวม</label>
        <input type="text" id="total_price" value="<?= number_format($detail['TOT_PRC'], 2) ?>" readonly>
    </div>

    <div class="btn" style="position: fixed;">
    <button type="submit" class="btn btn-submit" style="background-color: #007bff;">บันทึกข้อมูล</button>
    <a href="order_edit.php?order_no=<?= $order_no ?>" class="btn-back" style="background-color: #6c757d; ">กลับหน้าหลัก</a>
    </div>
</form>

<script>
function updateTotalPrice() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
    const total = amount * unitPrice;
    document.getElementById('total_price').value = total.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
</script>

</body>
</html>
<?php $conn->close(); ?>
