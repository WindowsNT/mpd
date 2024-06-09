<?php

require_once "function.php";
require_once "auth.php";


if (!$afm || !$ur)
    {
        redirect("index.php");
        die;
    }

if (!array_key_exists("f",$_GET))
{
    redirect("index.php");
    die;
}

$uid = $ur['ID'];
if (array_key_exists("force_user",$req))
    $uid = QQ("SELECT * FROM USERS WHERE CLSID = ?",array($req['force_user']))->fetchArray()['ID'];


$fr = QQ("SELECT * FROM PROSONFILE WHERE UID = ? AND ID = ?",array($uid,$_GET['f']))->fetchArray();
if (!$fr && !array_key_exists("force_user",$req))
{
    // check if uid checks f
    $fr = QQ("SELECT * FROM PROSONFILE WHERE ID = ?",array($_GET['f']))->fetchArray();
    if ($fr)
    {
        $cl = CheckLevel($ur['ID'],$fr['UID']);
        if ($cl <= 0)
            $fr = null;
    }
}
if (!$fr)
{
    redirect("index.php");
    die;
}

$d = file_get_contents("files/{$fr['CLSID']}");

$finfo = new finfo(FILEINFO_MIME);
header(sprintf('Content-Type: %s',$finfo->buffer($d)));
echo $d;
die;
