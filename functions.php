<?php
// functions.php
require_once __DIR__ . '/config.php';

function current_user() {
    return $_SESSION['user'] ?? null;
}

function pretty_money($n) {
    return number_format($n, 0, ',', '.') . ' ₫';
}
