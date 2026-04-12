<?php
// logout.php
session_start();
session_unset();    // Hapus semua variabel session
session_destroy();  // Hancurkan session dari memori server
header("Location: login.php");
exit;
?>