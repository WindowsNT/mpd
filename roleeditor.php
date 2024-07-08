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

$rolerow = QQ("SELECT * FROM ROLES WHERE UID = ? AND ROLE = ?",array($ur['ID'],ROLE_ROLEEDITOR))->fetchArray();
if (!$rolerow && !$superadmin)
{
    redirect("index.php");
    die;
}

if (array_key_exists("delete",$req))
{
    QQ("DELETE FROM ROLES WHERE ID = ?",array(
        $req['delete']
    ));
    redirect(sprintf("roleeditor.php"));
    die;
}

if (array_key_exists("c",$_POST))
{
    $params = '';
    if ($_POST['ROLE'] == ROLE_ROLEEDITOR && !$superadmin)
        die;

    if ($_POST['ROLE'] == ROLE_CHECKER)
        $params = sprintf('{"afms":[%s]}',$_POST['ROLEPARAMS']);
    if ($_POST['ROLE'] == ROLE_CREATOR)
        $params = sprintf('{"contests":[%s]}',$_POST['ROLEPARAMS']);
    if ($_POST['ROLE'] == ROLE_FOREASSETPLACES)
        $params = sprintf('{"places":[%s]}',$_POST['ROLEPARAMS']);
    if ($_POST['ROLE'] == ROLE_UNI)
        $params = sprintf('{"restriction":"%s"}',base64_encode($_POST['ROLEPARAMS']));
    if ($_POST['c'] > 0)
    {
        QQ("UPDATE ROLES SET UID = ?,ROLE = ?,ROLEPARAMS = ? WHERE ID = ?",array(
            $_POST['UID'],$_POST['ROLE'],$params,$_POST['c']
        ));
        $lastRowID = $_POST['c'];
    }
    else    
    {
        QQ("INSERT INTO ROLES (UID,ROLE,ROLEPARAMS) VALUES (?,?,?) ",array(
            $_POST['UID'],$_POST['ROLE'],$params
        ));
    }

    if ($lastRowID)
    {

    }
    redirect(sprintf("roleeditor.php"));
    die;
}


printf('<button href="index.php" class="autobutton button  is-danger">Πίσω</button> ');


function ViewOrEdit($cid,$t = 0)
{
    $items = QQ("SELECT * FROM ROLES WHERE ID = ?",array($cid))->fetchArray();
    $defp = '';
    if ($t == ROLE_CHECKER) $defp = '{"afms":[]}';
    if ($t == ROLE_CREATOR) $defp = '{"contests":[]}';
    if ($t == ROLE_UNI) $defp = '{"restriction":""}';
    if ($t == ROLE_GLOBALPROSONEDITOR) $defp = '';
    if ($t == ROLE_ROLEEDITOR) $defp = '';
    if ($t == ROLE_FOREASSETPLACES) $defp = '{"places":[]}';
    if (!$items)
        $items = array('ID' => '0','UID' => 0,'ROLE' => $t,'ROLEPARAMS' => $defp);

    $jitems = array();
    if ($items['ROLEPARAMS'])
        $jitems = json_decode($items['ROLEPARAMS']);
    ?>
    <br><br><br>
    <form method="POST" action="roleeditor.php">
    <input type="hidden" name="ROLE" value="<?= $items['ROLE'] ?>" />
    <input type="hidden" name="c" value="<?= $items['ID'] ?>" />

    <?php
        printf("%s<hr>",RoleToText($items['ROLE']));
        ?>
        Όνομα<br>
        <select name="UID" id="UID" class="input">
            <?php
            $q1 = QQ("SELECT * FROM USERS");
            while($r1 = $q1->fetchArray())
            {
                $sel = '';
                if ($items['UID'] == $r1['ID'])
                    $sel = 'selected';
                printf('<option value="%s" %s>%s %s</option>',$r1['ID'],$sel,$r1['LASTNAME'],$r1['FIRSTNAME']);
            }
            ?>
        </select><br><br>
        <?php
        if($items['ROLE'] == ROLE_CHECKER)
        {
            printf('<label for="ROLEPARAMS">Λίστα ΑΦΜ χωρισμένα με κόμμα:</label><input type="text" class="input" id="ROLEPARAMS" name="ROLEPARAMS" value="%s" />',implode(",",$jitems->afms));
        }
        if($items['ROLE'] == ROLE_CREATOR)
        {
            printf('<label for="ROLEPARAMS">Λίστα ID διαγωνισμών χωρισμένα με κόμμα:</label><input type="text" class="input" id="ROLEPARAMS" name="ROLEPARAMS" value="%s" />',implode(",",$jitems->contests));
        }
        if($items['ROLE'] == ROLE_UNI)
        {
            printf('<label for="ROLEPARAMS">XML ελέγχου:</label><textarea class="textarea" id="ROLEPARAMS" name="ROLEPARAMS" rows="20" cols="50">%s</textarea>',base64_decode($jitems->restriction));
        }
        if($items['ROLE'] == ROLE_FOREASSETPLACES)
        {
            printf('<label for="ROLEPARAMS">Λίστα ID φορέων χωρισμένα με κόμμα:</label><input type="text" class="input" id="ROLEPARAMS" name="ROLEPARAMS" value="%s" />',implode(",",$jitems->places));
        }
    ?>  
        <br><br>
        <button class="button is-success">Υποβολή<button>
    </form>
    <?php
}


if (array_key_exists("c",$_GET))
    {
        $id = $_GET['c'];
        ViewOrEdit($id,array_key_exists("t",$req) ? $req['t'] : 0);
        die;
    }

$j = '      <div class="dropdown-item">
            <a href="roleeditor.php?c=0&t=%s">Role Editor</a>
      </div>
';
    if (!$superadmin)
        $j = '';

printf('<div class="dropdown is-hoverable">
  <div class="dropdown-trigger">
    <button class="button is-primary  block" aria-haspopup="true" aria-controls="dropdown-menu4">
      <span>Νέος Ρόλος</span>
    </button>
  </div>
  <div class="dropdown-menu" id="dropdown-menu4" role="menu">
    <div class="dropdown-content">
      <div class="dropdown-item">
            <a href="roleeditor.php?c=0&t=%s">Ελεγκτής Προσόντων</a>
      </div>
      <div class="dropdown-item">
            <a href="roleeditor.php?c=0&t=%s">Δημιουργός Διαγωνισμών</a>
      </div>
      <div class="dropdown-item">
            <a href="roleeditor.php?c=0&t=%s">Ίδρυμα</a>
      </div>
      <div class="dropdown-item">
            <a href="roleeditor.php?c=0&t=%s">Διαχειριστής Γενικών Προσόντων</a>
      </div>
      <div class="dropdown-item">
            <a href="roleeditor.php?c=0&t=%s">Διαχειριστής Κενών Φορέα</a>
      </div>
      %s
    </div>
  </div>
</div> ',ROLE_CHECKER,ROLE_CREATOR,ROLE_UNI,ROLE_GLOBALPROSONEDITOR,ROLE_FOREASSETPLACES,$j);

echo '<table class="table datatable" style="width: 100%">';
echo '<thead>
            <th class="all">#</th>
            <th class="all">UID</th>
            <th class="all">Όνομα</th>
            <th class="all">Ρόλος</th>
            <th class="all">Παράμετροι</th>
            <th class="all">Επιλογές</th>
        </thead><tbody>';
$q2 = QQ("SELECT * FROM ROLES");
while($r2 = $q2->fetchArray())
{
    if ($r2['ROLE'] == ROLE_ROLEEDITOR && !$superadmin)
        continue;
    printf('<tr>');
    printf('<td>%s</td>',$r2['ID']);
    printf('<td>%s</td>',$r2['UID']);
    $urr = QQ("SELECT * FROM USERS WHERE ID = ?",array($r2['UID']))->fetchArray();
    printf('<td>%s %s</td>',$urr['LASTNAME'],$urr['FIRSTNAME']);
    printf('<td>%s %s</td>',$r2['ROLE'],RoleToText($r2['ROLE']));
    printf('<td>%s</td>',substr($r2['ROLEPARAMS'] ? $r2['ROLEPARAMS'] : '',0,50));
    printf('<td>');

    printf('<button class="autobutton button is-small is-primary block" href="roleeditor.php?c=%s">Επεξεργασία</button> ',$r2['ID']);
    if ($superadmin)
        printf('<button class="autobutton button is-small is-warning block" href="impersonate.php?u=%s">Impersonate</button> ',$r2['UID']);
    printf('<button class="sureautobutton button is-small is-danger block" href="roleeditor.php?delete=%s">Διαγραφή</button></td>',$r2['ID']);

    printf('</tr>');
}

echo '</tbody></table>';
