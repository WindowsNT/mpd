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

$contestrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($req['cid']))->fetchArray();
if (!$contestrow)
{
    redirect("index.php");
    die;
}

$placerow = QQ("SELECT * FROM PLACES WHERE ID = ?",array($req['pid']))->fetchArray();
if (!$placerow)
{
    redirect("index.php");
    die;
}

$posrow = QQ("SELECT * FROM POSITIONS WHERE ID = ?",array($req['pos']))->fetchArray();
if (!$posrow)
{
    redirect("index.php");
    die;
}

if (array_key_exists("PROSONTYPE",$_POST))
{
    $id = $_POST['e'];
    if ($id == 0)
    {
        QQ("INSERT INTO REQUIREMENTS (CID,POSID,PROSONTYPE,SCORE) VALUES (?,?,?,?)",array(
            $_POST['cid'],$_POST['pos'],$_POST['PROSONTYPE'],$_POST['SCORE']
        ));
        $id = $lastRowID;
    }
    else
    {
        QQ("UPDATE  REQUIREMENTS SET CID = ?,POSID = ?,PROSONTYPE = ?,SCORE = ? WHERE ID = ?",array(
            $_POST['cid'],$_POST['pos'],$_POST['PROSONTYPE'],$_POST['SCORE'],$id
        ));

    }
   
    redirect(sprintf("prosonta.php?t=%s&cid=%s&pid=%s&pos=%s",$_POST['t'],$_POST['cid'],$_POST['pid'],$_POST['pos']));
    die;
}

function PrintOptionsProson($x,$deep = 0,$sel = 0)
{
    $s = '';
    if (!$x)
        return;
    foreach($x->classes->c as $c)
    {
        $attr = $c->attributes();
        if ($sel == $attr['n'])
            $s .= sprintf('<option value="%s" selected>%s%s</option>',$attr['n'],deepx($deep), $attr['t']);
        else
            $s .= sprintf('<option value="%s">%s%s</option>',$attr['n'],deepx($deep), $attr['t']);
        $s .= PrintOptionsProson($c,$deep + 1);
    }
    return $s;
}

function EditProsontaThesis($prosonid)
{   
    global $xmlp;
    EnsureProsonLoaded();
    
    global $contestrow,$posrow,$rolerow,$placerow;
    $row = QQ("SELECT * FROM REQUIREMENTS WHERE ID = ?",array($prosonid))->fetchArray();
    if (!$row)
        $row = array("ID" => 0,"CID" => $contestrow['ID'],"POSID" => $posrow['ID'],"PROSONTYPE" => 0,"PARAMID" => 0,"PARAMREGEX" => "","SCORE" => 0);

        ?>
        <form method="POST" action="prosonta.php">
            <input type="hidden" name="e" value="<?= $row['ID'] ?>" />
            <input type="hidden" name="t" value="<?= $rolerow['ID'] ?>" />
            <input type="hidden" name="cid" value="<?= $row['CID'] ?>" />
            <input type="hidden" name="pid" value="<?= $placerow['ID'] ?>" />
            <input type="hidden" name="pos" value="<?= $row['POSID'] ?>" />
            Προσόν:
            <select class="select input" name="PROSONTYPE">
                <?php echo PrintOptionsProson($xmlp,0,$row['PARAMID']); ?>
            </select><br><br>
            Σκορ (0 = Προαπαιτούμενο):
            <input class="input" type="number" name="SCORE" step="0.01" value="<?= $row['SCORE'] ?>"  /><br><br>


        <button class="button is-small is-success">Υποβολή</button>
        </form>

        <?php

}

function ViewProsontaThesis()
{
    global $contestrow,$posrow,$rolerow,$placerow,$xml_proson,$xmlp;
    EnsureProsonLoaded();
    $q1 = QQ("SELECT * FROM REQUIREMENTS WHERE CID = ? AND POSID = ?",array($contestrow['ID'],$posrow['ID']));
    ?>
    <table class="table datatable">
    <thead>
        <th>#</th>
        <th>Προσόν</th>
        <th>Σκορ</th>
        <th>Παράμετροι</th>
        <th>Εντολές</th>
    </thead>
    <tbody>
    <?php
    while($r1 = $q1->fetchArray())
    {
        printf('<tr>');
        printf('<td>%s</td>',$r1['ID']);

        $pars = array();
        $croot = RootForClassId($xmlp->classes,$r1['PROSONTYPE'],$pars);
        if (!$croot)
            continue;
        $attr = $croot->attributes();
        $s = '';
        foreach($pars as $par)
        {
            $attrp = $par->attributes();
            $s .= sprintf('%s<br>',$attrp['t']);
        }
        $s .= $attr['t'].'<br>';
        printf('<td>%s</td>',$s);
        printf('<td>%s</td>',$r1['SCORE']);

        printf('<td><button class="autobutton is-small is-link button" href="regex.php?t=%s&cid=%s&pid=%s&pos=%s&prid=%s">Regex</button></td>',$rolerow['ID'],$contestrow['ID'],$placerow['ID'],$posrow['ID'],$r1['ID']);
        printf('<td><button class="autobutton is-small is-danger button" href="prosonta.php?t=%s&cid=%s&pid=%s&pos=%s&delete=%s">Διαγραφή</button></td>',$rolerow['ID'],$contestrow['ID'],$placerow['ID'],$posrow['ID'],$r1['ID']);

        printf('</tr>');
    }
?>
</tbody>
</table>
<?php
}

printf('<button href="contest.php?t=%s" class="autobutton button  is-danger">Πίσω</button><hr> ',$req['t']);
printf('Θέσεις σε φορέα: %s<br>',$placerow['DESCRIPTION']);
printf('Θέση: %s<hr>',$posrow['DESCRIPTION']);

if (array_key_exists("e",$req))
{
    EditProsontaThesis($req['e']);
}
else
    {
        if (array_key_exists("delete",$req))
            QQ("DELETE FROM REQUIREMENTS WHERE ID = ?",array($req['delete']));

        ViewProsontaThesis();
        printf('<button class="autobutton is-primary is-small button" href="prosonta.php?t=%s&cid=%s&pid=%s&pos=%s&e=0">Προσθήκη</a>',$rolerow['ID'],$contestrow['ID'],$placerow['ID'],$posrow['ID']);
    }

