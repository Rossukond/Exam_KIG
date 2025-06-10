<?php
include("config/db.php");
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

$gdoc_date1 = isset($_POST['gdoc_date1']) ? $_POST['gdoc_date1'] : '';
$gdoc_date2 = isset($_POST['gdoc_date2']) ? $_POST['gdoc_date2'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['process'])) {
        if (empty($gdoc_date1) || empty($gdoc_date2)) {
            echo "<script>alert('กรุณาเลือกช่วงวันที่ให้ครบถ้วน'); window.history.back();</script>";
            exit;
        }

        $sql = "SELECT h.Cus_id, d.Goods_id, h.Order_Date AS Doc_date, d.Ord_date, d.Fin_date, d.Amount, d.TOT_PRC
                FROM h_order h
                JOIN d_order d ON h.Order_no = d.Order_no
                WHERE d.Fin_date BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $gdoc_date1, $gdoc_date2);
        $stmt->execute();
        $result = $stmt->get_result();

        $sys_date = date('Y-m-d H:i:s');
        $insert_count = 0;

        while ($row = $result->fetch_assoc()) {
            $sql_check = "SELECT COUNT(*) AS cnt FROM m_order 
                          WHERE Cus_id = ? AND Goods_id = ? AND Doc_date = ? AND Ord_date = ? AND Fin_date = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("sssss", 
                $row['Cus_id'], 
                $row['Goods_id'], 
                $row['Doc_date'], 
                $row['Ord_date'], 
                $row['Fin_date']
            );
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $row_check = $result_check->fetch_assoc();

            if ($row_check['cnt'] == 0) {
                $sql_insert = "INSERT INTO m_order (Cus_id, Goods_id, Doc_date, Ord_date, Fin_date, Sys_date, Amount, cost_tot)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param(
                    "ssssssdd",
                    $row['Cus_id'],
                    $row['Goods_id'],
                    $row['Doc_date'],
                    $row['Ord_date'],
                    $row['Fin_date'],
                    $sys_date,
                    $row['Amount'],
                    $row['TOT_PRC']
                );
                $stmt_insert->execute();
                $insert_count++;
            }
        }

        // ลบ d_order
        $sql_delete_d = "DELETE FROM d_order WHERE Fin_date BETWEEN ? AND ?";
        $stmt_delete_d = $conn->prepare($sql_delete_d);
        $stmt_delete_d->bind_param("ss", $gdoc_date1, $gdoc_date2);
        $stmt_delete_d->execute();

        // ลบ h_order ที่ไม่มี detail แล้ว
        $sql_delete_h = "DELETE FROM h_order WHERE Order_no NOT IN (SELECT DISTINCT Order_no FROM d_order)";
        $conn->query($sql_delete_h);

        echo "<script>alert('ประมวลผลข้อมูลจำนวน $insert_count รายการเรียบร้อยแล้ว'); window.location='dashboard.php';</script>";
        exit;
    }

    if (isset($_POST['cancel'])) {
        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ประมวลผลข้อมูลสั่งซื้อสินค้า</title>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f9f9f9;
            padding: 30px;
            text-align: center;
        }
        h2 {
            color: #333;
        }
        form {
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            font-weight: bold;
            display: inline-block;
            width: 200px;
            text-align: right;
            margin-right: 10px;
        }
        input[type="datetime-local"] {
            padding: 5px;
            margin: 10px 0;
            width: 250px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            margin: 15px 10px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            transition: 0.3s;
        }
        input[type="button"] {
            padding: 10px 20px;
            margin: 15px 10px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            transition: 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        input[type="button"] {
            background-color: #f44336;
        }
        input[type="button"]:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>

<h2>ประมวลผลข้อมูลสั่งซื้อสินค้า</h2>
<form method="post">
    <div>
        <label>ระหว่างวันที่ส่งสินค้า: </label>
        <input type="datetime-local" name="gdoc_date1" required value="<?= htmlspecialchars($gdoc_date1) ?>">
    </div>
    <div>
        <label>ถึงวันที่: </label>
        <input type="datetime-local" name="gdoc_date2" required value="<?= htmlspecialchars($gdoc_date2) ?>">
    </div>
    <div>
        <input type="submit" name="process" value="ตกลง">
        <input type="button" value="ยกเลิก" onclick="confirmCancel();">
    </div>
</form>

<script>
function confirmCancel() {
    if (confirm('คุณต้องการยกเลิกการประมวลผลหรือไม่?')) {
        window.location.href = 'dashboard.php';
    }
}
</script>


</body>
</html>
