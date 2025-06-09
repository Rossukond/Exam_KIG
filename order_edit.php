<?php
include 'config/db.php';

$order_no = $_GET['order_no'] ?? '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get header info
$sqlHeader = "SELECT H.ORDER_NO, H.CUS_ID, C.CUS_NAME, H.ORDER_DATE
              FROM H_ORDER H
              JOIN CUS_NAME C ON H.CUS_ID = C.CUS_ID
              WHERE H.ORDER_NO = ?";
$stmtHeader = $conn->prepare($sqlHeader);
$stmtHeader->bind_param("s", $order_no);
$stmtHeader->execute();
$header = $stmtHeader->get_result()->fetch_assoc();

// Get detail info
$sqlDetail = "SELECT D.Goods_id, G.Goods_name, D.Ord_date, D.Fin_date, 
                     D.Amount, D.COST_UNIT, D.TOT_PRC
              FROM D_ORDER D
              JOIN GOODS_NAME G ON D.Goods_id = G.Goods_id
              WHERE D.ORDER_NO = ?";
$stmtDetail = $conn->prepare($sqlDetail);
$stmtDetail->bind_param("s", $order_no);
$stmtDetail->execute();
$details = $stmtDetail->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขคำสั่งซื้อสินค้า</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f9f9f9;
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }
        th {
            background-color: #eee;
        }
        .btn {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            color: white;
            margin-right: 5px;
        }
        .btn-edit {
            background-color: #c08327;
        }
        .btn-delete {
            background-color: #7c2f36;
        }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            margin-top: 20px;
            margin-right: 20px;
        }
    </style>
</head>
<body>

<h2>การบันทึก/แก้ไข การสั่งซื้อสินค้า</h2>
<p><strong>สถานะ:</strong> แก้ไขส่วน Detail การรับคำสั่งซื้อสินค้า</p>

<h3>ข้อมูล Header</h3>
<table>
    <tr><th>รหัสลูกค้า</th><td><?= $header['CUS_ID'] ?></td><th>ชื่อลูกค้า</th><td><?= $header['CUS_NAME'] ?></td></tr>
    <tr><th>วันที่สั่ง</th><td><?= date("d/m/Y", strtotime($header['ORDER_DATE'])) ?></td><th>เลขที่คำสั่งซื้อ</th><td><?= $header['ORDER_NO'] ?></td></tr>
</table>

<h3>รายละเอียดสินค้า</h3>
<table>
    <thead>
        <tr>
            <th>รหัสสินค้า</th>
            <th>รายละเอียด</th>
            <th>วันกำหนดส่ง</th>
            <th>วันที่ส่งสินค้าจริง</th>
            <th>จำนวนสั่ง</th>
            <th>ราคา/หน่วย</th>
            <th>ราคารวม</th>
            <th>Action</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $details->fetch_assoc()): ?>
        <tr>
            <td><?= $row['Goods_id'] ?></td>
            <td><?= $row['Goods_name'] ?></td>
            <td><?= date("d/m/Y", strtotime($row['Ord_date'])) ?></td>
            <td><?= date("d/m/Y", strtotime($row['Fin_date'])) ?></td>
            <td style="text-align:right;"><?= number_format($row['Amount'], 2) ?></td>
            <td style="text-align:right;"><?= number_format($row['COST_UNIT'], 2) ?></td>
            <td style="text-align:right;"><?= number_format($row['TOT_PRC'], 2) ?></td>
            <td><a class="btn btn-edit" href="order_detail_edit.php?order_no=<?= $order_no ?>&goods_id=<?= $row['Goods_id'] ?>">แก้ไข</a></td>
            <td><a class="btn btn-delete" href="order_detail_delete.php?order_no=<?= $order_no ?>&goods_id=<?= $row['Goods_id'] ?>" onclick="return confirm('ต้องการลบรายการสินค้านี้หรือไม่?')">ลบ</a></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div style="position: fixed; right: 20px;">
<a href="order.php" class="btn btn-back" style="background-color: #007bff;">กลับไปยังหน้าจอแสดงรายการสั่งสินค้า</a>
<a href="dashboard.php" class="btn btn-back" style="background-color:#6c757d;">กลับหน้าหลัก</a>
</div>

</body>
</html>
<?php $conn->close(); ?>
