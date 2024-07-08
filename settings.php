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

printf('<button class="button autobutton  is-danger block" href="index.php">Πίσω</button> ');
printf('<hr>');

// Push
if (1)
{
    Push3_ShowScripts($ur['CLSID'],0);
    echo Push3_ShowOptions($ur['CLSID'],0,0);
}
