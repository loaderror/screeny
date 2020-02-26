<?php
require_once('../config.php');
session_start();

if (@$_GET['key'] === $sessionKey) {
    $_SESSION['loggedIn'] = true;
}

if (@$_SESSION['loggedIn'] !== true) {
    die();
}
