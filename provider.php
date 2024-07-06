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

$params = json_decode($rolerow['ROLEPARAMS'],true);
$provider_data = base64_decode($params['restriction']);
if (array_key_exists("delete",$req))
{
    $what = QQ("SELECT * FROM USERS WHERE CLSID = ?",array($req['force_user']))->fetchArray()['ID'];
    if ($what)
        DeleteProson($req['delete'],$what);
    redirect(sprintf('provider.php?t=%s',$req['t']));
    die;
}

function PrintList()
{
    global $provider_data,$req,$ur;
    $x2 = simplexml_load_string($provider_data);
    $our_codes = array();
    GetAllClassesInXML($x2->classes,$our_codes);
    $q1 = QQ("SELECT * FROM PROSON");
    ?>
    <table class="table datatable">
    <thead>
        <th>#</th>
        <th>Όνομα</th>
        <th>ΑΦΜ</th>
        <th>Περιγραφή</th>
        <th>Έναρξη</th>
        <th>Λήξη</th>
        <th>Αρχεία</th>
        <th>Παράμετροι</th>
        <th>Κατάσταση</th>
        <th>Ενέργειες</th>
    </thead>
    <tbody>
    <?php
    while($r1 = $q1->fetchArray())
    {
        // Check if this belongs to us
        if (!HasProsonAccess($r1['ID'],$ur['ID'],0))
            continue;


        $ur2 = QQ("SELECT * FROM USERS WHERE ID = ?",array($r1['UID']))->fetchArray();
        if (!$ur2)
            continue;

        printf('<tr>');
        printf('<td>%s</td>',$r1['ID']);
        printf('<td>%s %s</td>',$ur2['LASTNAME'],$ur2['FIRSTNAME']);
        printf('<td>%s</td>',$ur2['AFM']);
        printf('<td>%s</td>',$r1['DESCRIPTION']);
        printf('<td>%s</td>',date("d/m/Y",$r1['STARTDATE']));
        printf('<td>%s</td>',$r1['ENDDATE'] < $r1['STARTDATE'] ? '-' : date("d/m/Y",$r1['ENDDATE']));

        $files = '';
        $q3 = QQ("SELECT * FROM PROSONFILE WHERE PID = ?",array($r1['ID']));
        while($r3 = $q3->fetchArray())
        {
            $files .= sprintf('<a href="viewfile.php?f=%s" target="_blank"><b>%s</b><br>',$r3['ID'],$r3['DESCRIPTION']);
        }
        if ($r1['STATE'] <= 0)
        {
            $files .= sprintf('<br><br><button class="is-link is-small button autobutton" href="files.php?e=%s&f=0&force_user=%s">Διαχείριση</button>',$r1['ID'],$ur2['CLSID']);
        }
        printf('<td>%s</td>',$files);

        $pl = '';
        $q3 = QQ("SELECT * FROM PROSONPAR WHERE PID = ?",array($r1['ID']));
        while($r3 = $q3->fetchArray())
        {
            $pl .= sprintf("%s<br>",$r3['PVALUE']);
        }
        printf('<td>%s</td>',$pl);
        printf('<td>%s</td>',$r1['STATE'] == 1 ? "OK" : ($r1['STATE'] == 0 ? "Αναμονή" : "Απόρριψη"));

        $en = '';
        if ($r1['STATE'] <= 0)
        {
            // Actions only when not confirmed
            $en .= sprintf('<button class="sureautobutton is-small is-danger button" href="provider.php?t=%s&delete=%s&force_user=%s">Διαγραφή</button>',$req['t'],$r1['ID'],$ur2['CLSID']);
        }
        printf('<td>%s</td>',$en);
        printf('</tr>');

    }
    ?>
    </tbody></table>
    <?php
}


printf('<button href="index.php" class="autobutton button  is-danger">Πίσω</button> ');
if (!array_key_exists("e",$req))
{
    unset($_SESSION['constraint']);
    printf('<button href="provider.php?t=%s&e=0" class="autobutton button  is-primary">Νεα Απόδοση</button> ',$req['t']);

    PrintList();
    die;
}

$_SESSION['constraint'] = $provider_data;

if (array_key_exists("lastname",$_POST))
{
    $urx= QQ("SELECT * FROM USERS WHERE LASTNAME = ? AND FIRSTNAME = ? AND AFM = ?",array($req['lastname'],$req['firstname'],$req['afm']))->fetchArray();
    if (!$urx)
    {
        redirect(sprintf("provider.php?t=%s",$req['t'])); die;
    }
    redirect(sprintf("proson.php?e=0&force_user=%s",$urx['CLSID'])); die;
}
?>
<br><br>
 <form method="POST" action="provider.php">
    <input type="hidden" name="t" value="<?= $req['t'] ?>" />
    <input type="hidden" name="e" value="1" />
    <label for="lastname">Επίθετο</label><input type="text" name="lastname" class="input"  required/><br><br>
    <label for="firstname">Όνομα</label><input type="text" name="firstname" class="input"  required/><br><br>
    <label for="afm">ΑΦΜ</label><input type="text" name="afm" class="input"  required/><br><br>
        <br><br>
    <button class="button is-success">Yποβολή</button>
</form>
<?php

