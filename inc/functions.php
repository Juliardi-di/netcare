<?php
require_once __DIR__ . '/../db/config.php';


function is_logged_in()
{
    return isset($_SESSION['user_id']);
}


function current_user()
{
    global $mysqli;
    if (!is_logged_in())
        return null;
    $id = (int) $_SESSION['user_id'];
    $res = $mysqli->query("SELECT id,name,email,role FROM users WHERE id={$id} LIMIT 1");
    return $res ? $res->fetch_assoc() : null;
}


function esc($s)
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}