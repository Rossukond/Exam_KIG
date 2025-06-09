<?php
include 'config/db.php';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("เชื่อมต่อฐานข้อมูลล้มเหลว");

$order_no = $_GET['order_no'] ?? ''; // รับค่าจาก URL

if (empty($order_no)) die("ไม่พบหมายเลข Order");

// ดึงข้อมูล Header
$sql_header = "SELECT H.ORDER_NO, H.CUS_ID, C.CUS_NAME, H.ORDER_DATE 
               FROM H_ORDER H 
               JOIN CUS_NAME C ON H.CUS_ID = C.CUS_ID 
               WHERE H.ORDER_NO = '$order_no'";
$result_header = $conn->query($sql_header);
$header = $result_header->fetch_assoc();

// ดึงสินค้า
$sql_goods = "SELECT GOODS_ID, GOODS_NAME, COST_UNIT FROM GOODS_NAME";
$result_goods = $conn->query($sql_goods);

// ถ้ามีการ POST (เพิ่มรายการสินค้า)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goods_id = $_POST['goods_id'];
    $due_date = $_POST['due_date'];
    $fin_date = $_POST['fin_date'];
    $amount = $_POST['amount'];
    $cost_unit = $_POST['cost_unit'];
    $tot_prc = $_POST['total_price'];

    // Insert into D_ORDER
    $stmt = $conn->prepare("INSERT INTO D_ORDER (ORDER_NO, GOODS_ID, ORD_DATE, FIN_DATE, AMOUNT, COST_UNIT, TOT_PRC) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssidd", $order_no, $goods_id, $due_date, $fin_date, $amount, $cost_unit, $tot_prc);
    $stmt->execute();
    $stmt->close();
}

// ดึงรายการ Detail ที่เพิ่มแล้ว
$sql_detail = "SELECT 
                    D.GOODS_ID AS GOODS_ID, 
                    D.ORD_DATE AS ORD_DATE, 
                    D.FIN_DATE AS FIN_DATE, 
                    D.AMOUNT AS AMOUNT, 
                    D.COST_UNIT AS COST_UNIT, 
                    D.TOT_PRC AS TOT_PRC,
                    G.GOODS_NAME AS GOODS_NAME
               FROM D_ORDER D
               JOIN GOODS_NAME G ON D.GOODS_ID = G.GOODS_ID
               WHERE D.ORDER_NO = '$order_no'";
$result_detail = $conn->query($sql_detail);


function formatDate($date) {
    return date("d/m/Y", strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>เพิ่มรายการการรับคำสั่งซื้อสินค้า</title>
<style>
    body { font-family: Arial; padding: 20px; background: #f7f7f7; }
    h2 { margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 20px; }
    th, td { padding: 8px; border: 1px solid #ddd; text-align: center; }
    select, input[type="text"], input[type="number"], input[type="date"] {
        width: 100%; padding: 5px; margin-bottom: 10px;
    }
    .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
    .btn-add { background:rgb(129, 158, 136); color: #00000; }
    .btn-back {
        display: inline-block;
        padding: 8px 16px;
        background-color: #007bff;
        color: white;
        border-radius: 4px;
        text-decoration: none;
        margin-left: 10px;
    }

    .btn-back:hover {
        background-color: #0056b3;
    }

    .form-container {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-top: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        margin-bottom: 15px;
        width: 48%;
    }

    .form-group-full {
        width: 100%;
        text-align: center;
        margin-top: 10px;
    }

    .form-container form {
        display: flex;
        flex-wrap: wrap;
        gap: 4%;
    }

    .form-container label {
        margin-bottom: 5px;
        font-weight: bold;
        color: #333;
    }

    .form-container input[type="text"],
    .form-container input[type="number"],
    .form-container input[type="date"],
    .form-container select {
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .btn-add {
        background: linear-gradient(90deg,rgb(146, 190, 156),rgb(79, 102, 84));
        color: #fff;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 30px;
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
<h2>การบันทึก/แก้ไข การสั่งซื้อสินค้า</h2>



<div class="form-container">
<h3>เพิ่มข้อมูล Detail</h3>
<p><strong>รหัสลูกค้า:</strong> <?php echo $header['CUS_ID']; ?> 
    <strong>ชื่อลูกค้า:</strong> <?php echo $header['CUS_NAME']; ?> 
    <strong>วันที่สั่งสินค้า:</strong> <?php echo formatDate($header['ORDER_DATE']); ?>
    <strong>Order No:</strong> <?php echo $header['ORDER_NO']; ?></p>
    
    <form method="post" action="">
        <div class="form-group">
            <label>รหัสสินค้า:</label>
            <select name="goods_id" required onchange="updatePrice(this)">
                <option value="">-- เลือกสินค้า --</option>
                <?php
                if ($result_goods->num_rows > 0) {
                    while($row = $result_goods->fetch_assoc()) {
                        echo "<option value='{$row['GOODS_ID']}' data-price='{$row['COST_UNIT']}'>{$row['GOODS_ID']} - {$row['GOODS_NAME']}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>วันกำหนดส่ง:</label>
            <input type="date" name="due_date" required>
        </div>

        <div class="form-group">
            <label>วันที่ส่งสินค้าจริง:</label>
            <input type="date" name="fin_date">
        </div>

        <div class="form-group">
            <label>จำนวนสั่ง:</label>
            <input type="number" name="amount" id="amount" required min="1" placeholder="กรอกจำนวนสินค้า">
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
            <button type="submit" class="btn btn-add">➕ เพิ่มรายการสินค้า</button>
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
    <?php
    if ($result_detail->num_rows > 0) {
        while($row = $result_detail->fetch_assoc()) {
            echo "<tr>
                <td>{$row['GOODS_ID']}</td>
                <td>{$row['GOODS_NAME']}</td>
                <td>".formatDate($row['ORD_DATE'])."</td>
                <td>".formatDate($row['FIN_DATE'])."</td>
                <td>{$row['AMOUNT']}</td>
                <td>".number_format($row['COST_UNIT'],2)."</td>
                <td>".number_format($row['TOT_PRC'],2)."</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='7'>ไม่มีข้อมูลสินค้า</td></tr>";
    }
    ?>
</table>

<div style="position: fixed; bottom: 20px; right: 20px;">
    <a href="order.php" class="btn btn-back">กลับไปยังหน้าจอแสดงรายการสั่งสินค้า</a>
    <a href="dashboard.php" class="btn btn-back">กลับหน้าหลัก</a>
</div>
<script>

document.getElementById('amount').addEventListener('input', function() {
    const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
    const amount = parseInt(this.value) || 0;
    document.getElementById('total_price').value = (unitPrice * amount).toFixed(2);
});

function updatePrice(selectObj) {
    var price = selectObj.options[selectObj.selectedIndex].getAttribute('data-price');
    document.getElementById('cost_unit').value = parseFloat(price).toFixed(2);
    calculateTotal();
}

function calculateTotal() {
    var unit_price = parseFloat(document.getElementById('cost_unit').value) || 0;
    var qty = parseInt(document.getElementById('amount').value) || 0;
    document.getElementById('total_price').value = (unit_price * qty).toFixed(2);
}


</script>

</body>
</html>

<?php $conn->close(); ?>
