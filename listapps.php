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


$cidrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($req['cid']))->fetchArray();
if (!$cidrow)
{
    redirect("index.php");
    die;
}

if (!HasContestAccess($cidrow['ID'],$ur['ID'],1))
{
    redirect("index.php");
    die;
}

if(array_key_exists("disable",$req))
    {
        QQ("UPDATE APPLICATIONS SET INACTIVE = 1 WHERE CID = ? AND ID = ?",array($req['cid'],$req['disable']));
        redirect(sprintf("listapps.php?cid=%s",$req['cid']));
        die;
    }
if(array_key_exists("enable",$req))
    {
        QQ("UPDATE APPLICATIONS SET INACTIVE = 0 WHERE CID = ? AND ID = ?",array($req['cid'],$req['enable']));
        redirect(sprintf("listapps.php?cid=%s",$req['cid']));
        die;
    }
if(array_key_exists("score",$req))
    {
        QQ("UPDATE APPLICATIONS SET FORCEDMORIA = ? WHERE CID = ? AND ID = ?",array($req['score'],$req['cid'],$req['aid']));
        redirect(sprintf("listapps.php?cid=%s",$req['cid']));
        die;
    }
if(array_key_exists("result",$req))
    {
        QQ("UPDATE APPLICATIONS SET FORCERESULT = ? WHERE CID = ? AND ID = ?",array($req['result'],$req['cid'],$req['aid']));
        redirect(sprintf("listapps.php?cid=%s",$req['cid']));
        die;
    }

    
printf('<button href="contest.php?" class="autobutton button  is-danger">Πίσω</button> ');

?>
<table class="table datatable" style="width: 100%">
<thead>
    <th class="all">#</th>
    <th class="all">Όνομα</th>
    <th class="all">Φορέας</th>
    <th class="all">Θέση</th>
    <th class="all">Σκορ</th>
    <th class="all">Επιλογές</th>
</thead>
<tbody>
<?php
EnsureProsonLoaded();
$q1 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ?",array($req['cid']));
while($r1 = $q1->fetchArray())
{
    $ur = QQ("SELECT * FROM USERS WHERE ID = ?",array($r1['UID']))->fetchArray();
    if (!$ur)
        continue;
    $fr = QQ("SELECT * FROM PLACES WHERE ID = ?",array($r1['PID']))->fetchArray();
    $pr = QQ("SELECT * FROM POSITIONS WHERE ID = ?",array($r1['POS']))->fetchArray();
    printf('<tr>');
    printf('<td>%s</td>',$r1['ID']);
    printf('<td>%s %s</td>',$ur['LASTNAME'],$ur['FIRSTNAME']);
    printf('<td>%s</td>',$fr['DESCRIPTION']);
    printf('<td>%s</td>',$pr['DESCRIPTION']);
    printf('<td>%s</td>',ScoreForThesi($ur['ID'],$req['cid'],$r1['PID'],$r1['POS']));
    printf('<td>');
    if ($r1['INACTIVE'] == 0)
        printf('<button class="autobutton is-success is-small button block" href="listapps.php?cid=%s&disable=%s">Ενεργή</button> ',$req['cid'],$r1['ID']);
    else
        printf('<button class="autobutton is-danger is-small button block" href="listapps.php?cid=%s&enable=%s">Ανενεργή</button> ',$req['cid'],$r1['ID']);
    if ($r1['FORCEDMORIA'] == 0)
        printf('<button class="is-link is-small button block" onclick="changescore(%s,%s);">Αλλαγή Σκορ</button> ',$req['cid'],$r1['ID']);
    else
        printf('<button class="is-danger is-small button block" onclick="resetscore(%s,%s);">%s</button> ',$req['cid'],$r1['ID'],$r1['FORCEDMORIA']);
    if ($r1['FORCERESULT'] == 0)
        printf('<button class="is-link is-small button block" onclick="changeresult(%s,%s);">Υποχρεωτικό Αποτέλεσμα</button> ',$req['cid'],$r1['ID']);
    else
        printf('<button class="is-danger is-small button block" onclick="resetresult(%s,%s);">%s</button> ',$req['cid'],$r1['ID'],$r1['FORCERESULT']);
    printf('</td>');
    printf('</tr>');

}
?>
</tbody></table>
<script>
    function changescore(cid,aid)
    {
        var sc = prompt("Νέο Σκορ:");
        if (!sc)
            return;
        window.location = "listapps.php?cid=" + cid + "&aid=" + aid + "&score=" + sc;
    }
    function changeresult(cid,aid)
    {
        var sc = prompt("Νέο ID από τον πίνακα POSITIONS:");
        if (!sc)
            return;
        window.location = "listapps.php?cid=" + cid + "&aid=" + aid + "&result=" + sc;
    }
    function resetscore(cid,aid)
    {
        var sc = 0;
        window.location = "listapps.php?cid=" + cid + "&aid=" + aid + "&score=" + sc;
    }
    function resetresult(cid,aid)
    {
        var sc = 0;
        window.location = "listapps.php?cid=" + cid + "&aid=" + aid + "&result=" + sc;
    }
</script>
<?php

