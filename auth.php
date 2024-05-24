<?php
require_once "function.php";
if (array_key_exists("afm2",$_SESSION))
    $_SESSION['afm'] = $_SESSION['afm2'];
if (array_key_exists("afm2",$_GET))
    $_SESSION['afm'] = $_GET['afm2'];
if (array_key_exists("logout",$_GET))
    unset($_SESSION['afm']);
$ur = null;
$afm = 0;
if (array_key_exists("afm",$_SESSION))
    $afm = $_SESSION['afm'];
$ur = QQ("SELECT * FROM USERS WHERE AFM = ?",array($afm))->fetchArray();
if (array_key_exists("redirect",$_SESSION))
    header(sprintf("Location: %s",$_SESSION['redirect']));
if (array_key_exists("redirect",$_GET))
    header(sprintf("Location: %s",$_GET['redirect']));
