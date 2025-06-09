<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ระบบบริหารจัดการสินค้าคงคลัง</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
    }

    .header {
      background-color: #333;
      padding: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    .nav-left, .nav-right {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .nav a,
    .dropdown > a {
      color: white;
      padding: 12px 16px;
      text-decoration: none;
      font-size: 17px;
      border-radius: 6px;
      display: inline-block;
    }

    .nav a:hover,
    .dropdown > a:hover {
      background-color: #555;
    }

    /* Dropdown */
    .dropdown {
      position: relative;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #444;
      min-width: 300px;
      top: 100%;
      left: 0;
      z-index: 9999;
    }

    .dropdown-content a {
      display: block;
      color: white;
      padding: 12px 16px;
      text-decoration: none;
    }

    .dropdown-content a:hover {
      background-color: #666;
    }

    .dropdown:hover .dropdown-content {
      display: block;
    }

    @media screen and (max-width: 600px) {
      .header {
        flex-direction: column;
        align-items: flex-start;
      }

      .nav-left, .nav-right {
        flex-direction: column;
        width: 100%;
      }

      .dropdown-content {
        position: static;
      }
    }
  </style>
</head>
<body>

  <div class="header">
    <div class="nav-left nav">
      <div class="dropdown">
        <a href="javascript:void(0)">ฐานข้อมูลอ้างอิง ▾</a>
        <div class="dropdown-content">
          <a href="customer.php">ข้อมูลลูกค้า</a>
          <a href="product.php">ข้อมูลสินค้า</a>
        </div>
      </div>
      <div class="dropdown">
        <a href="javascript:void(0)">การทำงานประจำวัน ▾</a>
        <div class="dropdown-content">
          <a href="order.php">บันทึก/แก้ไข การสั่งซื้อสินค้า</a>
          <a href="order_processing.php">การประมวลผลข้อมูลการสั่งซื้อสินค้า</a>
        </div>
      </div>
      <!-- <a href="report.php">รายงาน</a> -->
            <div class="dropdown">
        <a href="javascript:void(0)">รายงาน ▾</a>
        <div class="dropdown-content">
          <a href="Delivery_Schedule_Report.php">รายงานกำหนดส่งสินค้า</a>
        </div>
      </div>
    </div>
    <div class="nav-right nav">
      <a href="logout.php">ออกจากระบบ</a>
    </div>
  </div>

  <div style="padding: 20px;">
    <h2>ระบบบริหารจัดการสินค้าคงคลัง</h2>
    <p>ยินดีต้อนรับ!</p>
  </div>

</body>
</html>
