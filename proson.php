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
    if ($_POST['e'] > 0 && array_key_exists("proson_fcid_run_uid",$_SESSION) && !HasProsonAccess($_POST['e'],$uid,1))
        die;
    $whoimp = $uid;
    if (array_key_exists("proson_fcid_run_uid",$_SESSION))
        {
            $whoimp = $_SESSION['proson_fcid_run_uid'];
            unset($_SESSION['proson_fcid_run_uid']);
        }

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
            
            $others = QQ("SELECT * FROM PROSON WHERE UID = ? AND CLASSID = ?",array($whoimp,$_POST['CLASSID']));
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
            $others = QQ("SELECT * FROM PROSON WHERE UID = ? AND CLASSID = ?",array($whoimp,$_POST['CLASSID']));
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
        {
            if ($whoimp != $uid)
                redirect("index.php");
            else
                redirect("proson.php");
        }
    die;
}

echo '<div class="content" style="margin: 20px">';


function ViewOrEdit($pid,$items,$fcid = 0)
{
    global $xmlp,$_SESSION,$uid,$req,$music_schools,$music_eidik;
    EnsureProsonLoaded();
    $xx = $xmlp;
    if (array_key_exists("constraint",$_SESSION))
        $xx = simplexml_load_string($_SESSION['constraint']);

    if ($pid)
        $items = QQ("SELECT * FROM PROSON WHERE ID = ? AND UID = ?",array($pid,$uid))->fetchArray();
    if (!$items)
        $items = array('ID' => '0','UID' => $uid,'CLSID' => guidv4(),'DESCRIPTION' => '','CLASSID' => 0,'STARTDATE' => '0','ENDDATE' => '0',"DIORISMOS" => 0);

    if ($items['CLASSID'] == 0 && array_key_exists("CLASSID",$_GET))
        $items['CLASSID'] = $_GET['CLASSID'];

    if ($fcid)
        $items['CLASSID'] = $fcid;

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
            <select class="input" name="CLASSID" required>
                <option value disabled selected>Επιλέξτε...</option>');
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
    
    if (array_key_exists("proson_fcid",$_SESSION))
    {
        if (HasProsonAccess($_SESSION['proson_fcid'],$uid,1))
        {
            $pid = $_SESSION['proson_fcid'];
            $items = QQ("SELECT * FROM PROSON WHERE ID = ?",array($pid))->fetchArray();
            $items['CLASSID'] = $req['CLASSID'];
            $_SESSION['proson_fcid_run_uid'] = $items['UID'];
        }
        unset($_SESSION['proson_fcid']);
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

        <label for="STARTDATE">Ημερομηνία Έναρξης Ισχύος Δικαιολογητικού</label>
        <input required type="date" name="STARTDATE" class="input" value="<?= $items['STARTDATE'] > 0 ? date("Y-m-d",$items['STARTDATE']) : "" ?>"/>
        <br><br>

        <?php
            $canend = $croot->attributes()['canend'];
            if ($canend == 0)
            {
                ?>
                <input type="hidden" name="ENDDATE" class="input" />
                <?php
            }
            else
            {
                ?>
                <label for="STARTDATE">Ημερομηνία Λήξης Τίτλου </label>
                <input type="date" name="ENDDATE" class="input" value="<?= $items['ENDDATE'] > 0 ? date("Y-m-d",$items['ENDDATE']) : "" ?>"/>
                <br><br>
                <?php
        
            }
        ?>
        <label for="STARTDATE">Είναι το δικαιολογητικό προσόν διορισμού;</label>
        <select name="DIORISMOS" class="input">
            <option value="0">Όχι</option>
            <option value="1" <?= (int)$items['DIORISMOS'] ? "selected" : "" ?>>Ναι</option>
        </select>
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
            if ($pa['t'] == 1 || $pa['t'] == 3 || $pa['t'] == 4) // integer/integer days/select values
            {
/*                if ($pa['min'] == 0 && $pa['max'] == 1 && $pa['t'] == 1)
                {
                    if ($pa['v'] && strlen($pa['v']))
                        printf('<label class="checkbox" for="param_%s">%s</label><br><input type="checkbox" name="param_%s" %s readonly/><br><br>',$pa['id'],$pa['n'],$pa['id'],$pa['v'] == 1 ? 'checked' : '');
                    else
                        printf('<label class="checkbox" for="param_%s">%s</label><br><input type="checkbox" name="param_%s" %s /><br><br>',$pa['id'],$pa['n'],$pa['id'],$parval == 1 ? 'checked' : '');

                }
                else*/
                if ($pa['list'] && strlen($pa['list']))
                {
                    printf('<label for="param_%s">%s</label><br><select class="input" name="param_%s">',$pa['id'],$pa['n'],$pa['id']);
                    if ($pa['list'] == '--TMS--')
                        {
                            $vv = explode(",",$music_eidik);
                        }
                    else
                    if ($pa['list'] == '--MS--')
                        {
                            $vv = explode(",",$music_schools);
                        }
                    else
                        $vv = explode(",",$pa['list']);
                    $mi = (int)$pa['min'];
                    foreach($vv as $v)
                    {
                        $s = '';
                        if ($pa['t'] == 4)
                        {
                            $si = $parval;
                            if ($si == $v)
                                $s = 'selected';
                            printf('<option value="%s" %s>%s</option>',$v,$s,$v);
                        }
                        else
                        {
                            $si = (int)$parval;
                            if ($si == $mi)
                                $s = 'selected';
                            printf('<option value="%s" %s>%s</option>',$mi,$s,$v);
                        }
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
        <div class="notification is-info">
            Μόλις πατήσετε υποβολή για νέο προσόν, θα μεταβείτε στην οθόνη ανεβάσματος δικαιολογητικών.
</div>
<br><br>
        <button class="button is-success ">Υποβολή<button>
    </form>
    <?php
}

$fcid = 0;
if (array_key_exists("fcid",$_GET))
    $fcid = $_GET['fcid'];


if (array_key_exists("e",$_GET))
{
    ViewOrEdit($_GET['e'],null,$fcid);
    echo '<button class="button is-danger autobutton" href="proson.php">Πίσω</button>';
    die;
}


if (array_key_exists("delete",$_GET))
{
    DeleteProson($_GET['delete'],$uid);
    redirect("proson.php");
    die;
}

PrintButtons(array(array("n" => "Πίσω","h" => "index.php","s" => "is-danger"),array("n" => "Νέο Προσόν","h" => "proson.php?e=0","s" => "is-primary")));
echo PrintProsonta($uid);
?>
