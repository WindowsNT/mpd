<?php

require_once "function.php";
require_once "auth.php";
require_once "output.php";
echo '<div class="content" style="margin: 20px">';

if (!$afm || !$ur)
    {
        redirect("index.php");
        die;
    }

$rolerow = Single("ROLES","ID",$req['t']);
if ($rolerow['UID'] != $ur['ID'])
{
    redirect("index.php");
    die;
}

