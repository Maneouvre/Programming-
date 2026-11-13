<?php require 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>About Us - Emmanuel's Collections</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{font-family:Arial; margin:0; background:#FFF5F5;}
.header{background:#1e3a8a; color:white; padding:15px; text-align:center;}
/* Brand logo styling rule */
.shop-logo {border-radius:40px;width: 80px;height: 80px;}
.logo-container{display:flex; justify-content:center;}
.content{padding:20px; max-width:800px; margin:auto; background:#FFEAF0; margin-top:20px; border-radius:12px;}
.btn{background:#1e3a8a; color:white; padding:12px 25px; border-radius:25px; text-decoration:none; display:inline-block; margin-top:15px;}
.social-box{margin-top:25px; padding:20px; background:rgba(55,65,81,0.15); border-radius:12px; text-align:center;}
.social-icons a{width:50px; height:50px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:22px; color:white; margin:0 8px; text-decoration:none;}
.wa{background:#25D366;} .fb{background:#1877F2;} .x{background:#000;} .tw{background:#1DA1F2;}

</style>
</head>
<body>

<div class="header">
  <h2>EMMANUEL'S COLLECTIONS</h2>
</div>

<div class=logo-container>
<img src="shop_images\logo.jpg" alt="Logo" class="shop-logo" onerror="this.style.display='none'">
</div>

<div class="content">
  <h2>Know More About Us</h2>
  <p>Welcome to <b>Emmanuel's Collections</b> - your trusted fashion plug in Cheptulu, Kaimosi and Western Kenya.</p>
  
  <p>We started with a simple goal: Make trendy, quality clothes affordable for students and families. From dresses, shoes, jeans, T-shirts to official wear - we have it.</p>
  
  <p><b>📍 Location:</b> Cheptulu (Near KAFU)<br>
  <b>🚚 Delivery:</b> Free in Cheptulu,<br>
  <b>💳 Payment:</b> Lipa na M-Pesa, Cash on Delivery<br>
  <b>🕗 Open:</b> 8AM - 8PM Daily</p>

  <!-- SOCIAL ICONS - ONLY ON ABOUT PAGE -->
  <div class="social-box">
    <h3 style="margin-top:0;">Follow Us / Talk to Us</h3>
    <div class="social-icons">
      <a href="https://wa.me/254113476618?text=Hi%20Emmanuel's%20Collections" class="wa" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
      <a href="https://facebook.com/YOUR_PAGE" class="fb" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
      <a href="https://x.com/YOUR_HANDLE" class="x" target="_blank" title="X (Twitter)"><i class="fab fa-x-twitter"></i></a>
      <a href="https://twitter.com/YOUR_HANDLE" class="tw" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
    </div>
    <p style="font-size:13px; color:#555; margin-top:10px;">Tap an icon to chat or follow</p>
  </div>

  <a href="index.php" class="btn">← Back to Shop</a>
</div>

<footer style="text-align:center; padding:15px; background:#1e3a8a; color:white; margin-top:30px;">
  &copy; <?= date('Y') ?> EMMANUEL'S COLLECTIONS
</footer>

</body>
</html>