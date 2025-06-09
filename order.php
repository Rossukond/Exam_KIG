<?php
include 'config/db.php';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ดึงข้อมูลคำสั่งซื้อสินค้า
$sql = "SELECT 
            H.CUS_ID, 
            C.CUS_NAME, 
            H.ORDER_NO, 
            COUNT(*) AS CNT, 
            SUM(D.AMOUNT) AS AMOUNT
        FROM H_ORDER H
        JOIN D_ORDER D ON H.ORDER_NO = D.ORDER_NO
        JOIN CUS_NAME C ON H.CUS_ID = C.CUS_ID
        GROUP BY H.CUS_ID, C.CUS_NAME, H.ORDER_NO";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>แสดงข้อมูลการสั่งซื้อสินค้า</title>
<style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background-color: #f7f7f7;
    }
    h2 { margin-bottom: 5px; text-align: center; }
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
    th {
      text-align: left;
      padding: 10px;
      color: #555;
      border-bottom: 1px solid #ddd;
    }
    td {
      padding: 10px;
      vertical-align: middle;
      background: #fff;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .text-right {
      text-align: right;
    } 
    .actions a {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 4px;
      font-size: 14px;
      color: white;
      text-decoration: none;
    }
    .btn-edit {
      background-color:rgb(192, 131, 39);
    }
    .btn-delete {
      background-color:rgb(124, 47, 54);
    }
</style>
</head>
<body>

<h2>แสดงข้อมูลการสั่งซื้อสินค้า</h2>

<div class="top-bar">
    <a href="order_add.php" class="btn-add">เพิ่มข้อมูลการสั่งซื้อสินค้า</a>
    <a href="dashboard.php" class="btn-add" style="background-color:#6c757d;">กลับหน้าหลัก</a>
</div>

<table>
    <thead>
    <tr>
        <th>รหัสลูกค้า</th>
        <th>ชื่อลูกค้า</th>
        <th>เลขที่คำสั่งซื้อ</th>
        <th>จำนวนรายการที่สั่ง</th>
        <th>จำนวนที่สั่ง</th>
        <th>การกระทำ</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $totalCnt = 0;
    $totalAmount = 0;

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $totalCnt += $row['CNT'];
            $totalAmount += $row['AMOUNT'];

            echo "<tr>
                    <td>{$row['CUS_ID']}</td>
                    <td>{$row['CUS_NAME']}</td>
                    <td class='text-right'>{$row['ORDER_NO']}</td>
                    <td class='text-right'>" . number_format($row['CNT']) . "</td>
                    <td class='text-right'>" . number_format($row['AMOUNT']) . "</td>
                    <td class='actions'>
                        <a href='order_edit.php?order_no={$row['ORDER_NO']}' class='btn-edit'>แก้ไข</a>
                        <a href='order_delete.php?order_no={$row['ORDER_NO']}' class='btn-delete' onclick=\"return confirm('ต้องการลบข้อมูลนี้หรือไม่?')\">ลบ</a>
                    </td>
                  </tr>";
        }

        // แสดงผลรวมท้ายตาราง
        echo "<tr style='font-weight: bold; background-color: #f0f0f0;'>
                <td colspan='3' style='text-align: right;'>รวม</td>
                <td class='text-right'>" . number_format($totalCnt) . "</td>
                <td class='text-right'>" . number_format($totalAmount) . "</td>
                <td></td>
              </tr>";

    } else {
        echo "<tr><td colspan='6' style='text-align:center;'>ไม่พบข้อมูลการสั่งซื้อสินค้า</td></tr>";
    }
    ?>
    </tbody>
</table>

</body>
</html>

<?php $conn->close(); ?>
