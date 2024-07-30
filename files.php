<?php

require_once "function.php";
require_once "auth.php";


if (!$afm || !$ur)
    {
        redirect("index.php");
        die;
    }

$uid = $ur['ID'];
if (array_key_exists("force_user",$req))
    $uid = QQ("SELECT * FROM USERS WHERE CLSID = ?",array($req['force_user']))->fetchArray()['ID'];


if (array_key_exists("f1",$_FILES))
{
    $file = $_FILES['f1'];
	$fn = $file['name'];
	$extension = pathinfo($fn, PATHINFO_EXTENSION);
	if ($fn == "")
    {
        redirect("proson.php") ;die;
    }
	$tempfile = $file['tmp_name'];
	$vev = file_get_contents($tempfile);

    if (filesize($tempfile) > 10*1024*1024)
    {
        die("Το αρχείο είναι μεγαλύτερο από 10MB.");
    }

    // check jpg
    $finfo = new finfo(FILEINFO_MIME);
    $ct = $finfo->buffer($vev);
    $g = guidv4();
    if (strstr($ct,"image/jpeg"))
    {
        $vev = jpegrecompress($vev);
    }
    file_put_contents("files/$g",$vev);

    QQ("INSERT INTO PROSONFILE (PID,UID,CLSID,DESCRIPTION,FNAME,TYPE) VALUES(?,?,?,?,?,?)",
        array($_POST['e'],$uid,$g,$_POST['f0'],$fn,$extension));

    if ($lastRowID != 0)
    {
        $whatstate = 0;
        // Auto Accept ?
        $pr = Single("PROSON","ID",$_POST['e']);
        if ($pr)
        {
            EnsureProsonLoaded();
            $xx = $xmlp;
            $croot = RootForClassId($xx->classes,$pr['CLASSID']);
            if ($croot)
            {
                $autoaccept = $croot->attributes()['autoaccept'];
                if ($autoaccept)
                 $whatstate =            $required_check_level;
            }        

        }
        QQ("UPDATE PROSON SET STATE = ? WHERE ID = ?",array($whatstate,$_POST['e']));
    }
    if (array_key_exists("force_user",$req))
        redirect("provider.php");
    else
        redirect("proson.php");
    die;
}

if (!array_key_exists("f",$_GET))
    {
        redirect("proson.php");
        die;    
    }

if (array_key_exists("delete",$_GET))
{
    DeleteProsonFile($req['delete'],$uid);
    if (array_key_exists("force_user",$req))
        redirect(sprintf("files.php?e=%s&f=0&force_user=%s",$_GET['e'],$req['force_user']));
    else
        redirect(sprintf("files.php?e=%s&f=0",$_GET['e']));
    die;
}

$file_count = 0;
function PrintFiles($pid)
{
    global $req,$file_count;

    $s = '<table class="table datatable" style="width: 100%">';
    $s .= '<thead>
                <th class="all">#</th>
                <th class="all">Αρχείο</th>
                <th class="all">Περιγραφή</th>
                <th class="all">Εντολές</th>
            </thead><tbody>';


    $file_count = 0;
    $q1 = QQ("SELECT * FROM PROSONFILE WHERE PID = ? ",array($pid));
    while($r1 = $q1->fetchArray())
    {
        $file_count++;
        $s .= sprintf('<tr>');
        $s .= sprintf('<td>%s</td>',$r1['ID']);
        if (array_key_exists("force_user",$req))
            $s .= sprintf('<td><b><a href="viewfile.php?f=%s&force_user=%s" target="_blank">%s</a></td>',$r1['ID'],$req['force_user'],$r1['FNAME']);
        else
            $s .= sprintf('<td><b><a href="viewfile.php?f=%s" target="_blank">%s</a></td>',$r1['ID'],$r1['FNAME']);
        $s .= sprintf('<td>%s</td>',$r1['DESCRIPTION']);
        $s .= sprintf('<td>');

        if (array_key_exists("force_user",$req))
            $s .= sprintf('<button class="sureautobutton button is-small is-danger" q="Να διαγραφεί το συγκεκριμένο αρχείο;" href="files.php?e=%s&delete=%s&f=0&force_user=%s">Διαγραφή</button>',$pid,$r1['ID'],$req['force_user']);
        else
            $s .= sprintf('<button class="sureautobutton button is-small is-danger" q="Να διαγραφεί το συγκεκριμένο αρχείο;" href="files.php?e=%s&delete=%s&f=0">Διαγραφή</button>',$pid,$r1['ID']);
        $s .= sprintf('</td>');
        $s .= sprintf('</tr>');
    }

    $s .= '</tbody></table>';
    return $s;
}

require_once "output.php";
echo '<div class="content" style="margin: 20px">';
$filelist = PrintFiles($_GET['e']);
if ($_GET['f'] == 0)
{
    $pr = Single("PROSON","ID",$_GET['e']);
    printf('<button href="proson.php" class="autobutton button is-danger">Πίσω</button><hr>');
    if ($file_count != 0)
    echo '<div class="columns">
  <div class="column is-half">';
    printf('Προσόν: <b>%s</b><br>Ανέβασμα νέου αρχείου<hr>',$pr['DESCRIPTION']);    
    ?>
        <form method="POST" action="files.php" enctype="multipart/form-data">
        <?php
            if (array_key_exists("force_user",$req))
                printf('<input type="hidden" name="force_user" value="%s" />',$req['force_user']);
            ?>

            <input type="hidden" name="e" value="<?= $_GET['e'] ?>">
            <label for="f0">Περιγραφή του αρχείου</label>
        <input type="text" name="f0" id="f0" required class="input"/><br><br>

<!--        <input type="file" name="f1" id="f1" accept=".png,.jpg,.pdf,.jpeg;capture=camera" required class="input"/><br>-->
<div class="file has-name is-boxed">
    <label class="file-label">
        <input class="file-input" type="file" name="f1" id="f1" accept=".docx,.zip,.png,.jpg,.pdf,.jpeg;capture=camera" required >
        <span class="file-cta">
            <span class="file-icon">
                <i class="fa fa-upload"></i>
            </span>
            <span class="file-label">
                Επιλογή αρχείου<br>
                ZIP/JPG/PDF/PNG/DOCX <= 10MB
            </span>
        </span>
        <span class="file-name">Κάντε κλικ για να επιλέξετε...</span>
    </label>
</div>

        <br><br>
        <button class="button is-success">Yποβολή</button>
        </form>
    <?php

    if ($file_count != 0)
    {
        echo '</div><div class="column  is-half">';
        echo 'Υπάρχοντα Αρχεία<hr>';
        echo $filelist;
        echo '</div>';
        echo '</div>';
    }
}
