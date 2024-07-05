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

$rolerow = QQ("SELECT * FROM ROLES WHERE UID = ? AND ROLE = ?",array($ur['ID'],ROLE_GLOBALPROSONEDITOR))->fetchArray();
if (!$rolerow)
{
    redirect("index.php");
    die;
}

if (array_key_exists("x",$_POST))
{
    QQ("DELETE FROM GLOBALXML");
    if (strlen($_POST['x']) > 10)
        QQ("INSERT INTO GLOBALXML (XML) VALUES(?)",array($_POST['x']));
    redirect("index.php");
    die;
}

printf('<button href="index.php" class="autobutton button  is-danger">Πίσω</button> ');

?>
<br><br>
<form method="POST" action="globaleditor.php">
    <textarea class="textarea" rows="40" name="x">
        <?= $xml_proson?>
    </textarea>
    <br><br>
<button class="button is-success">ΥΠΟΒΟΛΗ</button>
</form>

<?php