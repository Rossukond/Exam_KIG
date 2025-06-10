<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <title>ปิดโปรแกรม</title>
</head>
<body>
    <script>
        alert('ออกจากระบบเรียบร้อยแล้ว');
        window.close(); // พยายามปิดหน้าต่าง
    </script>
    <h2>คุณได้ออกจากระบบเรียบร้อยแล้ว</h2>
    <p>กรุณาปิดหน้านี้</p>
</body>
</html>
