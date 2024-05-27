<?php

require_once "function.php";
require_once "auth.php";


if (!$afm || !$ur)
    {
        redirect("index.php");
        die;
    }


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

    $g = guidv4();
    file_put_contents("files/$g",$vev);

    QQ("INSERT INTO PROSONFILE (PID,UID,CLSID,DESCRIPTION,FNAME,TYPE) VALUES(?,?,?,?,?,?)",
        array($_POST['e'],$ur['ID'],$g,$_POST['f0'],$fn,$extension));
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
    DeleteProsonFile($req['delete'],$ur['ID']);
    redirect(sprintf("files.php?e=%s&f=0",$_GET['e']));
    die;
}

function PrintFiles($pid)
{
    global $ur;

    $s = '<table class="table datatable">';
    $s .= '<thead>
                <th>#</th>
                <th>Αρχείο</th>
                <th>Εντολές</th>
            </thead><tbody>';

            
    $q1 = QQ("SELECT * FROM PROSONFILE WHERE PID = ? ",array($pid));
    while($r1 = $q1->fetchArray())
    {
        $s .= sprintf('<tr>');
        $s .= sprintf('<td>%s</td>',$r1['ID']);
        $s .= sprintf('<td><b><a href="viewfile.php?f=%s" target="_blank">%s</a></td>',$r1['ID'],$r1['FNAME']);
        $s .= sprintf('<td>');
        $s .= sprintf('<button class="sureautobutton button is-small is-danger" q="Να διαγραφεί το συγκεκριμένο αρχείο;" href="files.php?e=%s&delete=%s&f=0">Διαγραφή</button>',$pid,$r1['ID']);
        $s .= sprintf('</td>');
        $s .= sprintf('</tr>');
    }

    $s .= '</tbody></table>';
    return $s;
}

require_once "output.php";
echo '<div class="content" style="margin: 20px">';
if ($_GET['f'] == 0)
{
    printf('<button href="proson.php" class="autobutton button is-danger">Πίσω</button><hr>');
    echo 'Αρχεία<hr>';
    echo PrintFiles($_GET['e']);
    echo '<hr>Ανέβασμα νέου αρχείου<hr>';    
    ?>
        <form method="POST" action="files.php" enctype="multipart/form-data">
            <input type="hidden" name="e" value="<?= $_GET['e'] ?>">
            <label for="f0">Περιγραφή</label>
        <input type="text" name="f0" id="f0" required class="input"/><br><br>
        <label for="f1">Ανέβασμα</label>
        <input type="file" name="f1" id="f1" accept=".png,.jpg,.pdf,.jpeg;capture=camera" required class="input"/><br>
        <br><br>
        <button class="button is-success">Yποβολή</button>
        </form>
    <?php

}
