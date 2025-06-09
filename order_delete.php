<?php
include 'config/db.php';

$order_no = $_GET['order_no'] ?? '';

if (!$order_no) {
    die("ไม่พบเลขที่คำสั่งซื้อ");
}

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// เปิด Transaction เพื่อความปลอดภัย
$conn->begin_transaction();

try {
    // ลบ Detail ก่อน
    $sqlDeleteDetail = "DELETE FROM D_ORDER WHERE ORDER_NO = ?";
    $stmtDetail = $conn->prepare($sqlDeleteDetail);
    $stmtDetail->bind_param("s", $order_no);
    $stmtDetail->execute();

    // ลบ Header
    $sqlDeleteHeader = "DELETE FROM H_ORDER WHERE ORDER_NO = ?";
    $stmtHeader = $conn->prepare($sqlDeleteHeader);
    $stmtHeader->bind_param("s", $order_no);
    $stmtHeader->execute();

    // Commit การลบ
    $conn->commit();

    // กลับหน้าหลัก
    header("Location: order.php");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}

$conn->close();
?>
