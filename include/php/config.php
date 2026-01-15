<?php
// include/php/config.php

// Database configuration - guna format yang sedia ada
$db_server = "localhost";
$db_user = "root";
$db_pw = "";
$db_name = "Spinv";

// Create connection dengan mysqli_connect
$conn = mysqli_connect($db_server, $db_user, $db_pw, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF8
mysqli_set_charset($conn, "utf8mb4");

// Email configuration untuk forgot password
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'erfanbagus06@gmail.com'); // Ganti dengan email anda
define('SMTP_PASS', 'echf gega xlga dryo'); // Ganti dengan App Password Gmail
define('SMTP_FROM', 'erfanbagus06@gmail.com');
define('SMTP_FROM_NAME', 'SPInventory System');

// Site URL
define('SITE_URL', 'http://localhost/spinventory'); // Ganti dengan URL site anda

// Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');
?>