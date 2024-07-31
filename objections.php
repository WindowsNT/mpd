<?php

require_once "function.php";
require_once "auth.php";


if (!$afm || !$ur)
    {
        redirect("index.php");
        die;
    }

$app = Single("APPLICATIONS","ID",$req['aid']);
if (!$app)
    die;

if (!HasAppAccess($app['ID'],$ur['ID'],1))
    die;

if (array_key_exists("aid",$_POST))
{
    if ($app['UID'] == $ur['ID'])
    {
        if ($_POST['oid'] == 0)
            QQ("REPLACE INTO OBJECTIONS (AID,OBJTEXT,DATE) VALUES(?,?,?)",array(
            $_POST['aid'],$_POST['OBJTEXT'],time()
        ));        
        else
        QQ("REPLACE INTO OBJECTIONS (ID,AID,OBJTEXT,DATE) VALUES(?,?,?,?)",array(
            $_POST['oid'],$_POST['aid'],$_POST['OBJTEXT'],time()
        ));    
        
        Push3_Send(sprintf("Η ένσταση έχει υποβληθεί!"),array($ur['CLSID']));
        
        redirect(sprintf("applications.php?results=%s",$app['CID']));
    }
    else
    {

        $wu = Single("USERS","ID",$app['UID']);
        Push3_Send(sprintf("Η ένσταση έχει απαντηθεί!"),array($wu['CLSID']));

        $oo = Single("OBJECTIONS","ID",$_POST['oid']);
        QQ("REPLACE INTO OBJECTIONS (ID,AID,OBJTEXT,OBJANSWER,DATE,RESULT) VALUES(?,?,?,?,?,?)",array(
            $_POST['oid'],$_POST['aid'],$oo['OBJTEXT'],$_POST['OBJANSWER'],$oo['DATE'],2
        ));        
        redirect(sprintf("listapps.php?cid=%s",$app['CID']));

    }
    die;
}

require_once "output.php";
echo '<div class="content" style="margin: 20px">';



$cr = Single("CONTESTS","ID",$app['CID']);
$fr = Single("PLACES","ID",$app['PID']);
$pr = Single("POSITIONS","ID",$app['POS']);

$oid = 0;
$items = null;

// Check history
$can = 1;

$q1 = QQ("SELECT * FROM OBJECTIONS WHERE AID = ? ORDER BY DATE ASC",array($req['aid']));
while($r1 = $q1->fetchArray())
{
    printf('<div class="notification is-primary">
    Ένσταση %s<hr>
    %s
',date("d-m-Y H:i:s",$r1['DATE']),$r1['OBJTEXT']);
    $req['oid'] = $r1['ID'];
    if ($r1['RESULT'] == 0)
    {
        // not answered yet
    }
    if ($r1['RESULT'] == 2)
    {
        $can = 0;
    }
    printf('</div>');
}


if ($ur['ID'] == $app['UID'])
{
    if ($can)
    {
        if (array_key_exists("oid",$req))
        {
            $oid = $req['oid'];
            $items = Single("OBJECTIONS","ID",$oid);
        }
        if (!$items)
        $items = array("ID" => 0,"OBJTEXT" => "","AID" => $req['aid'],"DATE" => time(),"RESULT" => 0);
        ?>

        <form method="POST" action="objections.php">
        <input type="hidden" name="aid" value="<?= $req['aid'] ?>"/>
        <input type="hidden" name="oid" value="<?= $items['ID'] ?>"/>
            <label for="OBJTEXT">Κείμενο Ένστασης</label>
            <textarea required name="OBJTEXT" class="summernote" rows="10"><?= $items['OBJTEXT'] ?></textarea>
            <br><br>

        <button class="button is-success">Υποβολή</button>
        </form>
        <button class="button is-danger autobutton" href="applications.php">Πίσω</button>
        <?php
    }
    else
    {
        $items = Single("OBJECTIONS","ID",$req['oid']);
        printf('<div class="notification is-success">
        Απάντηση<hr>
        %s',$items['OBJANSWER']);
    }
}
else
if ($req['oid'] != 0)   
{
    $items = Single("OBJECTIONS","ID",$req['oid']);

    // Answering
    ?>
    <form method="POST" action="objections.php">
    <input type="hidden" name="aid" value="<?= $req['aid'] ?>"/>
    <input type="hidden" name="oid" value="<?= $items['ID'] ?>"/>
        <label for="OBJANSWER">Κείμενο Απάντησης</label>
        <textarea required name="OBJANSWER" class="summernote" rows="10"><?= $items['OBJANSWER'] ?></textarea>
        <br><br>

    <button class="button is-success">Υποβολή</button>
    </form>
    <button class="button is-danger autobutton" href="applications.php">Πίσω</button>
    <?php

}

//echo 'Οι ενστάσεις είναι ανενεργές.';