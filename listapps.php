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
        QQ("UPDATE APPLICATIONS SET FORCEDMORIA = ? WHERE CID = ? AND ID = ?",array($req['score'],$req['cid'],$req['aid']));
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
    printf('<td>%d</td>',AppPreference($r1['ID']));
    printf('<td>%s</td>',$fr['DESCRIPTION']);
    printf('<td>%s</td>',$pr['DESCRIPTION']);
    $a = array();
    printf('<td>%s</td>',CalculateScore($ur['ID'],$req['cid'],$fr['ID'],$pr['ID'],0,$a));

    // Prosonta
    printf('<td>');
//    echo PrintProsontaForThesi($req['cid'],$fr['ID'],$pr['ID'],1);
    echo ViewUserProsontaForContest($ur['ID'],$req['cid']);
    printf('</td>');

    printf('<td>');
    if ($wa)
    {
        if ($r1['INACTIVE'] == 0)
            printf('<button class="autobutton is-success is-small button block" href="listapps.php?cid=%s&pid=%s&pos=%s&disable=%s">Ενεργή</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID']);
        else
            printf('<button class="autobutton is-danger is-small button block" href="listapps.php?cid=%s&pid=%s&pos=%s&enable=%s">Ανενεργή</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID']);
        if ($r1['FORCEDMORIA'] == 0)
            printf('<button class="is-link is-small button block" onclick="changescore(%s,%s,%s,%s);">Αλλαγή Σκορ</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID']);
        else
            printf('<button class="is-danger is-small button block" onclick="resetscore(%s,%s,%s,%s);">%s</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID'],$r1['FORCEDMORIA']);

        if ($r1['FORCERESULT'] == 1)
            printf('<button class="autobutton is-success is-small button block" href="listapps.php?cid=%s&pid=%s&pos=%s&aid=%s&result=-1">Ναι</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID']);
        else
        if ($r1['FORCERESULT'] == -1)
            printf('<button class="autobutton is-danger is-small button block" href="listapps.php?cid=%s&pid=%s&pos=%s&aid=%s&result=0">Όχι</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID']);
        else
            printf('<button class="autobutton is-link is-small button block" href="listapps.php?cid=%s&pid=%s&pos=%s&aid=%s&result=1">Υποχρεωτικό Αποτέλεσμα</button> ',$req['cid'],$req['pid'],$req['pos'],$r1['ID']);
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

