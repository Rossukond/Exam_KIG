<?php
include 'config/db.php';
$conn = new mysqli($servername, $username, $password, "kig");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query("SELECT * FROM cus_name");
?>
<!DOCTYPE html>
<html>
<head><title>ข้อมูลลูกค้า</title></head>
<body>
<h2>ข้อมูลลูกค้า</h2>
<table border="1">
<tr><th>รหัสลูกค้า</th><th>ชื่อลูกค้า</th></tr>
<?php while($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= $row["Cus_id"] ?></td>
    <td><?= $row["Cus_name"] ?></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
<?php $conn->close(); ?>
