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

$is_foreas_editing = 0;
if ($rolerow['ROLE'] == ROLE_CREATOR)
{
}
else
if ($rolerow['ROLE'] == ROLE_FOREASSETPLACES)
{
    $roleparx = QQ("SELECT * FROM ROLEPAR WHERE RID = ? AND PVALUE = ?",array($rolerow['ID'],$req['pid']))->fetchArray();
    if (!$roleparx)
        die;
    $is_foreas_editing = 1;
}
else
     die;

$placerow = QQ("SELECT * FROM PLACES WHERE ID = ?",array($req['pid']))->fetchArray();
if (!$placerow)
{
    redirect("index.php");
    die;
}

function ViewOrEditPos($posid)
{

}


if (array_key_exists("delete",$_GET))
{
    QQ("DELETE FROM POSITIONS WHERE ID = ?",array(
        $req['delete']
    ));
    redirect(sprintf("positions.php?t=%s&cid=%s&pid=%s",$req['t'],$req['cid'],$req['pid']));
    die;
}

if (array_key_exists("addposition",$_POST))
{
    QQ("INSERT INTO POSITIONS (CID,PLACEID,DESCRIPTION,COUNT) VALUES(?,?,?,?)",array(
        $req['cid'],$req['pid'],$req['DESCRIPTION'],$req['COUNT']
    ));
    redirect(sprintf("positions.php?t=%s&cid=%s&pid=%s",$_POST['t'],$_POST['cid'],$_POST['pid']));
    die;
}


if ($is_foreas_editing)
    printf('<button href="index.php?t=%s" class="autobutton button  is-danger">Πίσω</button><hr> ',$req['t']);
else
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
        printf('<td>');
        if (!$is_foreas_editing)
            printf('<button class="is-small autobutton is-link button" href="prosonta.php?t=%s&cid=%s&pid=%s&pos=%s">Προσόντα</button> ',$req['t'],$req['cid'],$req['pid'],$r4['ID']);
        printf('<button class="is-small sureautobutton is-danger button" href="positions.php?t=%s&cid=%s&pid=%s&delete=%s">Διαγραφή</button></td>',$r4['ID'],$req['t'],$req['cid'],$req['pid'],$r4['ID']);
        printf('</tr>');
    }
    ?>

    </tbody>
</table>

<br><br>
<script>
    function toggleadd()
    {
        $("#addpos1").hide();
        $("#addpos").show();
    }
    function toggleadd2()
    {
        $("#addpos1").show();
        $("#addpos").hide();
    }
</script>
<div id="addpos1">
<button class="button is-primary" onclick="toggleadd();">Προσθήκη θέσης</button>
</div>
<div id="addpos" style="display:none">
Προσθήκη Θέσης<hr>
        <form method="POST" action="positions.php">
    <input type="hidden" name="cid" value="<?= $req['cid'] ?>" />
    <input type="hidden" name="t" value="<?= $req['t'] ?>" />
    <input type="hidden" name="pid" value="<?= $req['pid'] ?>" />
    <input type="hidden" name="addposition" value="1" />

        <label for="DESCRIPTION">Θέση:</label>
        <?php
        $grouprow = QQ("SELECT * FROM POSITIONGROUPS WHERE CID = ?",array($req['cid']))->fetchArray();
        if ($grouprow)
        {
            echo '<select name="DESCRIPTION" class="input">';
            $list = explode(",",$grouprow['GROUPLIST']);
            foreach($list as $li)
            {
                printf('<option value="%s">%s</option>',$li,$li);
            }
            echo '</select>';
        }
        else
        {
            echo '<input type="text" name="DESCRIPTION" class="input" required/>';
        }
        ?>
        <br><br>
        <label for="COUNT">Αριθμός διαθέσιμων θέσεων:</label>
        <input type="number" name="COUNT" class="input" required/>
        <br><br>

        <button class="button is-success">Υποβολή<button>
    </form>
    <button class="button is-danger" onclick="toggleadd2();">Ακύρωση</button>

    </div>
    <?php
