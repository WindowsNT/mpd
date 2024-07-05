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

$cidrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($req['cid']))->fetchArray();
if (!$cidrow)
{
    redirect("index.php");
    die;
}

if (array_key_exists("DESCRIPTION",$_POST))
{
    QQ("DELETE FROM POSITIONGROUPS WHERE CID = ?",array($_POST['cid']));
    $gl = explode(",",$_POST['DESCRIPTION']);
    sort($gl);
    $fl = implode(",",$gl);
    QQ("INSERT INTO POSITIONGROUPS (CID,GROUPLIST) VALUES(?,?)",array($_POST['cid'],$fl));
    redirect(sprintf("contest.php?t=%s",$_POST['t']));
    die;

}

printf('<button href="contest.php?t=%s" class="autobutton button  is-danger">Πίσω</button> ',$req['t']);
$v = '';
$grouprow = QQ("SELECT * FROM POSITIONGROUPS WHERE CID = ?",array($req['cid']))->fetchArray();
if ($grouprow)
    $v = $grouprow['GROUPLIST'];
?>

<form method="POST" action="positiongroups.php">
    <input type="hidden" name="t" value="<?= $req['t'] ?>" />
    <input type="hidden" name="cid" value="<?= $req['cid'] ?>" />

    <br>
    <br>
        <label for="DESCRIPTION">Δώστε τις πιθανές θέσεις, διαχωρισμένες με κόμμα:</label>
        <input type="text" name="DESCRIPTION" class="input" required value="<?= $v ?>"/>
        <br><br>

        <button class="button is-success ">Υποβολή<button>
    </form>

    <?php

?>
<br><br><br>
Προσόντα για κάθε θέση:
<br>
<table class="table">
    <thead>
        <th></th>
        <th></th>
    </thead>
    <tbody>
        <?php
        foreach(explode(",",$v) as $vv)
        {
            $count = QQ("SELECT COUNT(*) FROM REQS2 WHERE FORTHESI = ?",array($vv))->fetchArray()[0];
            printf('<tr><td>%s</td><td><a class="button is-link is-small autobutton" href="prosonta3.php?t=%s&cid=%s&placeid=0&forthesi=%s">Προσόντα %s</a></td></tr>',$vv,$req['t'],$req['cid'],$vv,$count);
        }
        ?>
    </tbody>
</table>

<?php
    die;

