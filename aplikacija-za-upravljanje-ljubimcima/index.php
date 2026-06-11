<?php
// Početna stranica - preusmerava na login ili dashboard
require_once __DIR__ . '/includes/config.php';

if (Sesija::jePrijavljen()) {
    header('Location: ' . BASE_URL . '/dashboard.php');
} else {
    header('Location: ' . BASE_URL . '/login.php');
}
exit;
