<?php

require_once "function.php";
require_once "auth.php";
require_once "output.php";

if (!$afm || !$ur)
    {
        redirect("index.php");
        die;
    }

if (array_key_exists("e",$_POST))
{
    if ($_POST['e'] > 0)
    {
        $lastRowID = $_POST['e'];
        QQ("UPDATE PROSON SET DESCRIPTION = ?,CLASSID = ?,STARTDATE = ?,ENDDATE = ?,STATE = 0 WHERE ID = ? ",array(
           $_POST['DESCRIPTION'],$_POST['CLASSID'],strtotime($_POST['STARTDATE']),strtotime($_POST['ENDDATE']),$lastRowID
        ));
        $lastRowID = $_POST['e'];
        QQ("DELETE FROM PROSONPAR WHERE PID = ?",array($lastRowID));
        $lastRowID = $_POST['e'];
    
    }
    else    
    QQ("INSERT INTO PROSON (UID,CLSID,DESCRIPTION,CLASSID,STARTDATE,ENDDATE) VALUES (?,?,?,?,?,?) ",array(
        $ur['ID'],guidv4(),$_POST['DESCRIPTION'],$_POST['CLASSID'],strtotime($_POST['STARTDATE']),strtotime($_POST['ENDDATE'])
    ));

    if ($lastRowID)
    {
        $pid = $lastRowID;
        foreach($_POST as $key=>$value)
        {
            if (strstr($key,"param_"))
            {
                $pi = (int)substr($key,6);
                QQ("INSERT INTO PROSONPAR (PID,PIDX,PVALUE) VALUES (?,?,?)",array(
                    $pid,$pi,$value
                ));
            }
        }
    }
    redirect("proson.php");
    die;
}

echo '<div class="content" style="margin: 20px">';


function ViewOrEdit($pid,$items)
{
    global $ur,$xmlp;
    EnsureProsonLoaded();
    if ($pid)
        $items = QQ("SELECT * FROM PROSON WHERE ID = ? AND UID = ?",array($pid,$ur['ID']))->fetchArray();
    if (!$items)
        $items = array('ID' => '0','UID' => $ur['ID'],'CLSID' => guidv4(),'DESCRIPTION' => '','CLASSID' => 0,'STARTDATE' => '0','ENDDATE' => '0');

    if ($items['CLASSID'] == 0 && array_key_exists("CLASSID",$_GET))
        $items['CLASSID'] = $_GET['CLASSID'];

    // Find root
    $croot = RootForClassId($xmlp->classes,$items['CLASSID']);
    if ($croot && $croot->c)
    {
        ?>
        <form method="GET" action="proson.php">
            <input type="hidden" name="e" value="0" />
        <label for="CLASSID">Επιλογή Τύπου Προσόντος:</label>
            <select class="select" name="CLASSID">
                <?php
                foreach($croot->c as $c)
                {
                    $attr = $c->attributes();
                    printf('<option value="%s">%s</option>',$attr['n'],$attr['t']);
                }
                ?>
            </select>
            <button class="button is-link">Συνέχεια<button>
        </form>
        <?php
        return;
    }
    
    ?>
    <form method="POST" action="proson.php">
    <input type="hidden" name="e" value="<?= $items['ID'] ?>" />
        <input type="hidden" name="CLASSID" value="<?= $items['CLASSID'] ?>" />

        <label for="DESCRIPTION">Περιγραφή</label>
        <input type="text" name="DESCRIPTION" class="input" value="<?= $items['DESCRIPTION'] ?>" required/>
        <br><br>

        <label for="STARTDATE">Ημερομηνία Έναρξης</label>
        <input type="date" name="STARTDATE" class="input" value="<?= $items['STARTDATE'] > 0 ? date("Y-m-d",$items['STARTDATE']) : "" ?>"/>
        <br><br>

        <label for="STARTDATE">Ημερομηνία Λήξης</label>
        <input type="date" name="ENDDATE" class="input" value="<?= $items['ENDDATE'] > 0 ? date("Y-m-d",$items['ENDDATE']) : "" ?>"/>
        <br><br>

        <?php
        $params_root = $croot->params;
        if ($params_root)
        foreach($params_root->p as $param)
        {
            $pa = $param->attributes();                         
            $parval = '';
            if ($_GET['e'] > 0)
                $parval = QQ("SELECT * FROM PROSONPAR WHERE PIDX = ? AND PID = ?",array($pa['id'],$_GET['e']))->fetchArray()['PVALUE'];

            if ($pa['t'] == 0) // Text
            {
                printf('<label for="param_%s">%s</label><input class="input" type="text" name="param_%s" value="%s" /><br><br>',$pa['id'],$pa['n'],$pa['id'],$parval);
            }
            if ($pa['t'] == 2) // float
            {
                printf('<label for="param_%s">%s</label><input class="input" type="number" step="0.01" min="%s" max="%s" name="param_%s" value="%s"  /><br><br>',$pa['id'],$pa['n'],$pa['min'],$pa['max'],$pa['id'],$parval);
            }
        }
        ?>
        <button class="button is-success">Υποβολή<button>
    </form>
    <?php
}


if (array_key_exists("e",$_GET))
{
    ViewOrEdit($_GET['e'],null);
    echo '<button class="button is-danger autobutton" href="proson.php">Πίσω</button>';
    die;
}


if (array_key_exists("delete",$_GET))
{
    DeleteProson($_GET['delete'],$ur['ID']);
    redirect("proson.php");
    die;
}

printf('<button href="index.php" class="autobutton button is-danger">Πίσω</button> <button class="button is-primary autobutton" href="proson.php?e=0">Νέο Προσόν</button><br><br>Λίστα Προσόντων<hr>');
echo PrintProsonta($ur['ID']);
?>
