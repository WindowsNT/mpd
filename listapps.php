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


$cidrow = Single("CONTESTS","ID",$req['cid']);
if (!$cidrow)
{
    redirect("index.php");
    die;
}

$ra = HasContestAccess($cidrow['ID'],$ur['ID'],0);
$wa = HasContestAccess($cidrow['ID'],$ur['ID'],1);

if (!$ra)
{
    redirect("index.php");
    die;
}

if (!array_key_exists("pid",$req))
    $req['pid'] = 0;
if (!array_key_exists("pos",$req))
    $req['pos'] = 0;


if(array_key_exists("disable",$req))
    {
        QQ("UPDATE APPLICATIONS SET INACTIVE = 1 WHERE CID = ? AND ID = ?",array($req['cid'],$req['disable']));
        redirect(sprintf("listapps.php?cid=%s&pid=%s&pos=%s",$req['cid'],$req['pid'],$req['pos']));
        die;
    }
if(array_key_exists("enable",$req))
    {
        QQ("UPDATE APPLICATIONS SET INACTIVE = 0 WHERE CID = ? AND ID = ?",array($req['cid'],$req['enable']));
        redirect(sprintf("listapps.php?cid=%s&pid=%s&pos=%s",$req['cid'],$req['pid'],$req['pos']));
        die;
    }
if(array_key_exists("score",$req))
    {
        if (array_key_exists("prid",$req))
        {
            BeginTransaction();
            QQ("DELETE FROM PROSONFORCE WHERE UID = ? AND CID = ? AND PLACEID = ? AND POS = ? AND PIDCLASS = ? AND PRID = ?",array($req['uid'],$req['cid'],$req['pid'],$req['pos'],$req['class'],$req['prid']));
            if ($req['score'] > 0)
                QQ("INSERT INTO PROSONFORCE (UID,CID,PLACEID,POS,PIDCLASS,PRID,SCORE) VALUES(?,?,?,?,?,?,?)",array($req['uid'],$req['cid'],$req['pid'],$req['pos'],$req['class'],$req['prid'],$req['score']));
            QQ("COMMIT");
        }
        else
        {
            QQ("UPDATE APPLICATIONS SET FORCEDMORIA = ? WHERE CID = ? AND ID = ?",array($req['score'],$req['cid'],$req['aid']));
        }
        redirect(sprintf("listapps.php?cid=%s&pid=%s&pos=%s",$req['cid'],$req['pid'],$req['pos']));
        die;
    }
if(array_key_exists("result",$req))
    {
        QQ("UPDATE APPLICATIONS SET FORCERESULT = ? WHERE CID = ? AND ID = ?",array($req['result'],$req['cid'],$req['aid']));
        redirect(sprintf("listapps.php?cid=%s&pid=%s&pos=%s",$req['cid'],$req['pid'],$req['pos']));
        die;
    }

    
printf('<button href="contest.php?" class="autobutton button  is-danger">Πίσω</button> ');

?>
<table class="table datatable" style="width: 100%">
<thead>
    <th class="all">#</th>
    <th class="all">Όνομα</th>
    <th class="all">Προτίμηση</th>
    <th class="all">Φορέας</th>
    <th class="all">Θέση</th>
    <th class="all">Σκορ</th>
    <th class="all">Προσόντα</th>
    <th class="all">Επιλογές</th>
</thead>
<tbody>
<?php
EnsureProsonLoaded();
$q1 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? ORDER BY DATE",array($req['cid']));
while($r1 = $q1->fetchArray())
{
    if (array_key_exists("pid",$req) && $req['pid'] != 0 && $req['pid'] != $r1['PID'])
        continue;
    if (array_key_exists("pos",$req) && $req['pos'] != 0 && $req['pos'] != $r1['POS'])
        continue;
    $ur = Single("USERS","ID",$r1['UID']);
    if (!$ur)
        continue;
    $fr = Single("PLACES","ID",$r1['PID']);
    $pr = Single("POSITIONS","ID",$r1['POS']);
    printf('<tr>');
    printf('<td>%s</td>',$r1['ID']);
    printf('<td>%s %s</td>',$ur['LASTNAME'],$ur['FIRSTNAME']);
    $whatpref = AppPreference($r1['ID']);
    printf('<td>%d</td>',$whatpref);
    printf('<td>%s</td>',$fr['DESCRIPTION']);
    printf('<td>%s</td>',$pr['DESCRIPTION']);
    $a = array();
    $desc = array();
    $scx = CalculateScore($ur['ID'],$req['cid'],$fr['ID'],$pr['ID'],0,$a,0,$desc,0,0,$whatpref);
    printf('<td>%s</td>',$scx);


    // Prosonta
    printf('<td><button onclick="toggle(\'#pro%s\');" class="button is-small is-link">Προβολή</button><div id="pro%s" style="display:none;">',$r1['ID'],$r1['ID']);
    printf('<table class="table"><thead><th>#</th><th>Προσόν</th><th>Μόρια</th><th>Αρχεία</th></thead><tbody>');
    foreach($desc as $dd)
    {
        $prosontalist = $dd['h'];
        foreach($prosontalist as $prosonrow)
        {
            printf('<tr>');
            printf('<td>%s</td>',$prosonrow['ID']);
            if ($cidrow['CLASSID'] == 0)
            {
                $exist = QQ("SELECT * FROM PROSONFORCE WHERE UID = ? AND CID = ? AND PLACEID = ? AND POS = ? AND PIDCLASS = ? AND PRID = ?",array($ur['ID'],$req['cid'],$fr['ID'],$pr['ID'],$prosonrow['CLASSID'],$prosonrow['ID']))->fetchArray();
                if ($exist)
                    printf('<button class="button is-small is-danger" onclick="changeprosonscore(%s,%s,%s,%s,%s,%s,1);">%s</button> %s<br>',$ur['ID'],$req['cid'],$fr['ID'],$pr['ID'],$prosonrow['CLASSID'],$prosonrow['ID'],$exist['SCORE'],$prosonrow['DESCRIPTION']);
                else
                    printf('<button class="button is-small is-link" onclick="changeprosonscore(%s,%s,%s,%s,%s,%s);">%s</button> %s<br>',$ur['ID'],$req['cid'],$fr['ID'],$pr['ID'],$prosonrow['CLASSID'],$prosonrow['ID'],$dd['s'],$prosonrow['DESCRIPTION']);
            }
            else
            {
                if ($prosonrow['ID'] == 0)
                    printf('<td>%s</td><td><button class="button is-small is-info">%s</button></td>',$prosonrow['DESCRIPTION'],$dd['s']);
                else
                    printf('<td>%s</td><td>%s</td>',$prosonrow['DESCRIPTION'],$dd['s']);
            }

            // View the items
            $files4 = QQ("SELECT * FROM PROSONFILE WHERE PID = ?",array($prosonrow['ID']));
            $subt = '';
            while($files2 = $files4->fetchArray())
            {
                $subt .= sprintf('<a target="_blank" href="viewfile.php?f=%s">%s</a><br>',$files2['ID'],$files2['DESCRIPTION']);
            }
            printf('<td>%s</td>', $subt);
            printf('</tr>');
        }
    }
    printf('</tbody></table>');
    //    echo PrintProsontaForThesi($req['cid'],$fr['ID'],$pr['ID'],1);
//    echo ViewUserProsontaForContest($ur['ID'],$req['cid'],$fr['ID'],$pr['ID']);
    printf('</td>');

    printf('<td>');
    if ($wa)
    {
        $ec = QQ("SELECT * FROM OBJECTIONS WHERE AID = ? ",array($r1['ID']))->fetchArray();
        if ($ec && $ec['RESULT'] != 2)
            printf('<button class="autobutton is-danger is-small button block" href="objections.php?aid=%s">Ενστάσεις</button> ',$r1['ID']);
        if ($ec && $ec['RESULT'] == 2)
            printf('<button class="autobutton is-success is-small button block" href="objections.php?aid=%s">Ενστάσεις</button> ',$r1['ID']);
        if ($r1['INACTIVE'] == 0)
            printf('<button class="autobutton is-success is-small button block" href="listapps.php?cid=%s&pid=%s&pos=%s&disable=%s">Ενεργή</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID']);
        else
            printf('<button class="autobutton is-danger is-small button block" href="listapps.php?cid=%s&pid=%s&pos=%s&enable=%s">Ανενεργή</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID']);
        if ($r1['FORCEDMORIA'] == 0)
            printf('<button class="is-primary is-small button block" onclick="changescore(%s,%s,%s,%s);">Αλλαγή Σκορ</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID']);
        else
            printf('<button class="is-danger is-small button block" onclick="resetscore(%s,%s,%s,%s);">%s</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID'],$r1['FORCEDMORIA']);

        if ($r1['FORCERESULT'] == 1)
            printf('<button class="autobutton is-success is-small button block" href="listapps.php?cid=%s&pid=%s&pos=%s&aid=%s&result=-1">Ναι</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID']);
        else
        if ($r1['FORCERESULT'] == -1)
            printf('<button class="autobutton is-danger is-small button block" href="listapps.php?cid=%s&pid=%s&pos=%s&aid=%s&result=0">Όχι</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID']);
        else
            printf('<button class="autobutton is-primary is-small button block" href="listapps.php?cid=%s&pid=%s&pos=%s&aid=%s&result=1">Υποχρεωτικό Αποτέλεσμα</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID']);
    }
    else
    {
        if ($r1['INACTIVE'] == 0) printf('Ενεργή<br>'); else printf('Ανενεργή<br>');
        if ($r1['FORCEDMORIA'] != 0) printf('%s<br>',$r1['FORCEDMORIA']);
        if ($r1['FORCERESULT'] == 1) printf('Ναι<br>');
        if ($r1['FORCERESULT'] == -1) printf('Όχι<br>');

    }
    printf('</td>');
    printf('</tr>');

}
?>
</tbody></table>
<script>
    function changeprosonscore(uid,cid,pid,pos,classid,prid,reset = 0)
    {
        var sc = reset == 1 ? 0 : prompt("Νέο Σκορ:");
        if (!reset && !sc)
            return;
        window.location = "listapps.php?cid=" + cid + "&pid=" + pid + "&pos=" + pos + "&class=" + classid + "&prid=" + prid + "&score=" + sc + "&uid=" + uid;
    }

    function changescore(cid,pid,pos,aid)
    {
        var sc = prompt("Νέο Σκορ:");
        if (!sc)
            return;
        window.location = "listapps.php?cid=" + cid + "&pid=" + pid + "&pos=" + pos + "&aid=" + aid + "&score=" + sc;
    }
    function changeresult(cid,pid,pos,aid)
    {
        var sc = prompt("Νέο ID από τον πίνακα POSITIONS:");
        if (!sc)
            return;
        window.location = "listapps.php?cid=" + cid + "&aid=" + aid + "&result=" + sc;
    }
    function resetscore(cid,pid,pos,aid)
    {
        var sc = 0;
        window.location = "listapps.php?cid=" + cid + "&pid=" + pid + "&pos=" + pos + "&aid=" + aid + "&score=" + sc;
    }
    function resetresult(cid,pid,pos,aid)
    {
        var sc = 0;
        window.location = "listapps.php?cid=" + cid + "&aid=" + aid + "&result=" + sc;
    }
</script>
<?php

