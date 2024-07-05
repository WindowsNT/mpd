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

$rolerow = QQ("SELECT * FROM ROLES WHERE UID = ? AND ROLE = ?",array($ur['ID'],ROLE_GLOBALPROSONEDITOR))->fetchArray();
if (!$rolerow)
{
    redirect("index.php");
    die;
}

printf('<button href="index.php" class="autobutton button  is-danger">Πίσω</button> ');
