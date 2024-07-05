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

$rolerow = QQ("SELECT * FROM ROLES WHERE UID = ? AND ROLE = ?",array($ur['ID'],ROLE_FOREASSETPLACES))->fetchArray();
if (!$rolerow)
{
    redirect("index.php");
    die;
}

$t = time();
$t = 0;
$q1 = QQ("SELECT * FROM CONTESTS  WHERE STARTDATE > $t");
while($r1 = $q1->fetchArray())
{
    $q2 = QQ("SELECT * FROM PLACES WHERE CID = ?",array($r1['ID']));
    while($r2 = $q2->fetchArray())
    {
        $roleparx = QQ("SELECT * FROM ROLEPAR WHERE RID = ? AND PVALUE = ?",array($rolerow['ID'],$r2['ID']))->fetchArray();
        if (!$roleparx)
            continue;

        if (array_key_exists("cid",$req) && array_key_exists("pid",$req) && $req['cid'] ==$r1['ID'] && $req['pid'] == $r2['ID']) 
        {
            // edit
            return;
        }

        printf('Διαγωνισμός: %s<br><a href="positions.php?t=%s&cid=%s&pid=%s">%s</a>',$r1['DESCRIPTION'],$rolerow['ID'],$r1['ID'],$r2['ID'],$r2['DESCRIPTION']);
    }   
}