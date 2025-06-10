<?php
include 'config/db.php';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// เพิ่มลูกค้า
if(isset($_POST['add'])){
    $cus_id = $_POST['cus_id'];
    $cus_name = $_POST['cus_name'];
    if(!empty($cus_id) && !empty($cus_name)){
        $stmt = $conn->prepare("INSERT INTO cus_name (Cus_id, Cus_name) VALUES (?, ?)");
        $stmt->bind_param("ss", $cus_id, $cus_name);
        $stmt->execute();
    }
}

// ลบลูกค้า
if(isset($_GET['delete'])){
    $del_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM cus_name WHERE Cus_id = ?");
    $stmt->bind_param("s", $del_id);
    $stmt->execute();
}

// แก้ไขลูกค้า
if(isset($_POST['update'])){
    $cus_id = $_POST['cus_id'];
    $cus_name = $_POST['cus_name'];
    $stmt = $conn->prepare("UPDATE cus_name SET Cus_name = ? WHERE Cus_id = ?");
    $stmt->bind_param("ss", $cus_name, $cus_id);
    $stmt->execute();
}

// ดึงข้อมูลลูกค้าทั้งหมด
$result = $conn->query("SELECT * FROM cus_name");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการข้อมูลลูกค้า</title>
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f4f4f4; text-align: center; }
        table { border-collapse: collapse; margin: 0 auto; width: 60%; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background-color:rgb(165, 165, 165); color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        input[type="text"] { padding: 5px; }
        input[type="submit"], .btn { padding: 5px 10px; margin: 3px; cursor: pointer; border: none; border-radius: 4px; }
        .add { background-color:rgb(108, 153, 78); color: white; }
        .actions a {display: inline-block; padding: 6px 12px; border-radius: 4px; font-size: 14px; color: white; text-decoration: none;}
        .edit { background-color: #ff9800; color: white; }
        .delete { background-color: #f44336; color: white; }
        .back { background-color: #6c757d; color: white; margin-top: 10px; }
        input[type="button"] {
            display: inline-block;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
            margin-right: 20px;
        }
    </style>
</head>
<body>

<h2>จัดการข้อมูลลูกค้า</h2>

<!-- ฟอร์มเพิ่ม/แก้ไข -->
<?php
if(isset($_GET['edit'])):
    $edit_id = $_GET['edit'];
    $edit_result = $conn->query("SELECT * FROM cus_name WHERE Cus_id='$edit_id'");
    $edit_row = $edit_result->fetch_assoc();
?>
<form method="post">
    <input type="hidden" name="cus_id" value="<?= htmlspecialchars($edit_row['Cus_id']) ?>">
    <input type="text" name="cus_name" value="<?= htmlspecialchars($edit_row['Cus_name']) ?>" required>
    <input type="submit" name="update" value="อัปเดต" class="edit">
</form>
<?php else: ?>
<form method="post">
    <input type="text" name="cus_id" placeholder="รหัสลูกค้า" required>
    <input type="text" name="cus_name" placeholder="ชื่อลูกค้า" required>
    <input type="submit" name="add" value="เพิ่มลูกค้า" class="add">
</form>
<?php endif; ?>

<br><br>

<!-- ตารางข้อมูล -->
<table>
    <tr>
        <th>รหัสลูกค้า</th>
        <th>ชื่อลูกค้า</th>
        <th>จัดการ</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['Cus_id']) ?></td>
        <td><?= htmlspecialchars($row['Cus_name']) ?></td>
        <td class='actions'>
            <a href="?edit=<?= urlencode($row['Cus_id']) ?>" class="btn edit">แก้ไข</a>
            <a href="?delete=<?= urlencode($row['Cus_id']) ?>" class="btn delete" onclick="return confirm('ยืนยันการลบ?')">ลบ</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<br>
<input type="button" value="กลับหน้าหลัก" class="back" onclick="window.location='dashboard.php';">

</body>
</html>

<?php $conn->close(); ?>
