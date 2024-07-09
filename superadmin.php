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

if (array_key_exists("vacuum",$req))
{
    QQ("VACUUM");
    redirect("superadmin.php");
    die;
}

printf('<button class="button autobutton  is-danger block" href="index.php">Πίσω</button> ');
printf('<hr>');

printf('<button class="button autobutton  is-primary block" href="superadmin.php?vacuum=1">Vacuum [%s KB]</button> ',filesize($dbxx)/1024);
