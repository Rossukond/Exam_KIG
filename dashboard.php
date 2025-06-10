<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ระบบบริหารจัดการสินค้าคงคลัง</title>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      margin: 0;
      background-color: #f5f7fa;
      color: #333;
    }

    .header {
      background-color: #1a237e;
      padding: 12px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      flex-wrap: wrap;
    }

    .nav-left, .nav-right {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .nav a,
    .dropdown > a {
      color: #fff;
      padding: 10px 16px;
      text-decoration: none;
      font-size: 16px;
      border-radius: 8px;
      transition: background-color 0.3s ease;
    }

    .nav a:hover,
    .dropdown > a:hover {
      background-color: #3949ab;
    }

    .dropdown {
      position: relative;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #3949ab;
      min-width: 220px;
      top: 110%;
      left: 0;
      z-index: 9999;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .dropdown-content a {
      display: block;
      color: #fff;
      padding: 12px 16px;
      text-decoration: none;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      transition: background-color 0.3s ease;
    }

    .dropdown-content a:hover {
      background-color: #5c6bc0;
    }

    .dropdown:hover .dropdown-content {
      display: block;
    }

    @media screen and (max-width: 600px) {
      .header {
        flex-direction: column;
        align-items: flex-start;
      }

      .dropdown-content {
        position: static;
        box-shadow: none;
        border-radius: 0;
      }

      .nav-left, .nav-right {
        width: 100%;
        justify-content: space-between;
        margin-top: 8px;
      }
    }

    .content {
      padding: 40px 20px 20px;
      text-align: center;
    }

    h2 {
      color: #1a237e;
      font-size: 32px;
      margin-bottom: 10px;
    }

    p {
      color: #555;
      font-size: 18px;
    }

    .company-info {
      max-width: 900px;
      margin: 30px auto;
      background: #fff;
      padding: 30px 25px;
      border-radius: 16px;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
      text-align: left;
    }

    .company-info img {
      max-width: 700px;
      display: block;
      margin: 0 auto 20px;
      border-radius: 12px;
    }

    .company-info h3 {
      text-align: center;
      color: #1a237e;
      margin-bottom: 15px;
      font-size: 24px;
    }

    .company-info p {
      font-size: 17px;
      line-height: 1.8;
      color: #444;
      margin-bottom: 12px;
    }

    .logo {
      height: 40px;
      width: auto;
      margin-right: 10px;
    }

    .company-info {
      max-width: 800px;
      margin: 20px auto;
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      text-align: left;
      line-height: 1.8;
    }

    .company-info h3, .company-info h4 {
      text-align: center;
      color: #2c3e50;
    }

    .company-info p {
      color: #444;
      font-size: 16px;
    }

    /* .KKF-banner {
      width: 100%;
      max-height: 300px;
      border-radius: 16px;
      margin-bottom: 20px;
      object-fit: cover;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    } */


  </style>
</head>
<body>

  <div class="header">
    <div class="nav-left nav">
      <img src="images/logo.png" alt="โลโก้บริษัทแหอวน" class="logo" />
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
      <div class="dropdown">
        <a href="javascript:void(0)">รายงาน ▾</a>
        <div class="dropdown-content">
          <a href="report.php">รายงานกำหนดส่งสินค้า</a>
        </div>
      </div>
    </div>
    <div class="nav-right nav">
      <a href="logout.php">ออกจากระบบ</a>
    </div>
  </div>

  <div class="content">
    <h2>ระบบบริหารจัดการสินค้าคงคลัง</h2>
  </div>

  <section class="company-info">
    <img src="images/KKF.png" alt="โลโก้บริษัท ขอนแก่นแหอวน จำกัด" class="KKF" />
    <h3>บริษัท ขอนแก่นแหอวน จำกัด</h3>
    <p>
      บริษัท ขอนแก่นแหอวน จำกัด ก่อตั้งขึ้นเมื่อวันที่ 3 ตุลาคม พ.ศ. 2520 เป็นผู้นำด้านการผลิตและจำหน่ายแหอวน ตาข่าย 
      และอุปกรณ์ประมงรายใหญ่ของประเทศไทย ภายใต้แบรนด์ “เรือใบ” (SHIP) โดยมีการส่งออกสินค้ากว่า 52 ประเทศทั่วโลก 
      สินค้าทุกชิ้นผลิตจากวัตถุดิบคุณภาพสูง ผ่านกระบวนการที่ได้มาตรฐานสากล ได้รับการยอมรับจากลูกค้าทั้งในและต่างประเทศมาอย่างยาวนาน
    </p>
    <p>
      บริษัทให้ความสำคัญกับการพัฒนาเทคโนโลยีการผลิตอย่างต่อเนื่อง เช่น การใช้เครื่องจักรอัตโนมัติ การวิจัยและพัฒนา (R&D) 
      รวมถึงการนำระบบ ERP เข้ามาช่วยบริหารจัดการกระบวนการผลิต เพื่อเพิ่มประสิทธิภาพ ลดต้นทุน และสร้างความพึงพอใจสูงสุดแก่ลูกค้า
    </p>
    <p>
      ปัจจุบัน บริษัทมีสำนักงานใหญ่ที่จังหวัดขอนแก่น และมีสาขาในกรุงเทพมหานคร มหาสารคาม รวมถึงต่างประเทศ เช่น สาธารณรัฐประชาชนจีน 
      และสาธารณรัฐแห่งสหภาพเมียนมา เพื่อรองรับการขยายตัวของตลาดในภูมิภาค
    </p>
    <h4>ข้อมูลติดต่อ</h4>
    <p>
      ที่อยู่: 115 หมู่ 17 ถนนมิตรภาพ ตำบลในเมือง อำเภอเมืองขอนแก่น จังหวัดขอนแก่น 40000<br />
      Facebook: <a href="https://www.facebook.com/kkfthailand" target="_blank">KKF Thailand</a>
    </p>
  </section>

</body>
</html>
