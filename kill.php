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





if (!HasContestAccess($req['cid'],$ur['ID'],1))
    die;

Kill($req['cid'],0,0,0);
redirect("contest.php");