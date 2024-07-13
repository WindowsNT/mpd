<?php

require_once "function.php";
require_once "auth.php";


if (!$afm || !$ur)
    {
        redirect("index.php");
        die;
    }


require_once "output.php";
echo '<div class="content" style="margin: 20px">';
$t = time();
if (!array_key_exists("cid",$req))
{
    echo '<button href="index.php" class="autobutton button is-danger">Πίσω</button> ';
    echo '<table class="table datatable" style="width: 100%">';
    echo '<thead>
                <th class="all">#</th>
                <th class="all">Υπουργείο</th>
                <th class="all">Κατηγορία</th>
                <th class="all">Περιγραφή</th>
                <th class="all">Έναρξη</th>
                <th class="all">Λήξη</th>
                <th class="all">Επιλογές</th>
                <th class="all">Αιτήσεις</th>
            </thead><tbody>';

    $q1 = QQ("SELECT * FROM CONTESTS WHERE STARTDATE < $t AND ENDDATE > $t ORDER BY ENDDATE ASC");
    while($r1 = $q1->fetchArray())
    {
        printf('<tr>');
        printf('<td>%s</td>',$r1['ID']);
        printf('<td>%s</td>',$r1['MINISTRY']);
        printf('<td>%s</td>',$r1['CATEGORY']);
        printf('<td>%s</td>',$r1['DESCRIPTION']);
        printf('<td>%s</td>',date("Y-m-d",$r1['STARTDATE']));
        printf('<td>%s</td>',date("Y-m-d",$r1['ENDDATE']));
        printf('<td>');
        printf('<button class="button is-small is-warning autobutton" href="applications.php?cid=%s">Προβολή</a>',$r1['ID']);
        printf('</td>');

        printf('<td>');
        $q44 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? AND UID = ?",array($r1['ID'],$ur['ID']));
        while($r44 = $q44->fetchArray())
        {
            $placerow = Single("PLACES","ID",$r44['PID']);
            $posrow = Single("POSITIONS","ID",$r44['POS']);
            printf('<button class="is-link is-small button autobutton block" href="applications.php?&cid=%s&pid=%s&pos=%s">%s<br>Α.Π. %s<br><br>%s<br>%s</br>Μόρια %s</button> <br>',$r44['CID'],$r44['PID'],$r44['POS'],date("d/m/Y H:i",$r44['DATE']),ApplicationProtocol($r44),$placerow['DESCRIPTION'],$posrow['DESCRIPTION'],ScoreForAitisi($r44['ID']));
        }
        printf('</td>');


        printf('</tr>');

    }

    echo '</tbody></table>';
    die;
}

$contestrow = QQ("SELECT * FROM CONTESTS WHERE STARTDATE < $t AND ENDDATE > $t AND ID = ?",array($req['cid']))->fetchArray();
if (!$contestrow)
    {
        redirect("applications.php");
        die;
    }

if (!array_key_exists("pid",$req))
    {
    echo '<button href="applications.php" class="autobutton button is-danger">Πίσω</button> <button href="index.php" class="autobutton button is-warning">Αρχική</button> <hr>';
    printf("%s<hr>Επιλέξτε φορέα που σας ενδιαφέρει:",$contestrow['DESCRIPTION']);

    echo '<table class="table datatable" style="width: 100%">';
    echo '<thead>
                <th class="all">#</th>
                <th class="all">Περιγραφή</th>
                <th class="all">Επιλογές</th>
                <th class="all">Αιτήσεις</th>
            </thead><tbody>';

    $q2 = QQ("SELECT * FROM PLACES  WHERE CID = ?",array($contestrow['ID']));
    while($r2 = $q2->fetchArray())
    {
        printf('<tr>');
        printf('<td>%s</td>',$r2['ID']);
        printf('<td>%s</td>',$r2['DESCRIPTION']);
        printf('<td>');
        printf('<button class="button is-small is-warning autobutton" href="applications.php?cid=%s&pid=%s">Προβολή</a>',$contestrow['ID'],$r2['ID']);
        printf('</td>');

        printf('<td>');
        $q44 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? AND UID = ? AND PID = ?",array($contestrow['ID'],$ur['ID'],$r2['ID']));
        while($r44 = $q44->fetchArray())
        {
            $placerow = Single("PLACES","ID",$r44['PID']);
            $posrow = Single("POSITIONS","ID",$r44['POS']);
            printf('<button class="is-link is-small button autobutton block" href="applications.php?&cid=%s&pid=%s&pos=%s">%s<br>Α.Π. %s<br><br>%s<br>%s</br>Μόρια %s</button> <br>',$r44['CID'],$r44['PID'],$r44['POS'],date("d/m/Y H:i",$r44['DATE']),ApplicationProtocol($r44),$placerow['DESCRIPTION'],$posrow['DESCRIPTION'],ScoreForAitisi($r44['ID']));
        }
        printf('</td>');

        printf('</tr>');
    }

echo '</tbody></table>';
die;
}

$placerow = QQ("SELECT * FROM PLACES WHERE CID = ? AND ID = ?",array($contestrow['ID'],$req['pid']))->fetchArray();
if (!$placerow)
    {
        redirect(sprintf("applications.php?cid=%s",$contestrow['ID']));
        die;
    }

if (!array_key_exists("pos",$req))
    {
    printf('<button href="applications.php?cid=%s" class="autobutton button is-danger">Πίσω</button> <button href="index.php" class="autobutton button is-warning">Αρχική</button> <hr>',$contestrow['ID']);
    printf("%s<br>%s<hr>Επιλέξτε θέση που σας ενδιαφέρει:",$contestrow['DESCRIPTION'],$placerow['DESCRIPTION']);

    echo '<table class="table datatable" style="width: 100%">';
    echo '<thead>
                <th class="all">#</th>
                <th class="all">Περιγραφή</th>
                <th class="all">Θέσεις</th>
                <th class="all">Επιλογές</th>
                <th class="all">Αιτήσεις</th>
            </thead><tbody>';

    $q3 = QQ("SELECT * FROM POSITIONS  WHERE CID = ? AND PLACEID = ?",array($contestrow['ID'],$placerow['ID']));
    while($r3 = $q3->fetchArray())
    {
        printf('<tr>');
        printf('<td>%s</td>',$r3['ID']);
        printf('<td>%s</td>',$r3['DESCRIPTION']);
        printf('<td>%s</td>',$r3['COUNT']);

        printf('<td>');
        printf('<button class="button is-small is-warning autobutton" href="applications.php?cid=%s&pid=%s&pos=%s">Προβολή</a>',$contestrow['ID'],$placerow['ID'],$r3['ID']);
        printf('</td>');

        printf('<td>');
        $q44 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? AND UID = ? AND PID = ? AND POS = ?",array($contestrow['ID'],$ur['ID'],$placerow['ID'],$r3['ID']));
        while($r44 = $q44->fetchArray())
        {
            $placerow = Single("PLACES","ID",$r44['PID']);
            $posrow = Single("POSITIONS","ID",$r44['POS']);
            printf('<button class="is-link is-small button autobutton block" href="applications.php?&cid=%s&pid=%s&pos=%s">%s<br>Α.Π. %s<br><br>%s<br>%s</br>Μόρια %s</button> <br>',$r44['CID'],$r44['PID'],$r44['POS'],date("d/m/Y H:i",$r44['DATE']),ApplicationProtocol($r44),$placerow['DESCRIPTION'],$posrow['DESCRIPTION'],ScoreForAitisi($r44['ID']));
        }
        printf('</td>');


        printf('</tr>');
    }

echo '</tbody></table>';
die;
}

    
$posrow = QQ("SELECT * FROM POSITIONS WHERE CID = ? AND PLACEID = ? AND ID = ?",array($contestrow['ID'],$placerow['ID'],$req['pos']))->fetchArray();
if (!$posrow)
    {
        redirect("applications.php");
        die;
    }

if (array_key_exists("aid",$req))
{
    if ($req['aid'] == 0)
    {
        QQ("INSERT INTO APPLICATIONS (UID,CID,PID,POS,DATE) VALUES (?,?,?,?,?)",array(
            $ur['ID'],$contestrow['ID'],$placerow['ID'],$posrow['ID'],time()
        ));
        PushAithsiCompleted($lastRowID);
    }
    else
        QQ("DELETE FROM APPLICATIONS WHERE ID = ? AND UID = ?",array($req['aid'],$ur['ID']));

    unset($req['aid']);
}

if (!array_key_exists("aid",$req))
    {
        printf('<button href="applications.php?cid=%s&pid=%s" class="autobutton button is-danger">Πίσω</button> <button href="index.php" class="autobutton button is-warning">Αρχική</button> <hr>',$contestrow['ID'],$placerow['ID']);
        printf("%s<br>%s<br>%s<hr>",$contestrow['DESCRIPTION'],$placerow['DESCRIPTION'],$posrow['DESCRIPTION']);

    $app = QQ("SELECT * FROM APPLICATIONS WHERE UID = ? AND CID = ? AND PID = ? AND POS = ?",array(
        $ur['ID'],$contestrow['ID'],$placerow['ID'],$posrow['ID'],
    ))->fetchArray();
    if (!$app)
    {
        $sc = ScoreForThesi($ur['ID'],$req['cid'],$req['pid'],$posrow['ID'],true);
        if ($sc >= 0)
            printf('<br>Τα μόριά σας για αυτή τη θέση: <b>%s</b><br><br><button class="button is-primary autobutton" href="applications.php?cid=%s&pid=%s&pos=%s&aid=0">Κάνε αίτηση</a>',$sc,$contestrow['ID'],$placerow['ID'],$posrow['ID']);
        else
            printf('<br>Δεν μπορείτε να κάνετε αίτηση για αυτή τη θέση: <br><b>%s</b>',$rejr);
//            echo PrintProsontaForThesi($req['cid'],$req['pid'],$req['pos']);
        }
    else
    {
        printf('Έγινε αίτηση (%s)<br>Α.Π. %s<br><br><button class="button is-danger sureautobutton" q="Θέλετε σίγουρα να ακυρώσετε την αίτηση;" href="applications.php?cid=%s&pid=%s&pos=%s&aid=%s">Διαγραφή</button><br><br>',date("d/m/Y H:i",$app['DATE']),ApplicationProtocol($app),$contestrow['ID'],$placerow['ID'],$posrow['ID'],$app['ID']);
        $sc = ScoreForThesi($ur['ID'],$req['cid'],$req['pid'],$posrow['ID'],true);
        printf("Σύνολο μορίων: %s<br>",$sc);
        if (AppPreference($app['ID']) == 1)
            printf("+$first_pref_score Πρώτη προτίμηση<br>");

    }
}
