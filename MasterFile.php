<?php
include("config/db.php");
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ดึงข้อมูลจากตาราง m_order พร้อมชื่อสินค้าและชื่อลูกค้า
$sql = "SELECT m.*, c.Cus_name, g.Goods_name 
        FROM m_order m
        LEFT JOIN cus_name c ON m.Cus_id = c.Cus_id
        LEFT JOIN goods_name g ON m.Goods_id = g.Goods_id
        ORDER BY m.Cus_id, m.Goods_id, m.Doc_date";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Master File</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f8f8;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color:rgb(112, 112, 112);
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .total-row {
            font-weight: bold;
            background-color: #ffeb99;
        }
        .btn {
            display: block;
            margin-left: auto;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            background-color: #4287f5;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #2a5dba;
        }
    </style>
</head>
<body>

<h2>รายการข้อมูล Master File (M_ORDER)</h2>

<table>
    <tr>
        <th>ลำดับ</th>
        <th>รหัสลูกค้า : ชื่อลูกค้า</th>
        <th>รหัสสินค้า : รายละเอียดสินค้า</th>
        <th>วันที่สั่งสินค้า</th>
        <th>วันที่กำหนดส่งตามแผน</th>
        <th>วันที่ส่งสินค้าจริง</th>
        <th>System Date</th>
        <th>จำนวนที่สั่ง</th>
        <th>ราคารวม (บาท)</th>
    </tr>
    <?php
    $i = 1;
    $total_amount = 0;
    $total_cost = 0.00;

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>".$i++."</td>";
            echo "<td>".$row['Cus_id']." : ".$row['Cus_name']."</td>";
            echo "<td>".$row['Goods_id']." : ".$row['Goods_name']."</td>";
            echo "<td>".$row['Doc_date']."</td>";
            echo "<td>".$row['Ord_date']."</td>";
            echo "<td>".$row['Fin_date']."</td>";
            echo "<td>".$row['Sys_date']."</td>";
            echo "<td>".number_format($row['Amount'], 2)."</td>";
            echo "<td>".number_format($row['cost_tot'], 2)."</td>";
            echo "</tr>";

            // คิดผลรวม
            $total_amount += $row['Amount'];
            $total_cost += $row['cost_tot'];
        }

        // แสดงผลรวมด้านล่าง
        echo "<tr class='total-row'>";
        echo "<td colspan='7'>รวมทั้งสิ้น</td>";
        echo "<td>".number_format($total_amount, 2)."</td>";
        echo "<td>".number_format($total_cost, 2)."</td>";
        echo "</tr>";

    } else {
        echo "<tr><td colspan='9'>ไม่มีข้อมูลใน Master File</td></tr>";
    }
    ?>
</table>

<br>
<button class="btn" onclick="window.location.href='dashboarsd.php'">กลับหน้าหลัก</button>

</body>
</html>
