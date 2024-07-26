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


if (array_key_exists("addplace",$_POST))
{
    QQ("INSERT INTO PLACES (CID,PARENTPLACEID,DESCRIPTION) VALUES(?,?,?)",array(
        $req['cid'],$req['par'],$req['DESCRIPTION']
    ));
    redirect(sprintf("contest.php"));
    die;
}

if (array_key_exists("deleteplace",$req))
{
    QQ("DELETE FROM PLACES WHERE ID = ?",array(
        $req['pid']
    ));
    redirect(sprintf("contest.php"));
    die;
}

if (array_key_exists("addplace",$_GET))
{
    ?>
        <form method="POST" action="contest.php">
    <input type="hidden" name="cid" value="<?= $req['cid'] ?>" />
    <input type="hidden" name="par" value="<?= $req['par'] ?>" />
    <input type="hidden" name="addplace" value="1" />

        <label for="DESCRIPTION">Όνομα νέου φορέα:</label>
        <input type="text" name="DESCRIPTION" class="input" required/>
        <br><br>

        <button class="button is-success">Υποβολή<button>
    </form>

    <?php
    die;
}



if (array_key_exists("editplace",$_POST))
{
    QQ("UPDATE PLACES SET DESCRIPTION = ? WHERE ID = ?",array(
        $req['DESCRIPTION'],$req['editplace']
    ));
    redirect(sprintf("contest.php"));
    die;
}

if (array_key_exists("editplace",$_GET))
{
    $pr = Single("PLACES","ID",$req['pid']);
    ?>

    <form method="POST" action="contest.php">
    <input type="hidden" name="editplace" value="<?= $req['pid'] ?>" />

        <label for="DESCRIPTION">Όνομα φορέα:</label>
        <input type="text" name="DESCRIPTION" class="input" required value="<?= $pr['DESCRIPTION'] ?>"/>
        <br><br>

        <button class="button is-success">Υποβολή<button>
    </form>

    <?php
    die;
}



if (array_key_exists("c",$_POST))
{
    if ($_POST['c'] > 0)
    {
        if (!HasContestAccess($_POST['c'],$ur['ID'],1))
            die;
        QQ("UPDATE CONTESTS SET DESCRIPTION = ?,LONGDESCRIPTION = ?,FIRSTPREFSCORE = ?,MORIAVISIBLE = ?,MINISTRY = ?,CATEGORY = ?,STARTDATE = ?,ENDDATE = ?,CLASSID = ? WHERE ID = ? ",array(
           $_POST['DESCRIPTION'],$_POST['LONGDESCRIPTION'],$_POST['FIRSTPREFSCORE'],$_POST['MORIAVISIBLE'],$_POST['MINISTRY'],$_POST['CATEGORY'],strtotime($_POST['STARTDATE']),strtotime($_POST['ENDDATE']),$_POST['CLASSID'],$_POST['c']
        ));
        $lastRowID = $_POST['c'];
    }
    else    
    QQ("INSERT INTO CONTESTS (UID,DESCRIPTION,LONGDESCRIPTION,FIRSTPREFSCORE,MORIAVISIBLE,MINISTRY,CATEGORY,STARTDATE,ENDDATE,CLASSID) VALUES (?,?,?,?,?,?,?,?,?,?) ",array(
        $ur['ID'],$_POST['DESCRIPTION'],$_POST['LONGDESCRIPTION'],$_POST['FIRSTPREFSCORE'],$_POST['MORIAVISIBLE'],$_POST['MINISTRY'],$_POST['CATEGORY'],strtotime($_POST['STARTDATE']),strtotime($_POST['ENDDATE']),$_POST['CLASSID'],
    ));

    if ($lastRowID)
    {

    }
    redirect(sprintf("contest.php"));
    die;
}

function ViewOrEdit($cid)
{
    global $ur,$superadmin;
    $items = array();
    if ($cid)
        {
            if ($superadmin)
                $items = Single("CONTESTS","ID",$cid);
            else
                $items = QQ("SELECT * FROM CONTESTS WHERE ID = ? AND UID = ?",array($cid,$ur['ID']))->fetchArray();
        }
    if (!$items)
        $items = array('ID' => '0','UID' => $ur['ID'],'CLSID' => guidv4(),'DESCRIPTION' => '','LONGDESCRIPTION' => '','FIRSTPREFSCORE' => 2.0,'MORIAVISIBLE' => 0,'STARTDATE' => '0','ENDDATE' => '0',"MINISTRY" => "","CATEGORY" => '',"CLASSID" => 0);

    ?>
    <form method="POST" action="contest.php">
    <input type="hidden" name="c" value="<?= $items['ID'] ?>" />

        <label for="MINISTRY">Υπουργείο</label>
        <input type="text" name="MINISTRY" class="input" value="<?= $items['MINISTRY'] ?>" required/>
        <br><br>

        <label for="CATEGORY">Κατηγορία</label>
        <input type="text" name="CATEGORY" class="input" value="<?= $items['CATEGORY'] ?>" required/>
        <br><br>

        <label for="DESCRIPTION">Περιγραφή</label>
        <input type="text" name="DESCRIPTION" class="input" value="<?= $items['DESCRIPTION'] ?>" required/>
        <br><br>

        <label for="LONGDESCRIPTION">Αναλυτική Περιγραφή</label>
        <textarea name="LONGDESCRIPTION" class="summernote" rows="10"><?= $items['LONGDESCRIPTION'] ?></textarea>
        <br><br>

        <label for="CLASSID">Αλγόριθμος Υπολογισμού Μορίων</label>
        <select name="CLASSID" class="input">
            <option value="0" <?= $items['CLASSID'] == 0 ? "selected" : "" ?>>Προεπιλογή</option>
            <option value="101" <?= $items['CLASSID'] == 101 ? "selected" : "" ?>>Μεταθέσεις Μουσικών Σχολείων</option>
            <option value="102" <?= $items['CLASSID'] == 102 ? "selected" : "" ?>>Αποσπάσεις Μουσικών Σχολείων</option>
        </select>
        <br><br>

        <label for="FIRSTPREFSCORE">Μόρια Πρώτης Προτίμησης</label>
        <input type="number" step="0.01" name="FIRSTPREFSCORE" class="input" value="<?= $items['FIRSTPREFSCORE'] ?>" required/>
        <br><br>

        <label for="MORIAVISIBLE">Ορατά τα μόρια στους αιτούντες (0-2)</label>
        <input type="number" min="0" max="2" name="MORIAVISIBLE" class="input" value="<?= $items['MORIAVISIBLE'] ?>" required/>
        <br><br>

        <label for="STARTDATE">Ημερομηνία Έναρξης Αιτήσεων</label>
        <input type="date" name="STARTDATE" class="input" value="<?= $items['STARTDATE'] > 0 ? date("Y-m-d",$items['STARTDATE']) : "" ?>" required/>
        <br><br>

        <label for="STARTDATE">Ημερομηνία Λήξης Αιτήσεων</label>
        <input type="date" name="ENDDATE" class="input" value="<?= $items['ENDDATE'] > 0 ? date("Y-m-d",$items['ENDDATE']) : "" ?>" required/>
        <br><br>

        <button class="button is-success">Υποβολή<button>
    </form>
    <?php
}

$id = 0;
if (array_key_exists("c",$_GET))
    {
        $id = $_GET['c'];
        ViewOrEdit($id);
    }
else
{
    printf('<button href="index.php" class="autobutton button  is-danger">Πίσω</button> ');
    printf('<button class="autobutton button  is-primary" href="contest.php?c=0">Νέος</button> ');
    
       echo PrintContests($ur['ID']);
    ?>
    <?php
}