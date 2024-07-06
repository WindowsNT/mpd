<?php

require_once "function.php";
require_once "auth.php";
require_once "output.php";

if (!$afm || !$ur)
    {
        redirect("index.php");
        die;
    }

$role = QQ("SELECT * FROM ROLES WHERE ID = ? AND UID = ?",array($_GET['t'],$ur['ID']))->fetchArray();
if (!$role)
{
    redirect("index.php");
    die;
}

if (array_key_exists("approve",$_GET))
{
    $prr = QQ("SELECT * FROM PROSON WHERE ID = ?",array($_GET['approve']))->fetchArray();
    $acc = CheckLevel($ur['ID'],$prr['UID']);
    if ($acc > 0)
        QQ("UPDATE PROSON SET STATE = 1 WHERE ID = ?",array($prr['ID']));
    redirect(sprintf("check.php?t=%s",$_GET['t']));
    die;
}
if (array_key_exists("reject",$_GET))
{
    $prr = QQ("SELECT * FROM PROSON WHERE ID = ?",array($_GET['reject']))->fetchArray();
    $acc = CheckLevel($ur['ID'],$prr['UID']);
    if ($acc > 0)
        QQ("UPDATE PROSON SET STATE = -1 WHERE ID = ?",array($prr['ID']));
    redirect(sprintf("check.php?t=%s",$_GET['t']));
    die;
}

echo '<div class="content" style="margin: 20px">';
echo 'Έλεγχος Προσόντων<hr>';

$params = json_decode($role['ROLEPARAMS'],true);
$afms = $params['afms'];

?>
'<table class="table datatable">
    <thead>
        <th>ΑΦΜ</th>
        <th>Όνομα</th>
        <th>Προσόντα</th>
    </thead>
    <tbody>
<?php

foreach($afms as $afm)
{
    $cr = QQ("SELECT * FROM USERS WHERE AFM = ?",array($afm))->fetchArray();
    if (!$cr)
        continue;

    printf('<tr>');
    printf('<td>%s</td>',$afm);
    printf('<td>%s %s</td>',$cr['LASTNAME'],$cr['FIRSTNAME']);
    printf('<td>');


    echo PrintProsonta($cr['ID'],$ur['ID'],$role);
    
    printf('</td>');
    printf('</tr>');
}
?>
        
        </tbody>
</table>
