<?php
session_start();

$_SESSION = [];
session_unset();
session_destroy();

header("Location: /netcare/login.php?role=petugas");
exit;
