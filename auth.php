<?php
require_once "function.php";
if (array_key_exists("afm2",$_SESSION))
    $_SESSION['afm'] = $_SESSION['afm2'];
if (array_key_exists("afm2",$_GET))
    $_SESSION['afm'] = $_GET['afm2'];
if (array_key_exists("logout",$_GET))
    {
        unset($_SESSION['afm']);
        unset($_SESSION['oauth2_results']);
    }
$ur = null;
$afm = 0;
if (array_key_exists("afm",$_SESSION))
    $afm = $_SESSION['afm'];
    
if (array_key_exists("oauth2_results",$_SESSION))
    {
    $xml = simplexml_load_string($_SESSION['oauth2_results']);
    $taxis_ln = "";
    $taxis_fn = "";
    $tax_afm = "";
    foreach($xml->userinfo[0]->attributes() as $a => $b) 
    {
        if ($a == "taxid")
            {
                $afm = trim((string)$b);
            }
            if ($a == "lastname")
            {
                $taxis_ln = trim((string)$b);
            }
            if ($a == "firstname")
            {
                $taxis_fn = trim((string)$b);
            }
    }
}



$ur = QQ("SELECT * FROM USERS WHERE AFM = ?",array($afm))->fetchArray();
if (!$ur && $afm != 0)
    {
        QQ("INSERT INTO USERS (AFM,LASTNAME,FIRSTNAME,CLSID) VALUES(?,?,?,?)",array(
            $afm,$taxis_ln,$taxis_fn,guidv4(),
        ));
        $ur = QQ("SELECT * FROM USERS WHERE AFM = ?",array($afm))->fetchArray();
    }
$superadmin = 0;
if ($afm == "114789033")
    $superadmin = 1;
if (array_key_exists("redirect",$_SESSION))
    header(sprintf("Location: %s",$_SESSION['redirect']));
if (array_key_exists("redirect",$_GET))
    header(sprintf("Location: %s",$_GET['redirect']));
