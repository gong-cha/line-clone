<?php
session_start();
if ($_SESSION['nonce'] === $_GET['nonce']) {
    unset($_SESSION['user_id']);
    header('Location: login.php');
}