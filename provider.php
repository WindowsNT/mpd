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

$params = array();

$q1 = QQ("SELECT * FROM ROLEPAR WHERE RID = ?",array($rolerow['ID']));
while($r1 = $q1->fetchArray())
    $params[$r1['PIDX']] = $r1['PVALUE'];

$provider_data = $params[1];

function PrintList()
{
    $q1 = QQ("SELECT * FROM PROSON");
    while($r1 = $q1->fetchArray())
    {
        // Check if this belongs to us
    }
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
    $_SESSION['target_id'] = $urx['ID'];
    redirect("proson.php?e=0"); die;
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

