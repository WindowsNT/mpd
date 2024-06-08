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

$placerow = QQ("SELECT * FROM PLACES WHERE ID = ?",array($req['pid']))->fetchArray();
if (!$placerow)
{
    redirect("index.php");
    die;
}

function ViewOrEditPos($posid)
{

}

if (array_key_exists("addposition",$_POST))
{
    QQ("INSERT INTO POSITIONS (CID,PLACEID,DESCRIPTION,COUNT) VALUES(?,?,?,?)",array(
        $req['cid'],$req['pid'],$req['DESCRIPTION'],$req['COUNT']
    ));
    redirect(sprintf("positions.php?t=%s&cid=%s&pid=%s",$_POST['t'],$_POST['cid'],$_POST['pid']));
    die;
}


printf('<button href="contest.php?t=%s" class="autobutton button  is-danger">Πίσω</button><hr> ',$req['t']);
printf('Θέσεις σε φορέα: %s<hr>',$placerow['DESCRIPTION']);

?>
<table class="table datatable">
    <thead>
        <th>#</th>
        <th>θέση</th>
        <th>Διαθεσιμότητα</th>
        <th>Επιλογές</th>
    </thead>
    <tbody>
    <?php
    $q4 = QQ("SELECT * FROM POSITIONS WHERE CID = ? AND PLACEID = ?",array($req['cid'],$req['pid']));
    while($r4 = $q4->fetchArray())
    {
        printf('<tr>');
        printf('<td>%s</td>',$r4['ID']);
        printf('<td>%s</td>',$r4['DESCRIPTION']);
        printf('<td>%s</td>',$r4['COUNT']);
        printf('<td><button class="is-small autobutton is-link button" href="prosonta.php?t=%s&cid=%s&pid=%s&pos=%s">Προσόντα</button></td>',$req['t'],$req['cid'],$req['pid'],$r4['ID']);
        printf('</tr>');
    }
    ?>

    </tbody>
</table>

<br><br>
Προσθήκη Θέσης<hr>
        <form method="POST" action="positions.php">
    <input type="hidden" name="cid" value="<?= $req['cid'] ?>" />
    <input type="hidden" name="t" value="<?= $req['t'] ?>" />
    <input type="hidden" name="pid" value="<?= $req['pid'] ?>" />
    <input type="hidden" name="addposition" value="1" />

        <label for="DESCRIPTION">Όνομα θέσης:</label>
        <input type="text" name="DESCRIPTION" class="input" required/>
        <br><br>
        <label for="COUNT">Αριθμός διαθέσιμων θέσεων:</label>
        <input type="number" name="COUNT" class="input" required/>
        <br><br>

        <button class="button is-success">Υποβολή<button>
    </form>

    <?php
