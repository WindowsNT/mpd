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

$rolerow = QQ("SELECT * FROM ROLES WHERE ID = ?",array($req['t']))->fetchArray();
if ($rolerow['UID'] != $ur['ID'])
{
    redirect("index.php");
    die;
}

$contestrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($req['cid']))->fetchArray();
if (!$contestrow)
{
    redirect("index.php");
    die;
}

$placerow = QQ("SELECT * FROM PLACES WHERE ID = ?",array($req['pid']))->fetchArray();
if (!$placerow)
{
    redirect("index.php");
    die;
}

$posrow = QQ("SELECT * FROM POSITIONS WHERE ID = ?",array($req['pos']))->fetchArray();
if (!$posrow)
{
    redirect("index.php");
    die;
}


$reqrow = QQ("SELECT * FROM REQUIREMENTS WHERE ID = ?",array($req['prid']))->fetchArray();
if (!$reqrow)
{
    redirect("index.php");
    die;
}

$pros = $reqrow['PROSONTYPE'];
EnsureProsonLoaded();

if (array_key_exists("from",$_POST))
{
    if ($req['from'] != $req['to'])
        QQ("UPDATE REQUIREMENTS SET NOTLINK = ? WHERE ID = ?",array($req['to'],$req['from']));
    redirect(sprintf("prosonta.php?t=%s&cid=%s&pid=%s&pos=%s",$req['t'],$req['cid'],$req['pid'],$req['pos']));
    die;
}

$pars = array();
$proot = RootForClassId($xmlp->classes,$pros,$pars);
$attrs = $proot->attributes();


printf("Φορέας %s<br>Θέση %s<br>Προσόν %s - %s<br>",$placerow['DESCRIPTION'],$posrow['DESCRIPTION'],$pros,$attrs['t']);

$to = (int)$reqrow['NOTLINK'];
?>
<form method="POST" action="notlink.php">
<input type="hidden" name="t" value="<?= $req['t'] ?>" />
    <input type="hidden" name="cid" value="<?= $req['cid'] ?>" />
    <input type="hidden" name="pid" value="<?= $req['pid'] ?>" />
    <input type="hidden" name="pos" value="<?= $req['pos'] ?>" />
    <input type="hidden" name="prid" value="<?= $req['prid'] ?>" />
    <input type="hidden" name="from" value="<?= $req['prid'] ?>" />
    <br><br>ID για NOT:<br>
    <input type="number" class="input" name="to" value="<?= $to ?>" />
<br><br>
<button class="button is-success">Υποβολή</button>
</form>
<?php
    

