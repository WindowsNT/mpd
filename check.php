<?php

require_once "function.php";
require_once "auth.php";
require_once "output.php";

if (!$afm || !$ur)
    {
        redirect("index.php");
        die;
    }

if ($superadmin)
    $role = QQ("SELECT * FROM ROLES WHERE ID = ?",array($_GET['t']))->fetchArray();
else
    $role = QQ("SELECT * FROM ROLES WHERE ID = ? AND UID = ?",array($_GET['t'],$ur['ID']))->fetchArray();
if (!$role)
{
    redirect("index.php");
    die;
}

$params = json_decode($role['ROLEPARAMS'],true);
$afms = $params['afms'];
$level = 1;
if (array_key_exists("level",$params))
    $level = $params['level'];


if (array_key_exists("approve",$_GET))
{
    $prr = QQ("SELECT * FROM PROSON WHERE ID = ?",array($_GET['approve']))->fetchArray();
    $acc = HasProsonAccess($prr['UID'],$ur['ID']);
    if ($acc)
        {
            QQ("UPDATE PROSON SET STATE = ?,FAILREASON = '' WHERE ID = ?",array($level,$prr['ID']));
            PushProsonState($prr['ID']);
        }
    $ur = Single("USERS","ID",$prr['UID']);
    redirect(sprintf("check.php?t=%s&afm=%s",$_GET['t'],$ur['AFM']));
    die;
}
if (array_key_exists("reject",$_GET))
{
    $prr = Single("PROSON","ID",$_GET['reject']);
    $acc = HasProsonAccess($prr['UID'],$ur['ID']);
    if ($acc)
        {
            QQ("UPDATE PROSON SET STATE = -1,FAILREASON = ? WHERE ID = ?",array($req['reason'],$prr['ID']));
            PushProsonState($prr['ID']);
        }
    $ur = Single("USERS","ID",$prr['UID']);
    redirect(sprintf("check.php?t=%s&afm=%s",$_GET['t'],$ur['AFM']));
    die;
}


echo '<div class="content" style="margin: 20px">';
echo '<button href="index.php" class="autobutton button is-danger">Πίσω</button> <br><br>';
printf('Έλεγχος Προσόντων (Eπίπεδο: %s)<hr>',$level);


if (!array_key_exists("afm",$req))
{
    foreach($afms as $afm)
    {
        $cr = Single("USERS","AFM",$afm);
        if (!$cr)
            continue;
        printf('<a href="check.php?t=%s&afm=%s">%s %s</a><br>',$req['t'],$afm,$cr['LASTNAME'],$cr['FIRSTNAME']);
    }
    die;
}

if (!in_array($req['afm'],$afms))
    die;

?>
'<table class="table datatable" style="width: 100%">
    <thead>
        <th class="all">ΑΦΜ</th>
        <th class="all">Όνομα</th>
        <th class="all">Προσόντα</th>
    </thead>
    <tbody>
<?php


$cr = Single("USERS","AFM",$req['afm']);
printf('<tr>');
printf('<td>%s</td>',$afm);
printf('<td>%s %s</td>',$cr['LASTNAME'],$cr['FIRSTNAME']);
printf('<td>');


echo PrintProsonta($cr['ID'],$ur['ID'],$role,$level);

printf('</td>');
printf('</tr>');

?>
        
        </tbody>
</table>
