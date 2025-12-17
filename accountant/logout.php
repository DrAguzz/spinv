<?php
session_start();

// Hapus semua session
$_SESSION = [];           // Kosongkan semua session
session_unset();          // Optional: hapus semua session
session_destroy();        // Hancurkan session

// Redirect ke login page
header("Location: ../login.php");  // Sesuaikan path ikut folder
exit();
