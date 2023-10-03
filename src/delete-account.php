<?php
session_start();
if ($_SESSION['nonce'] === $_GET['nonce']) {
    require_once 'load.php';
    DB::delete_account($_SESSION['user_id']);
    unset($_SESSION['user_id']);
    header('Location: signup.php');
}