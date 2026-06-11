<?php
require_once __DIR__ . '/includes/config.php';
Sesija::odjaviKorisnika();
header('Location: ' . BASE_URL . '/login.php');
exit;
