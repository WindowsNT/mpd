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
if (!HasFileAccess($req['f'],$uid,0))
    die;
$fr = QQ("SELECT * FROM PROSONFILE WHERE ID = ?",array($req['f']))->fetchArray();


$d = file_get_contents("files/{$fr['CLSID']}");

$finfo = new finfo(FILEINFO_MIME);
header(sprintf('Content-Type: %s',$finfo->buffer($d)));
echo $d;
die;
