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

$cid = 0;
$placeid = 0;
$posid = 0;
$forthesi = '';

$cidrow = null;
$placerow = null;
$posrow = null;


if (array_key_exists("cid",$req)) { $cid = $req['cid']; $cidrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($cid))->fetchArray(); }
if (array_key_exists("placeid",$req)) { $placeid = $req['placeid']; $placerow = QQ("SELECT * FROM PLACES WHERE ID = ?",array($placeid))->fetchArray(); }
if (array_key_exists("posid",$req)) { $posid = $req['posid']; $posrow = QQ("SELECT * FROM POSITIONS WHERE ID = ?",array($posid))->fetchArray(); }
if (array_key_exists("forthesi",$req)) $forthesi = $req['forthesi'];

if ($cid == 0)
    die; // atm

$rolerow = QQ("SELECT * FROM ROLES WHERE UID = ? AND ROLE = ?",array($ur['ID'],ROLE_CREATOR))->fetchArray();
if (!$rolerow)
{
    redirect("index.php");
    die;
}

if (array_key_exists("e",$_POST))
{
    if ($req['e'] == 0)
        QQ("INSERT INTO REQS2 (CID,PLACEID,POSID,FORTHESI,PROSONTYPE,SCORE) VALUES(?,?,?,?,?,?)",array(
            $cid,$placeid,$posid,$forthesi,$req['PROSONTYPE'],$req['SCORE']
        ));
    $a = sprintf("prosonta3.php?t=%s&cid=%s&placeid=%s&posid=%s&forthesi=%s",$req['t'],$cid,$placeid,$posid,$forthesi);
    redirect($a);
    die;
}


if (array_key_exists("delete",$req))
{
    QQ("DELETE FROM REQS2 WHERE ID = ?",array($req['delete']));
    $a = sprintf("prosonta3.php?t=%s&cid=%s&placeid=%s&posid=%s&forthesi=%s",$req['t'],$cid,$placeid,$posid,$forthesi);
    redirect($a);
    die;
}

if (array_key_exists("oredit",$req) || array_key_exists("notedit",$req) )
{
    $which = 0;
    if (array_key_exists("oredit",$req) )
    {
        $which = 0;
        $what = $req['oredit'];
    }
    else{
        $which = 1;
        $what = $req['notedit'];
    }
    $reqrow = QQ("SELECT * FROM REQS2 WHERE ID = ?",array($what))->fetchArray();
    $to = (int)$reqrow['ORLINK'];
    if ($which == 1)
        $to = (int)$reqrow['NOTLINK'];
?>
        <form method="POST" action="prosonta3.php">
        <input type="hidden" name="ornotapply" value="<?= $what ?>" />
        <input type="hidden" name="ornot" value="<?= $which ?>" />
            <input type="hidden" name="t" value="<?= $req['t'] ?>" />
            <input type="hidden" name="cid" value="<?= $req['cid'] ?>" />
            <input type="hidden" name="placeid" value="<?= $req['placeid'] ?>" />
            <input type="hidden" name="posid" value="<?= $req['posid'] ?>" />
            <input type="hidden" name="forthesi" value="<?= $req['forthesi'] ?>" />
            <br><br>ID:<br>
    <input type="number" class="input" name="to" value="<?= $to ?>" />
        <br><br>
        <button class="button is-success">Υποβολή</button>
        </form>
        <?php
    die;
}

if (array_key_exists("regexedit",$req))
{
    $what = $req['regexedit'];
    $reqrow = QQ("SELECT * FROM REQS2 WHERE ID = ?",array($what))->fetchArray();
    $pros = $reqrow['PROSONTYPE'];
    EnsureProsonLoaded();

    $pars = array();
    $proot = RootForClassId($xmlp->classes,$pros,$pars);
    $attrs = $proot->attributes();
    printf("Προσόν %s - %s<br>",$pros,$attrs['t']);
    if (!$proot->params)
        die("Δεν υπάρχουν ρυθμίσεις για αυτό το προσόν.");

        ?>
        <form method="POST" action="prosonta3.php">
            <input type="hidden" name="rexapply" value="<?= $req['regexedit'] ?>" />
            <input type="hidden" name="t" value="<?= $req['t'] ?>" />
            <input type="hidden" name="cid" value="<?= $req['cid'] ?>" />
            <input type="hidden" name="placeid" value="<?= $req['placeid'] ?>" />
            <input type="hidden" name="posid" value="<?= $req['posid'] ?>" />
            <input type="hidden" name="forthesi" value="<?= $req['forthesi'] ?>" />
        
        <table class="table datatable">
        <thead>
            <th>#</th>
            <th>Όνομα</th>
            <th>Regex/Expression</th>
        </thead>
        <tbody>
        <?php
        foreach($proot->params->p as $p)
        {
            $attrs2 = $p->attributes();
            $val = '';

            $alls = array();
            if ($reqrow['REGEXRESTRICTIONS'] && strlen($reqrow['REGEXRESTRICTIONS']))
                $alls = explode("|||",$reqrow['REGEXRESTRICTIONS']);
            foreach($alls as $all)
            {
                $it1 = explode("||",$all);
                if ($attrs2['id'] == $it1[0])
                {
                    $val = $it1[1];
                }
            }

            printf('<tr>');
            printf('<td>%s</td>',$attrs2['id']);
            printf('<td>%s</td>',$attrs2['n']);
            printf('<td>
                <input type="text" class="input" name="reg%s" id="reg%s" value="%s"/>
            </td>',$attrs2['id'],$attrs2['id'],$val);
            printf('</tr>');
        }
        
        ?>
        </tbody>
        </table>
        <br><br>
        <button class="button is-success">Υποβολή</button>
        </form>
        <?php
            
    die;
}

if (array_key_exists("rexapply",$_POST))
{
    $rest = '';
    foreach($_POST as $k=>$v)
    {
        if (substr($k,0,3) == "reg")
        {
            $id = substr($k,3);
            if (!strlen($v))
                continue;
            if (strlen($rest))
                $rest .= '|||';
            $rest .= $id;
            $rest .= '||';
            $rest .= $v;
        }
    }
    QQ("UPDATE REQS2 SET REGEXRESTRICTIONS = ? WHERE ID = ?",array($rest,$req['rexapply']));
    $a = sprintf("prosonta3.php?t=%s&cid=%s&placeid=%s&posid=%s&forthesi=%s",$req['t'],$cid,$placeid,$posid,$forthesi);
    redirect($a);
    die;
}

if (array_key_exists("ornotapply",$_POST))
{
    if ($req['ornot'] == 0)
        QQ("UPDATE REQS2 SET ORLINK = ? WHERE ID = ?",array($req['to'],$req['ornotapply']));
    if ($req['ornot'] == 1)
        QQ("UPDATE REQS2 SET NOTLINK = ? WHERE ID = ?",array($req['to'],$req['ornotapply']));
    $a = sprintf("prosonta3.php?t=%s&cid=%s&placeid=%s&posid=%s&forthesi=%s",$req['t'],$cid,$placeid,$posid,$forthesi);
    redirect($a);
    die;
}


printf('<button href="contest.php?t=%s" class="autobutton button  is-danger">Πίσω</button> ',$req['t']);
$q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = ? AND  POSID = ? AND (FORTHESI IS NULL OR FORTHESI = '')",array($cid,$placeid,$posid));
if ($forthesi != '')
    $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = ? AND  POSID = ? AND FORTHESI = ?",array($cid,$placeid,$posid,$forthesi));
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
EnsureProsonLoaded();
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


    printf('<td>');
    $RexCount = 0;
    if ($r1['REGEXRESTRICTIONS'] && strlen($r1['REGEXRESTRICTIONS']))   
        $RexCount = count(explode("|||",$r1['REGEXRESTRICTIONS']));

    printf('<button class="autobutton is-small is-link button" href="prosonta3.php?t=%s&cid=%s&placeid=%s&posid=%s&forthesi=%s&regexedit=%s">Regex %s</button> ',$rolerow['ID'],$cid,$placeid,$posid,$forthesi,$r1['ID'],$RexCount);
    printf('<button class="autobutton is-small is-link button" href="prosonta3.php?t=%s&cid=%s&placeid=%s&posid=%s&forthesi=%s&oredit=%s">OR %s</button> ',$rolerow['ID'],$cid,$placeid,$posid,$forthesi,$r1['ID'],(int)$r1['ORLINK']);
    printf('<button class="autobutton is-small is-link button" href="prosonta3.php?t=%s&cid=%s&placeid=%s&posid=%s&forthesi=%s&notedit=%s">NOT %s</button> ',$rolerow['ID'],$cid,$placeid,$posid,$forthesi,$r1['ID'],(int)$r1['NOTLINK']);

    printf('</td>');

    printf('<td><button class="sureautobutton is-small is-danger button" href="prosonta3.php?t=%s&cid=%s&placeid=%s&posid=%s&forthesi=%s&delete=%s">Διαγραφή</button></td>',$rolerow['ID'],$cid,$placeid,$posid,$forthesi,$r1['ID']);

    printf('</tr>');
}
?>
</tbody></table>
<?php

//printf('<a class="autobutton is-primary button" href="prosonta3.php?t=%s&cid=%s&placeid=0&posid=%s&forthesi=%s">Προσθήκη</a>',$req['t'],$cid,$placeid,$posid,$forthesi);
EnsureProsonLoaded();
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
            {
                $dis = '';
                if ($c->classes->c)
                    $dis = 'disabled';
                $s .= sprintf('<option value="%s" %s>%s%s</option>',$attr['n'],$dis,deepx($deep), $attr['t']);
            }
        $s .= PrintOptionsProson($c,$deep + 1);
    }
    return $s;
}


?>

<script>
    function toggleadd()
    {
        $("#addpos1").hide();
        $("#addpos").show();
    }
    function toggleadd2()
    {
        $("#addpos1").show();
        $("#addpos").hide();
    }
</script>

<div id="addpos1">
<button class="button is-primary" onclick="toggleadd();">Προσθήκη</button>
</div>

<div id="addpos" style="display:none;">
Προσθήκη Προσόντος<hr>
        <form method="POST" action="prosonta3.php">
            <input type="hidden" name="t" value="<?= $req['t'] ?>"/>
            <input type="hidden" name="e" value="0"/>
            <input type="hidden" name="cid" value="<?= $cid ?>"/>
            <input type="hidden" name="placeid" value="<?= $placeid ?>"/>
            <input type="hidden" name="posid" value="<?= $posid ?>"/>
            <input type="hidden" name="forthesi" value="<?= $forthesi ?>"/>

                        Προσόν:
            <select class="select input" name="PROSONTYPE">
                <?php echo PrintOptionsProson($xmlp,0,$row['PARAMID']); ?>
            </select><br><br>
            Σκορ (0 = Προαπαιτούμενο):
            <input class="input" type="number" name="SCORE" step="0.01" value="<?= $row['SCORE'] ?>"  required /><br><br>

        <button class="button is-success">Υποβολή<button>
    </form>
    <button class="button is-danger" onclick="toggleadd2();">Ακύρωση</button>
</div>



