<?php

require_once "function.php";
require_once "auth.php";


if (!$afm || !$ur)
    {
        redirect("index.php");
        die;
    }

if (array_key_exists("aid",$_POST))
{
    if ($_POST['oid'] == 0)
        QQ("INSERT INTO OBJECTIONS (AID,OBJTEXT,DATE) VALUES(?,?,?)",array(
        $_POST['aid'],$_POST['OBJTEXT'],time()
    ));
    else
        QQ("UPDATE OBJECTIONS SET AID = ?,OBJTEXT = ?,DATE = ? WHERE ID = ?)",array(
            $_POST['aid'],$_POST['OBJTEXT'],time(),$req['oid']
        ));
        
    redirect("applications.php");
    die;
}

require_once "output.php";
echo '<div class="content" style="margin: 20px">';


$app = Single("APPLICATIONS","ID",$req['aid']);
if (!$app)
    die;

$cr = Single("CONTESTS","ID",$app['CID']);
$fr = Single("PLACES","ID",$app['PID']);
$pr = Single("POSITIONS","ID",$app['POS']);

$oid = 0;
$items = null;
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
        <label for="OBJTEXT">Κείμενο Ένστασης</label>
        <textarea required name="OBJTEXT" class="summernote" rows="10"><?= $items['OBJTEXT'] ?></textarea>
        <br><br>

    <button class="button is-success">Υποβολή</button>
</form>

<?php

//echo 'Οι ενστάσεις είναι ανενεργές.';