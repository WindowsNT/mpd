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

if (array_key_exists("addplace",$_POST))
{
    QQ("INSERT INTO PLACES (CID,PARENTPLACEID,DESCRIPTION) VALUES(?,?,?)",array(
        $req['cid'],$req['par'],$req['DESCRIPTION']
    ));
    redirect(sprintf("contest.php?t=%s",$_POST['t']));
    die;
}

if (array_key_exists("deleteplace",$req))
{
    QQ("DELETE FROM PLACES WHERE ID = ?",array(
        $req['pid']
    ));
    redirect(sprintf("contest.php?t=%s",$req['t']));
    die;
}

if (array_key_exists("addplace",$_GET))
{
    ?>
        <form method="POST" action="contest.php">
    <input type="hidden" name="cid" value="<?= $req['cid'] ?>" />
    <input type="hidden" name="t" value="<?= $req['t'] ?>" />
    <input type="hidden" name="par" value="<?= $req['par'] ?>" />
    <input type="hidden" name="addplace" value="1" />

        <label for="DESCRIPTION">Όνομα νέου φορέα:</label>
        <input type="text" name="DESCRIPTION" class="input" required/>
        <br><br>

        <button class="button is-link is-small">Υποβολή<button>
    </form>

    <?php
    die;
}



if (array_key_exists("editplace",$_POST))
{
    QQ("UPDATE PLACES SET DESCRIPTION = ? WHERE ID = ?",array(
        $req['DESCRIPTION'],$req['editplace']
    ));
    redirect(sprintf("contest.php?t=%s",$_POST['t']));
    die;
}

if (array_key_exists("editplace",$_GET))
{
    $pr = QQ("SELECT * FROM PLACES WHERE ID = ?",array($req['pid']))->fetchArray();
    ?>

    <form method="POST" action="contest.php">
    <input type="hidden" name="t" value="<?= $req['t'] ?>" />
    <input type="hidden" name="editplace" value="<?= $req['pid'] ?>" />

        <label for="DESCRIPTION">Όνομα φορέα:</label>
        <input type="text" name="DESCRIPTION" class="input" required value="<?= $pr['DESCRIPTION'] ?>"/>
        <br><br>

        <button class="button is-link is-small">Υποβολή<button>
    </form>

    <?php
    die;
}



if (array_key_exists("c",$_POST))
{
    if ($_POST['c'] > 0)
    {
        QQ("UPDATE CONTESTS SET DESCRIPTION = ?,STARTDATE = ?,ENDDATE = ? WHERE ID = ? ",array(
           $_POST['DESCRIPTION'],strtotime($_POST['STARTDATE']),strtotime($_POST['ENDDATE']),$lastRowID
        ));
        $lastRowID = $_POST['c'];
    }
    else    
    QQ("INSERT INTO CONTESTS (UID,DESCRIPTION,STARTDATE,ENDDATE) VALUES (?,?,?,?) ",array(
        $ur['ID'],$_POST['DESCRIPTION'],strtotime($_POST['STARTDATE']),strtotime($_POST['ENDDATE'])
    ));

    if ($lastRowID)
    {

    }
    redirect(sprintf("contest.php?t=%s",$_POST['t']));
    die;
}

function ViewOrEdit($cid)
{
    global $ur;
    $items = array();
    if ($cid)
        $items = QQ("SELECT * FROM CONTENTS WHERE ID = ? AND UID = ?",array($cid,$ur['ID']))->fetchArray();
    if (!$items)
        $items = array('ID' => '0','UID' => $ur['ID'],'CLSID' => guidv4(),'DESCRIPTION' => '','STARTDATE' => '0','ENDDATE' => '0');

    ?>
    <form method="POST" action="contest.php">
    <input type="hidden" name="c" value="<?= $items['ID'] ?>" />
    <input type="hidden" name="t" value="<?= $_GET['t'] ?>" />

        <label for="DESCRIPTION">Περιγραφή</label>
        <input type="text" name="DESCRIPTION" class="input" value="<?= $items['DESCRIPTION'] ?>" required/>
        <br><br>

        <label for="STARTDATE">Ημερομηνία Έναρξης</label>
        <input type="date" name="STARTDATE" class="input" value="<?= $items['STARTDATE'] > 0 ? date("Y-m-d",$items['STARTDATE']) : "" ?>" required/>
        <br><br>

        <label for="STARTDATE">Ημερομηνία Λήξης</label>
        <input type="date" name="ENDDATE" class="input" value="<?= $items['ENDDATE'] > 0 ? date("Y-m-d",$items['ENDDATE']) : "" ?>" required/>
        <br><br>

        <button class="button is-link is-small">Υποβολή<button>
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
    printf('<button class="autobutton button  is-primary" href="contest.php?c=0&t=%s">Νέος</button> ',$req['t']);
    printf('<button class="autobutton button  is-link" href="positiongroups.php?t=%s">Γκρουπ Θέσεων</button>',$req['t']);
       echo PrintContests($req['t'],$ur['ID']);
    ?>
    <?php
}