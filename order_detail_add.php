<?php
include 'config/db.php';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("เชื่อมต่อฐานข้อมูลล้มเหลว");

$order_no = $_GET['order_no'] ?? '';
$cus_id = $_GET['cus_id'] ?? '';
$order_date = $_GET['order_date'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ถ้ายังไม่มี ORDER_NO แปลว่ายังไม่ได้สร้าง H_ORDER
    if (empty($order_no)) {
        $stmt = $conn->prepare("INSERT INTO H_ORDER (CUS_ID, ORDER_DATE) VALUES (?, ?)");
        $stmt->bind_param("ss", $cus_id, $order_date);
        $stmt->execute();
        $order_no = $conn->insert_id; // สมมุติว่า ORDER_NO เป็น AUTO_INCREMENT
        $stmt->close();

        header("Location: order_detail_add.php?order_no=$order_no&cus_id=$cus_id&order_date=$order_date");
        exit;
    }

    // เพิ่มรายละเอียดรายการสินค้า (D_ORDER)
    $goods_id = $_POST['goods_id'];
    $due_date = $_POST['due_date'];
    $fin_date = $_POST['fin_date'];
    $amount = $_POST['amount'];
    $cost_unit = $_POST['cost_unit'];
    $tot_prc = $_POST['total_price'];

    $stmt = $conn->prepare("INSERT INTO D_ORDER (ORDER_NO, GOODS_ID, ORD_DATE, FIN_DATE, AMOUNT, COST_UNIT, TOT_PRC) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssidd", $order_no, $goods_id, $due_date, $fin_date, $amount, $cost_unit, $tot_prc);
    $stmt->execute();
    $stmt->close();
}

// ดึงข้อมูล H_ORDER
$header = null;
if (!empty($order_no)) {
    $sql_header = "SELECT H.ORDER_NO, H.CUS_ID, C.CUS_NAME, H.ORDER_DATE 
                   FROM H_ORDER H 
                   JOIN CUS_NAME C ON H.CUS_ID = C.CUS_ID 
                   WHERE H.ORDER_NO = '$order_no'";
    $result_header = $conn->query($sql_header);
    $header = $result_header->fetch_assoc();
}

// ดึงรายการสินค้า (สำหรับ dropdown)
$sql_goods = "SELECT GOODS_ID, GOODS_NAME, COST_UNIT FROM GOODS_NAME";
$result_goods = $conn->query($sql_goods);

// ดึงรายการ D_ORDER
$sql_detail = "SELECT D.GOODS_ID, G.GOODS_NAME, D.ORD_DATE, D.FIN_DATE, D.AMOUNT, D.COST_UNIT, D.TOT_PRC
               FROM D_ORDER D
               JOIN GOODS_NAME G ON D.GOODS_ID = G.GOODS_ID
               WHERE D.ORDER_NO = '$order_no'";
$result_detail = $conn->query($sql_detail);

function formatDate($date) {
    if (!$date || $date === '0000-00-00') return '-';
    return date("d/m/Y", strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>การบันทึก/แก้ไข การสั่งซื้อสินค้า</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f7f7f7; }
        h2 { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: center; }
        select, input[type="text"], input[type="number"], input[type="date"] {
            width: 100%; padding: 5px; margin-bottom: 10px;
        }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            font-size: 16px;
            text-decoration: none;
            margin-right: 20px;
        }
        .btn-back:hover { background-color: #0056b3; }

        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .form-group { display: flex; flex-direction: column; margin-bottom: 15px; width: 45%; }
        .form-group-full { width: 100%; text-align: right; }
        .form-container form { display: flex; flex-wrap: wrap; gap: 4%; }
        .form-container label { margin-bottom: 5px; font-weight: bold; color: #333; }

        .btn-add {
            background: linear-gradient(90deg, rgb(146, 190, 156), rgb(79, 102, 84));
            color: #fff;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            transition: background 0.3s ease;
        }
        .btn-add:hover {
            background: linear-gradient(90deg, #218838, #1e7e34);
        }
    </style>
</head>
<body>
<h2>เพิ่มรายการ รายละเอียดการรับคำสั่งซื้อสินค้า</h2>

<div class="form-container">
    <h3>เพิ่มข้อมูลรายละเอียดคำสั่งซื้อสินค้า</h3>

    <?php if ($header): ?>
        <p>
            <strong>รหัสลูกค้า:</strong> <?= $header['CUS_ID'] ?>
            <strong>ชื่อลูกค้า:</strong> <?= $header['CUS_NAME'] ?>
            <strong>วันที่สั่งสินค้า:</strong> <?= formatDate($header['ORDER_DATE']) ?>
            <strong>Order No:</strong> <?= $header['ORDER_NO'] ?>
        </p>
    <?php else: ?>
        <p style="color:red;">ไม่พบข้อมูลคำสั่งซื้อ กรุณาย้อนกลับ</p>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form-group">
            <label>รหัสสินค้า:</label>
            <select name="goods_id" required onchange="updatePrice(this)">
                <option value="">-- เลือกสินค้า --</option>
                <?php while ($row = $result_goods->fetch_assoc()): ?>
                    <option value="<?= $row['GOODS_ID'] ?>" data-price="<?= $row['COST_UNIT'] ?>">
                        <?= $row['GOODS_ID'] ?> - <?= $row['GOODS_NAME'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>วันที่กำหนดส่งสินค้า:</label>
            <input type="date" name="due_date" required>
        </div>

        <div class="form-group">
            <label>วันที่ส่งสินค้าจริง:</label>
            <input type="date" name="fin_date">
        </div>

        <div class="form-group">
            <label>จำนวนที่สั่งสินค้า:</label>
            <input type="number" name="amount" id="amount" required min="1" oninput="calculateTotal()">
        </div>

        <div class="form-group">
            <label>ราคา/หน่วย (บาท):</label>
            <input type="text" id="cost_unit" name="cost_unit" readonly>
        </div>

        <div class="form-group">
            <label>ราคารวม (บาท):</label>
            <input type="text" id="total_price" name="total_price" readonly>
        </div>

        <div class="form-group-full">
            <button type="submit" class="btn btn-add">เพิ่มรายการสินค้า</button>
        </div>
    </form>
</div>

<h3>รายการสินค้าที่เพิ่มแล้ว</h3>
<table>
    <tr>
        <th>รหัสสินค้า</th>
        <th>ชื่อสินค้า</th>
        <th>วันกำหนดส่ง</th>
        <th>วันที่ส่งจริง</th>
        <th>จำนวนสั่ง</th>
        <th>ราคา/หน่วย</th>
        <th>ราคารวม</th>
    </tr>
    <?php if ($result_detail->num_rows > 0): ?>
        <?php while ($row = $result_detail->fetch_assoc()): ?>
            <tr>
                <td><?= $row['GOODS_ID'] ?></td>
                <td><?= $row['GOODS_NAME'] ?></td>
                <td><?= formatDate($row['ORD_DATE']) ?></td>
                <td><?= formatDate($row['FIN_DATE']) ?></td>
                <td><?= $row['AMOUNT'] ?></td>
                <td><?= number_format($row['COST_UNIT'], 2) ?></td>
                <td><?= number_format($row['TOT_PRC'], 2) ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="7">ไม่มีข้อมูลสินค้า</td></tr>
    <?php endif; ?>
</table>

<div style="position: fixed; margin-top: 20px; right: 20px;">
    <a href="order.php" class="btn btn-back">กลับไปยังหน้าแสดงรายการสั่งซื้อสินค้า</a>
    <a href="dashboard.php" class="btn btn-back" style="background-color:#6c757d;">กลับหน้าหลัก</a>
</div>

<script>
function updatePrice(selectObj) {
    const price = selectObj.options[selectObj.selectedIndex].getAttribute('data-price');
    document.getElementById('cost_unit').value = parseFloat(price).toFixed(2);
    calculateTotal();
}

function calculateTotal() {
    const unit_price = parseFloat(document.getElementById('cost_unit').value) || 0;
    const qty = parseInt(document.getElementById('amount').value) || 0;
    document.getElementById('total_price').value = (unit_price * qty).toFixed(2);
}

document.getElementById('amount').addEventListener('input', calculateTotal);
</script>
</body>
</html>

<?php $conn->close(); ?>
