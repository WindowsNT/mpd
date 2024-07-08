<?php

require_once "function.php";
require_once "auth.php";
require_once "output.php";
echo '<div class="content" style="margin: 20px">';

if (!$afm || !$ur || !$superadmin)
    {
        redirect("index.php");
        die;
    }

$ur = Single("USERS","ID",$req['u']);
$_SESSION['afm2'] = $ur['AFM'];
unset($_SESSION['oauth2_results']);
redirect("index.php");
