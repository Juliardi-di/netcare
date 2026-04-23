<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];
session_unset();
session_destroy();

header("Location: /netcare/login.php?role=opd");
exit;
