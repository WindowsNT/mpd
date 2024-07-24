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

$is_foreas_editing = 0;
$ra = HasContestAccess($req['cid'],$ur['ID'],0);
$wa = HasContestAccess($req['cid'],$ur['ID'],1);
if (!$wa)
{
    // Check place access
    if (!HasPlaceAccessForKena($req['pid'],$ur['ID']))
    {
        redirect("index.php");
        die;
    }
    $is_foreas_editing = 1;
}

//*

$placerow = Single("PLACES","ID",$req['pid']);
if (!$placerow)
{
    redirect("index.php");
    die;
}

function ViewOrEditPos($posid)
{

}

if (array_key_exists("movefromglobal",$_GET) && $wa)
{
    // cid contest, pid place, movefromglobal pos
    BeginTransaction();
    $prosonrow = Single("POSITIONS","ID",$req['movefromglobal']); 
    if ($prosonrow)
    {
        $globalrow = QQ("SELECT * FROM REQS2 WHERE CID = ? AND FORTHESI = ?",array($req['cid'],$prosonrow['DESCRIPTION']));
        $dups = array();
        while($r1 = $globalrow->fetchArray())
        {
            QQ("INSERT INTO REQS2 (CID,PLACEID,POSID,PROSONTYPE,SCORE,REGEXRESTRICTIONS) VALUES(?,?,?,?,?,?)",array($req['cid'],$req['pid'],$req['movefromglobal'],$r1['PROSONTYPE'],$r1['SCORE'],$r1['REGEXRESTRICTIONS']));
            $dups[$r1['ID']] = $lastRowID;
        }

        foreach($dups as $k => $newid)
        {
            $old_row = Single("REQS","ID",$k);

            if (!$old_row || !$newid)
                continue;

            $neworlink = 0;
            $newnotlink = 0;
            $newandlink = 0;
            if ((int)$old_row['ORLINK'] != 0)
                    $neworlink = $dups[(int)$old_row['ORLINK']];
            if ((int)$old_row['NOTLINK'] != 0)
                    $newnotlink = $dups[(int)$old_row['NOTLINK']];
            if ((int)$old_row['ANDLINK'] != 0)
                    $newandlink = $dups[(int)$old_row['ANDLINK']];
            QQ("UPDATE REQS2 SET ORLINK = ?,NOTLINK = ?,ANDLINK = ? WHERE ID = ?",array($neworlink,$newnotlink,$newandlink,$newid));

        }
    }
//    QQ("ROLLBACK");
    QQ("COMMIT");
    redirect(sprintf("positions.php?cid=%s&pid=%s",$req['cid'],$req['pid']));
    die;
   
}

if (array_key_exists("delete",$_GET) && ($wa || $is_foreas_editing))
{
    QQ("DELETE FROM POSITIONS WHERE ID = ?",array(
        $req['delete']
    ));
    redirect(sprintf("positions.php?cid=%s&pid=%s",$req['cid'],$req['pid']));
    die;
}

if (array_key_exists("addposition",$_POST) && ($wa || $is_foreas_editing))
{
    $ex = QQ("SELECT * FROM POSITIONS WHERE CID = ? AND PLACEID = ? AND DESCRIPTION = ?",array($req['cid'],$req['pid'],$req['DESCRIPTION'],))->fetchArray();
    if (!$ex)
        QQ("INSERT INTO POSITIONS (CID,PLACEID,DESCRIPTION,COUNT) VALUES(?,?,?,?)",array(
        $req['cid'],$req['pid'],$req['DESCRIPTION'],$req['COUNT']
        ));
    redirect(sprintf("positions.php?cid=%s&pid=%s",$_POST['cid'],$_POST['pid']));
    die;
}


if ($is_foreas_editing)
    printf('<button href="index.php" class="autobutton button  is-danger">Πίσω</button><hr> ');
else
    printf('<button href="contest.php" class="autobutton button  is-danger">Πίσω</button><hr> ');
printf('Θέσεις σε φορέα: %s<hr>',$placerow['DESCRIPTION']);

?>
<table class="table datatable" style="width: 100%">
    <thead>
        <th class="all">#</th>
        <th class="all">θέση</th>
        <th class="all">Διαθεσιμότητα</th>
        <th class="all">Προσόντα</th>
        <th class="all">Επιλογές</th>
    </thead>
    <tbody>
    <?php
    $crow = Single("CONTESTS","ID",$req['cid']);
    $q4 = QQ("SELECT * FROM POSITIONS WHERE CID = ? AND PLACEID = ?",array($req['cid'],$req['pid']));
    while($r4 = $q4->fetchArray())
    {
        printf('<tr>');
        printf('<td>%s</td>',$r4['ID']);
        printf('<td>%s</td>',$r4['DESCRIPTION']);
        printf('<td>%s</td>',$r4['COUNT']);
        printf('<td>');
        if (!$is_foreas_editing)
            {
                $CountY = QQ("SELECT COUNT (*) FROM REQS2 WHERE CID = ? AND FORTHESI = ?",array($req['cid'],$r4['DESCRIPTION']))->fetchArray()[0];
                if ($crow['CLASSID'] != 0)
                    printf('Προσόντα Ορισμένα από το Σύστημα &nbsp;');
                else
                if ($CountY) 
                    printf('Προσόντα Κοινά από τον Διαγωνισμό &nbsp;');
                else
                {
                    if ($wa)
                    {
                        $CountX = QQ("SELECT COUNT (*) FROM REQS2 WHERE CID = ? AND POSID = ?",array($req['cid'],$r4['ID']))->fetchArray()[0];
                        printf('<button class="is-small autobutton is-link button" href="prosonta3.php?cid=%s&placeid=%s&posid=%s">Προσόντα %s</button> ',$req['cid'],$req['pid'],$r4['ID'],$CountX);
                    }
/*                if ($CountX == 0)   
                    {
                        if ($CountY)+
                            printf('<button class="is-small autobutton is-warning button" href="positions.php?cid=%s&pid=%s&movefromglobal=%s">Μεταφορά από Global %s</button> ',$req['cid'],$req['pid'],$r4['ID'],$CountY);
                    }
*/
                }   

            }
        printf('</td>');
        printf('<td>');
        if (!$is_foreas_editing)
        {
            $aitcount = QQ("SELECT COUNT(*) FROM APPLICATIONS WHERE CID = ? AND PID = ? AND POS = ?",array($req['cid'],$req['pid'],$r4['ID']))->fetchArray()[0];
            printf('<button class="autobutton button is-small is-primary block" href="listapps.php?cid=%s&pid=%s&pos=%s">Λίστα Αιτήσεων (%s)</button> ',$req['cid'],$req['pid'],$r4['ID'],$aitcount);
        }
        if ($wa || $is_foreas_editing)
            printf('<button class="is-small sureautobutton is-danger button" href="positions.php?cid=%s&pid=%s&delete=%s">Διαγραφή</button>',$req['cid'],$req['pid'],$r4['ID']);
        printf('</td>');
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
<?php
if ($wa || $is_foreas_editing)
{
?>
<div id="addpos1">
<button class="button is-primary" onclick="toggleadd();">Προσθήκη θέσης</button>
</div>
<?php
}
?>
<div id="addpos" style="display:none">
Προσθήκη Θέσης<hr>
        <form method="POST" action="positions.php">
    <input type="hidden" name="cid" value="<?= $req['cid'] ?>" />
    <input type="hidden" name="pid" value="<?= $req['pid'] ?>" />
    <input type="hidden" name="addposition" value="1" />

        <label for="DESCRIPTION">Θέση:</label>
        <?php
        $grouprow = Single("POSITIONGROUPS","CID",$req['cid']); 
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
