<?php

require_once "function.php";
require_once "auth.php";
require_once "output.php";


if (!$afm || !$ur)
    {
        redirect("index.php");
        die;
    }

$uid = $ur['ID'];
if (array_key_exists("force_user",$req))
    $uid = QQ("SELECT * FROM USERS WHERE CLSID = ?",array($req['force_user']))->fetchArray()['ID'];

if (array_key_exists("e",$_POST))
{
    // Unique Parameters
    EnsureProsonLoaded();
    $rootc = RootForClassId($xmlp->classes,$_POST['CLASSID']);
    if ($rootc->params)
    {
        foreach($rootc->params->children() as $ch)
        {
            if (!$ch->attributes())
                continue;
            $uni = $ch->attributes()['unique'];
            if ($uni == 0)
                continue;
            $parid = $ch->attributes()['id'];
            $val = $_POST[sprintf("param_%s",$parid)];
            
            $others = QQ("SELECT * FROM PROSON WHERE UID = ? AND CLASSID = ?",array($ur['ID'],$_POST['CLASSID']));
            while($other = $others->fetchArray())
            {
                $que = QQ("SELECT * FROM PROSONPAR WHERE PID = ? AND PIDX = ? AND PVALUE = ?",array($other['ID'],$parid,$val))->fetchArray();
                if ($que && $other['ID'] != $_POST['e'])
                {
                    printf("Feature %s already found in another upload.",$val);
                    die;
                }
            }
        }

    }

    // Unique Class
    if ($rootc->attributes())
    {
        $uni = $rootc->attributes()['unique'];
        if ($uni == 1)
        {
            $others = QQ("SELECT * FROM PROSON WHERE UID = ? AND CLASSID = ?",array($ur['ID'],$_POST['CLASSID']));
            while($other = $others->fetchArray())
            {
                if ($other['ID'] != $_POST['e'])
                {
                    printf("Feature %s already found in another upload.",$rootc->attributes()['t']);
                    die;
                }
            }
    
        }
    }

   
    $newly = 0;
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
        {
            $newly = 1;
            QQ("INSERT INTO PROSON (UID,CLSID,DESCRIPTION,CLASSID,STARTDATE,ENDDATE) VALUES (?,?,?,?,?,?) ",array(
        $uid,guidv4(),$_POST['DESCRIPTION'],$_POST['CLASSID'],strtotime($_POST['STARTDATE']),strtotime($_POST['ENDDATE'])
    ));
        }

    $pid = 0;
    if ($lastRowID)
    {
        $pid = $lastRowID;
        foreach($_POST as $key=>$value)
        {
            if (strstr($key,"param_"))
            {
                $pi = (int)substr($key,6);
                if ($value == "on") 
                {
                    $value = 1;
                }
                QQ("INSERT INTO PROSONPAR (PID,PIDX,PVALUE) VALUES (?,?,?)",array(
                    $pid,$pi,$value
                ));
            }
        }
    }
    if (array_key_exists("force_user",$req))
    {
        redirect("provider.php"); die;
    }

    if ($newly)
        redirect(sprintf("files.php?e=%s&f=0",$pid));
    else
        redirect("proson.php");
    die;
}

echo '<div class="content" style="margin: 20px">';


function ViewOrEdit($pid,$items)
{
    global $xmlp,$_SESSION,$uid,$req;
    EnsureProsonLoaded();
    $xx = $xmlp;
    if (array_key_exists("constraint",$_SESSION))
        $xx = simplexml_load_string($_SESSION['constraint']);

    if ($pid)
        $items = QQ("SELECT * FROM PROSON WHERE ID = ? AND UID = ?",array($pid,$uid))->fetchArray();
    if (!$items)
        $items = array('ID' => '0','UID' => $uid,'CLSID' => guidv4(),'DESCRIPTION' => '','CLASSID' => 0,'STARTDATE' => '0','ENDDATE' => '0');

    if ($items['CLASSID'] == 0 && array_key_exists("CLASSID",$_GET))
        $items['CLASSID'] = $_GET['CLASSID'];

    // Find root
    $croot = RootForClassId($xx->classes,$items['CLASSID']);
    if ($croot && $croot->c)
    {
        ?>
        <form method="GET" action="proson.php">
            <input type="hidden" name="e" value="0"/>
            <?php
            if (array_key_exists("force_user",$req))
                printf('<input type="hidden" name="force_user" value="%s" />',$req['force_user']);
            ?>
        <label for="CLASSID">Επιλογή Τύπου Προσόντος:</label>
            <select class="input" name="CLASSID">
                <?php
                foreach($croot->c as $c)
                {
                    $attr = $c->attributes();
                    printf('<option value="%s">%s</option>',$attr['n'],$attr['t']);
                }
                ?>
            </select><br><br>
            <button class="button is-link">Συνέχεια<button>
        </form>
        <?php
        return;
    }
    
    ?>
    <form method="POST" action="proson.php">
    <input type="hidden" name="e" value="<?= $items['ID'] ?>" />
        <input type="hidden" name="CLASSID" value="<?= $items['CLASSID'] ?>" />

        <?php
            if (array_key_exists("force_user",$req))
                printf('<input type="hidden" name="force_user" value="%s" />',$req['force_user']);
            ?>

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
                {
                    $yu9 = QQ("SELECT * FROM PROSONPAR WHERE PIDX = ? AND PID = ?",array($pa['id'],$_GET['e']))->fetchArray();
                    if ($yu9)
                        $parval = $yu9['PVALUE'];
                }

            if ($pa['t'] == 0) // Text
            {
                if ($pa['v'] && strlen($pa['v']))
                    printf('<label for="param_%s">%s</label><input class="input" type="text" name="param_%s" value="%s" readonly/><br><br>',$pa['id'],$pa['n'],$pa['id'],$pa['v']);
                else
                    printf('<label for="param_%s">%s</label><input class="input" type="text" name="param_%s" value="%s" /><br><br>',$pa['id'],$pa['n'],$pa['id'],$parval);
            }
            if ($pa['t'] == 1 || $pa['t'] == 3) // integer/integer days
            {
                if ($pa['min'] == 0 && $pa['max'] == 1 && $pa['t'] == 1)
                {
                    if ($pa['v'] && strlen($pa['v']))
                        printf('<label class="checkbox" for="param_%s">%s</label><br><input type="checkbox" name="param_%s" %s readonly/><br><br>',$pa['id'],$pa['n'],$pa['id'],$pa['v'] == 1 ? 'checked' : '');
                    else
                        printf('<label class="checkbox" for="param_%s">%s</label><br><input type="checkbox" name="param_%s" %s /><br><br>',$pa['id'],$pa['n'],$pa['id'],$parval == 1 ? 'checked' : '');

                }
                else
                if ($pa['list'] && strlen($pa['list']))
                {
                    printf('<label for="param_%s">%s</label><br><select class="input" name="param_%s">',$pa['id'],$pa['n'],$pa['id']);
                    $vv = explode(",",$pa['list']);
                    $mi = (int)$pa['min'];
                    foreach($vv as $v)
                    {
                        $s = '';
                        $si = (int)$parval;
                        if ($si == $mi)
                            $s = 'selected';
                        printf('<option value="%s" %s>%s</option>',$mi,$s,$v);
                        $mi++;
                    }
                    printf('</select><br><br>');

                }
                else
                {
                    if ($pa['v'] && strlen($pa['v']))
                        printf('<label for="param_%s">%s</label><input class="input" type="number" step="1" min="%s" max="%s" name="param_%s" value="%s"  readonly/><br><br>',$pa['id'],$pa['n'],$pa['min'],$pa['max'],$pa['id'],$pa['v']);
                    else
                        printf('<label for="param_%s">%s</label><input class="input" type="number" step="1" min="%s" max="%s" name="param_%s" value="%s"  /><br><br>',$pa['id'],$pa['n'],$pa['min'],$pa['max'],$pa['id'],$parval);
                }
            }
            if ($pa['t'] == 2) // float
            {
                if ($pa['v'] && strlen($pa['v']))
                    printf('<label for="param_%s">%s</label><input class="input" type="number" step="0.01" min="%s" max="%s" name="param_%s" value="%s"  readonly/><br><br>',$pa['id'],$pa['n'],$pa['min'],$pa['max'],$pa['id'],$oa['v']);
                else
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
    DeleteProson($_GET['delete'],$uid);
    redirect("proson.php");
    die;
}

printf('<button href="index.php" class="autobutton button is-danger">Πίσω</button> <button class="button is-primary autobutton" href="proson.php?e=0">Νέο Προσόν</button><br><br>Λίστα Προσόντων<hr>');
echo PrintProsonta($uid);
?>
