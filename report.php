<?php
include("config/db.php");
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

$gdoc_date1 = isset($_POST['gdoc_date1']) ? $_POST['gdoc_date1'] : date('Y-m-d');
$gdoc_date2 = isset($_POST['gdoc_date2']) ? $_POST['gdoc_date2'] : date('Y-m-d');

$sum_amount = 0;
$sum_total = 0;

if(isset($_POST['show'])) {
    $sql = "SELECT h.Order_no, h.Cus_id, c.Cus_name, d.Goods_id, g.Goods_name,
                   d.Ord_date, d.Fin_date, d.Amount, d.COST_UNIT, d.TOT_PRC
            FROM h_order h
            INNER JOIN d_order d ON h.Order_no = d.Order_no
            INNER JOIN cus_name c ON h.Cus_id = c.Cus_id
            INNER JOIN goods_name g ON d.Goods_id = g.Goods_id
            WHERE d.Ord_date BETWEEN ? AND ?
            ORDER BY d.Ord_date";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $gdoc_date1, $gdoc_date2);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>รายงานกำหนดส่งสินค้า</title>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f4f4f4;
            padding: 30px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        form {
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        input[type="date"], input[type="submit"], input[type="button"] {
            padding: 8px 15px;
            margin: 10px 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            transition: 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        input[type="button"] {
            background-color: #2196F3;
            color: white;
            cursor: pointer;
            transition: 0.3s;
        }
        input[type="button"]:hover {
            background-color: #1976D2;
        }
        table {
            margin: 0 auto;
            border-collapse: collapse;
            width: 90%;
            background-color: #fff;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        th {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {background-color: #f9f9f9;}
        tr:hover {background-color: #f1f1f1;}
        tr.summary {
            background-color: #FFFFCC;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>รายงานกำหนดส่งสินค้า</h2>

<form method="post" action="report.php">
    วันที่กำหนดส่งตามแผน: 
    <input type="date" name="gdoc_date1" value="<?= $gdoc_date1 ?>">
    ถึงวันที่:
    <input type="date" name="gdoc_date2" value="<?= $gdoc_date2 ?>">
    <input type="submit" name="show" value="แสดง">
</form>

<?php if(isset($result)) { ?>
<br>
<table>
    <tr>
        <th>ลำดับ</th>
        <th>รายละเอียดลูกค้า</th>
        <th>รายละเอียดสินค้า</th>
        <th>วันที่สั่ง</th>
        <th>เลขที่สั่ง</th>
        <th>วันกำหนดส่ง</th>
        <th>จำนวน</th>
        <th>ราคา/หน่วย</th>
        <th>ราคารวม</th>
    </tr>

    <?php
    $i = 1;
    while($row = $result->fetch_assoc()) {
        $cus_detail = $row['Cus_id'] . " : " . $row['Cus_name'];
        $goods_detail = $row['Goods_id'] . " : " . $row['Goods_name'];
        echo "<tr>";
        echo "<td align='center'>".$i."</td>";
        echo "<td>".$cus_detail."</td>";
        echo "<td>".$goods_detail."</td>";
        echo "<td>".$row['Ord_date']."</td>";
        echo "<td>".$row['Order_no']."</td>";
        echo "<td>".$row['Fin_date']."</td>";
        echo "<td align='right'>".number_format($row['Amount'],2)."</td>";
        echo "<td align='right'>".number_format($row['COST_UNIT'],2)."</td>";
        echo "<td align='right'>".number_format($row['TOT_PRC'],2)."</td>";
        echo "</tr>";

        $sum_amount += $row['Amount'];
        $sum_total += $row['TOT_PRC'];
        $i++;
    }

    // สรุปยอดรวม
    echo "<tr class='summary'>";
    echo "<td colspan='6' align='right'>รวม</td>";
    echo "<td align='right'>".number_format($sum_amount,2)."</td>";
    echo "<td></td>";
    echo "<td align='right'>".number_format($sum_total,2)."</td>";
    echo "</tr>";
    ?>
</table>
<?php } ?>

<br>
<input type="button" value="พิมพ์รายงาน" onclick="window.print();">
<input type="button" value="ออก" onclick="window.location='dashboard.php';">

</body>
</html>
