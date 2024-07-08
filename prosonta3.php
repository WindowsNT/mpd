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

if (!HasContestAccess($req['cid'],$ur['ID'],1))
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
        else
        QQ("UPDATE REQS2 SET SCORE = ?,PROSONTYPE = ? WHERE ID = ?",array(
            $req['SCORE'],$req['PROSONTYPE'],$req['e']
        ));
    $a = sprintf("prosonta3.php?cid=%s&placeid=%s&posid=%s&forthesi=%s",$cid,$placeid,$posid,$forthesi);
    redirect($a);
    die;
}


if (array_key_exists("delete",$req))
{
    QQ("DELETE FROM REQS2 WHERE ID = ?",array($req['delete']));
    $a = sprintf("prosonta3.php?cid=%s&placeid=%s&posid=%s&forthesi=%s",$cid,$placeid,$posid,$forthesi);
    redirect($a);
    die;
}


if (array_key_exists("setmin",$req))
{
    QQ("UPDATE REQS2 SET MINX = ? WHERE ID = ?",array($req['value'],$req['setmin']));
    $si = Single("REQS2","ID",$req['setmin']);
    if ($si && (int)$si['MAXX'] < (int)$si['MINX'])
        QQ("UPDATE REQS2 SET MAXX = ? WHERE ID = ?",array($req['value'],$req['setmin']));
    $a = sprintf("prosonta3.php?cid=%s&placeid=%s&posid=%s&forthesi=%s",$cid,$placeid,$posid,$forthesi);
    redirect($a);
    die;
}

if (array_key_exists("setmax",$req))
{
    QQ("UPDATE REQS2 SET MAXX = ? WHERE ID = ?",array($req['value'],$req['setmax']));
    $si = Single("REQS2","ID",$req['setmax']);
    if ($si && (int)$si['MINX'] > (int)$si['MAXX'])
        QQ("UPDATE REQS2 SET MINX = ? WHERE ID = ?",array($req['value'],$req['setmax']));
    $a = sprintf("prosonta3.php?cid=%s&placeid=%s&posid=%s&forthesi=%s",$cid,$placeid,$posid,$forthesi);
    redirect($a);
    die;
}

if (array_key_exists("oredit",$req) || array_key_exists("notedit",$req) || array_key_exists("andedit",$req) )
{
    $which = 0;
    if (array_key_exists("andedit",$req) )
    {
        $which = 2;
        $what = $req['andedit'];
    }
    else
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
    if ($which == 2)
        $to = (int)$reqrow['ANDLINK'];
?>
        <form method="POST" action="prosonta3.php">
        <input type="hidden" name="ornotapply" value="<?= $what ?>" />
        <input type="hidden" name="ornot" value="<?= $which ?>" />
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
            <input type="hidden" name="cid" value="<?= $req['cid'] ?>" />
            <input type="hidden" name="placeid" value="<?= $req['placeid'] ?>" />
            <input type="hidden" name="posid" value="<?= $req['posid'] ?>" />
            <input type="hidden" name="forthesi" value="<?= $req['forthesi'] ?>" />
        
        <table class="table datatable" style="width: 100%">
        <thead>
            <th class="all">#</th>
            <th class="all">Όνομα</th>
            <th class="all">Expression</th>
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
                <textarea type="text" class="input" cols="100" name="reg%s" id="reg%s" >%s</textarea>
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
    $a = sprintf("prosonta3.php?cid=%s&placeid=%s&posid=%s&forthesi=%s",$cid,$placeid,$posid,$forthesi);
    redirect($a);
    die;
}

if (array_key_exists("ornotapply",$_POST))
{
    if ($req['ornot'] == 0)
        QQ("UPDATE REQS2 SET ORLINK = ? WHERE ID = ?",array($req['to'],$req['ornotapply']));
    if ($req['ornot'] == 1)
        QQ("UPDATE REQS2 SET NOTLINK = ? WHERE ID = ?",array($req['to'],$req['ornotapply']));
    if ($req['ornot'] == 2)
        QQ("UPDATE REQS2 SET ANDLINK = ? WHERE ID = ?",array($req['to'],$req['ornotapply']));
    $a = sprintf("prosonta3.php?cid=%s&placeid=%s&posid=%s&forthesi=%s",$cid,$placeid,$posid,$forthesi);
    redirect($a);
    die;
}


printf('<button href="contest.php" class="autobutton button  is-danger">Πίσω</button> ');
$q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = ? AND  POSID = ? AND (FORTHESI IS NULL OR FORTHESI = '')",array($cid,$placeid,$posid));
if ($forthesi != '')
    $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = ? AND  POSID = ? AND FORTHESI = ?",array($cid,$placeid,$posid,$forthesi));
?>
<table class="table datatable" style="width: 100%">
<thead>
    <th class="all">#</th>
    <th class="all">Προσόν</th>
    <th class="all">Expression</th>
    <th class="all">Σκορ</th>
    <th class="all">Παράμετροι</th>
    <th class="all">Εντολές</th>
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

    $sfinal = '';
    $s2 = explode("|||",$r1['REGEXRESTRICTIONS'] ? $r1['REGEXRESTRICTIONS'] : '');
    foreach($s2 as $ss2)
    {
        $ss3 = explode("||",$ss2);
        if (count($ss3) == 2)
        {
            foreach($croot->params->children() as $ch)
            {
                if ($ss3[0] != $ch->attributes()['id'])
                    continue;
                $parname = $ch->attributes()['n'];
                if ($parname)
                    $sfinal .= sprintf("%s<br>%s",$parname,$ss3[1]);
                break;
            }
        }
    }

    printf('<td>%s</td>',$sfinal);
    printf('<td>%s</td>',$r1['SCORE']);


    printf('<td>');
    $RexCount = 0;
    if ($r1['REGEXRESTRICTIONS'] && strlen($r1['REGEXRESTRICTIONS']))   
        $RexCount = count(explode("|||",$r1['REGEXRESTRICTIONS']));

    printf('<button class="autobutton is-small %s button block" href="prosonta3.php?cid=%s&placeid=%s&posid=%s&forthesi=%s&regexedit=%s">Eval %s</button> ',$RexCount ? 'is-success' : 'is-link',$cid,$placeid,$posid,$forthesi,$r1['ID'],$RexCount);
    printf('<button class="autobutton is-small %s button block" href="prosonta3.php?cid=%s&placeid=%s&posid=%s&forthesi=%s&andedit=%s">AND %s</button> ',(int)$r1['ANDLINK'] ? 'is-success' : 'is-link',$cid,$placeid,$posid,$forthesi,$r1['ID'],(int)$r1['ANDLINK']);
    printf('<button class="autobutton is-small %s button block" href="prosonta3.php?cid=%s&placeid=%s&posid=%s&forthesi=%s&oredit=%s">OR %s</button> ',(int)$r1['ORLINK'] ? 'is-success' : 'is-link',$cid,$placeid,$posid,$forthesi,$r1['ID'],(int)$r1['ORLINK']);
    printf('<button class="autobutton is-small %s button block" href="prosonta3.php?cid=%s&placeid=%s&posid=%s&forthesi=%s&notedit=%s">NOT %s</button> ',(int)$r1['NOTLINK'] ? 'is-success' : 'is-link',$cid,$placeid,$posid,$forthesi,$r1['ID'],(int)$r1['NOTLINK']);
    printf('<button class="is-small %s button block" onclick="askmin(%s,%s,%s,%s,\'%s\');">MIN %s</button> ',(int)$r1['MINX'] > 0 ? 'is-success' : 'is-link',$r1['ID'],$cid,$placeid,$posid,$forthesi,(int)$r1['MINX'] > 0 ? $r1['MINX'] : '');
    printf('<button class="is-small %s button block" onclick="askmax(%s,%s,%s,%s,\'%s\');">MAX %s</button> ',(int)$r1['MAXX'] > 0 ? 'is-success' : 'is-link',$r1['ID'],$cid,$placeid,$posid,$forthesi,(int)$r1['MAXX'] > 0 ? $r1['MAXX'] : '');

    printf('</td>');
    printf('<td>');

    printf('<button class="is-small is-primary button" onclick="editx(%s,%s,%s,\'%s\',%s,%s,\'%s\');">Επεξεργασία</button> ',$cid,$placeid,$posid,$forthesi,$r1['ID'],$r1['PROSONTYPE'],$r1['SCORE']);
    printf('<button class="sureautobutton is-small is-danger button" href="prosonta3.php?cid=%s&placeid=%s&posid=%s&forthesi=%s&delete=%s">Διαγραφή</button> ',$cid,$placeid,$posid,$forthesi,$r1['ID']);
    printf('</td>');

    printf('</tr>');
}
?>
</tbody></table>
<script>
    function askmin(id,cid,placeid,posid,forthesi)
    {
        var x = prompt("Ελάχιστη τιμή προσόντων:");
        if (x == null) return;
        x = parseInt(x);
        var url = "prosonta3.php?setmin=" + id + "&value=" + x + "&cid=" + cid + "&placeid=" + placeid + "&posid=" + posid + "&forthesi=" + forthesi;
        window.location = url;
    }
    function askmax(id,cid,placeid,posid,forthesi)
    {
        var x = prompt("Μέγιστη τιμή προσόντων:");
        if (x == null) return;
        x = parseInt(x);
        var url = "prosonta3.php?setmax=" + id + "&value=" + x + "&cid=" + cid + "&placeid=" + placeid + "&posid=" + posid + "&forthesi=" + forthesi;
        window.location = url;
    }
    function editx(cid,placeid,posid,thesi,id,type,score)
    {
        toggleadd();
        $("#e").val(id);
        $("#proson").val(type);
        $("#score").val(score);
    }
</script>
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
        $("#e").val(0);
        $("#proson").val(0);
        $("#score").val('');
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
        <form method="POST" action="prosonta3.php">
            <input type="hidden" id="e" name="e" value="0"/>
            <input type="hidden" name="cid" value="<?= $cid ?>"/>
            <input type="hidden" name="placeid" value="<?= $placeid ?>"/>
            <input type="hidden" name="posid" value="<?= $posid ?>"/>
            <input type="hidden" name="forthesi" value="<?= $forthesi ?>"/>

                        Προσόν:
            <select class="select input" id="proson" name="PROSONTYPE">
                <?php echo PrintOptionsProson($xmlp,0,$row['PARAMID']); ?>
            </select><br><br>
            Σκορ (0 = Προαπαιτούμενο):
            <textarea class="input" id="score" name="SCORE" required></textarea><br><br>

        <button class="button is-success">Υποβολή<button>
    </form>
    <button class="button is-danger" onclick="toggleadd2();">Ακύρωση</button>
</div>



