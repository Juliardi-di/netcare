<?php
session_start();
session_unset();
session_destroy();

header("Location: /netcare/login.php");
exit;
