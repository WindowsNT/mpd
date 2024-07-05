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

$byname = '';
$placerow = null;
if (array_key_exists("pid",$req))
    $placerow = QQ("SELECT * FROM PLACES WHERE ID = ?",array($req['pid']))->fetchArray();
if (!$placerow)
{
    if (!array_key_exists("name",$req))
        {
            redirect("index.php");
            die;
        }
    $byname = ($req['name']);
    $posrow = array("ID" => 0,"DESCRIPTION" => $byname);
    $placerow = array("ID" => 0,"DESCRIPTION" => "Όλοι");
}
else
{
    $posrow = QQ("SELECT * FROM POSITIONS WHERE ID = ?",array($req['pos']))->fetchArray();
    if (!$posrow)
    {
        redirect("index.php");
        die;
    }
}



$reqrow = QQ("SELECT * FROM REQUIREMENTS WHERE ID = ?",array($req['prid']))->fetchArray();
if (!$reqrow)
{
    redirect("index.php");
    die;
}

$pros = $reqrow['PROSONTYPE'];
EnsureProsonLoaded();

if (array_key_exists("prid",$_POST))
{
    QQ("DELETE FROM REQRESTRICTIONS WHERE RID = ?",array($req['prid']));
    foreach($_POST as $key=>$value)
    {
        if (strstr($key,"reg"))
        {
            $param_id = substr($key,3);
            if (strlen($value))
                QQ("INSERT INTO REQRESTRICTIONS (RID,PID,RESTRICTION) VALUES(?,?,?)",array(
                $req['prid'],
                $param_id,$value
            ));
        }
    }
    redirect(sprintf("prosonta.php?t=%s&cid=%s&pid=%s&pos=%s",$req['t'],$req['cid'],$req['pid'],$req['pos']));
    die;
//    print_r($_POST); die;
}

$pars = array();
$proot = RootForClassId($xmlp->classes,$pros,$pars);
$attrs = $proot->attributes();
printf("Φορέας %s<br>Θέση %s<br>Προσόν %s - %s<br>",$placerow['DESCRIPTION'],$posrow['DESCRIPTION'],$pros,$attrs['t']);
if (!$proot->params)
    die("Δεν υπάρχουν ρυθμίσεις για αυτό το προσόν.");

?>
<form method="POST" action="regex.php">
    <input type="hidden" name="t" value="<?= $req['t'] ?>" />
    <input type="hidden" name="cid" value="<?= $req['cid'] ?>" />
    <input type="hidden" name="pid" value="<?= $req['pid'] ?>" />
    <input type="hidden" name="pos" value="<?= $req['pos'] ?>" />
    <input type="hidden" name="prid" value="<?= $req['prid'] ?>" />

<table class="table datatable">
<thead>
    <th>#</th>
    <th>Όνομα</th>
    <th>Regex/Expression</th>
</thead>
<tbody>
<?php
foreach($proot->params->p as $p)
{
    $attrs2 = $p->attributes();
    $val = '';
    $rx = QQ("SELECT * FROM REQRESTRICTIONS WHERE RID = ? AND PID =? ",array($reqrow['ID'],$attrs2['id']))->fetchArray();
    if ($rx)
        $val = $rx['RESTRICTION'];
    printf('<tr>');
    printf('<td>%s</td>',$attrs2['id']);
    printf('<td>%s</td>',$attrs2['n']);
    printf('<td>
        <input type="text" class="input" name="reg%s" id="reg%s" value="%s"/>
    </td>',$attrs2['id'],$attrs2['id'],$val);
    printf('</tr>');
}

?>
</tbody>
</table>
<br><br>
<button class="button is-success">Υποβολή</button>
</form>
<?php
    

