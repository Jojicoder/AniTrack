<?php
session_start();
require_once __DIR__ . '/config/app.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/home.php');
} else {
    header('Location: ' . BASE_URL . '/pages/index.html');
}
exit;
