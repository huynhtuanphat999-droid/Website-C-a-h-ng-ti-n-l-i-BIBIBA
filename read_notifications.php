<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) exit;

$uid = $_SESSION['user_id'];

$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")
    ->execute([$uid]);
?>
