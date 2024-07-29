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

if (array_key_exists("results",$req))
{
    $cr = Single("CONTESTS","ID",$req['results']);
    if (!$cr)
        die;
    if ($cr['ENDDATE'] > $t)
        die;
    if ($cr['MORIAVISIBLE'] < 2)
        die;

    echo 'Αποτελέσματα Διαγωνισμού<hr>';
    $w = QQ("SELECT * FROM WINTABLE WHERE CID = ? AND UID = ?",array($req['results'],$ur['ID']))->fetchArray();
    if (!$w)
        printf('<div class="notification is-warning">
    Δεν έχετε επιλεγεί για κάποια θέση!
</div>');
    else
    {
        $desc = array();
        $sc = ScoreForThesi($ur['ID'],$req['results'],$w['PID'],$w['POS'],0,$desc,AppPreference($w['AID']));
        $approw = Single("APPLICATIONS","ID",$w['AID']);
        $placerow = Single("PLACES","ID",$w['PID']);
        $posrow = Single("POSITIONS","ID",$w['POS']);
        printf('<div class="notification is-success">
        Εχετε επιλεγεί!<br><br>
        Φορέας: <b>%s</b><br>
        Θέση: <b>%s</b><br>
        Μόρια: <b>%s</b>
</div>',$placerow['DESCRIPTION'],$posrow['DESCRIPTION'],$sc);

    }

    echo 'Οι αιτήσεις σας<hr>';
    echo '<table class="table datatable" style="width: 100%">';
    echo '<thead>
                <th class="all">#</th>
                <th class="all">Φορέας</th>
                <th class="all">Θέση</th>
                <th class="all">Μόρια</th>
                <th class="all">Αιτήσεις Άλλων</th>
                <th class="all">Ενέργειες</th>
            </thead><tbody>';

    $q1 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? AND UID = ? ORDER BY ID ASC",array($req['results'],$ur['ID']));
    while($r1 = $q1->fetchArray())
    {
        $pref = AppPreference($r1['ID']);

        $won = Single("WINTABLE","AID",$r1['ID']);

        $fr = Single("PLACES","ID",$r1['PID']);
        $pr = Single("POSITIONS","ID",$r1['POS']);
        $desc = array();
        $sc = ScoreForThesi($ur['ID'],$req['results'],$fr['ID'],$pr['ID'],0,$desc,$pref == 1);
        printf('<tr>');
        printf('<td>%s</td>',$r1['ID']);
        printf('<td>%s</td>',$fr['DESCRIPTION']);
        printf('<td>%s</td>',$pr['DESCRIPTION']);
        printf('<td>%s</td>',$sc);

        printf('<td>');

        $q2 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? AND PID = ? AND POS = ?",array($req['results'],$fr['ID'],$pr['ID']));
        while($r2 = $q2->fetchArray())
        {
            if ($r2['UID'] == $ur['ID']) continue;

            
        }
        printf('</td>');

        printf('<td>');
        if (!$won)
        {   
            printf('<button class="autobutton is-warning is-small button" href="objections.php?aid=%s">Ένσταση</button>',$r1['ID']);
        }
        printf('</td>');
        
        printf('</tr>');
    }
    echo '</tbody></table>';
    die;    
}

if (!array_key_exists("cid",$req))
{
    PrintButtons(array(array("n" => "Πίσω","h" => "index.php","s" => "is-danger")));
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

    $q1 = QQ("SELECT * FROM CONTESTS ORDER BY ENDDATE ASC");
    while($r1 = $q1->fetchArray())
    {
        $CanAct = 1;
        if ($r1['STARTDATE'] > $t || $r1['ENDDATE'] < $t)
        {
            $CanAct = 0;
            $cait = QQ("SELECT COUNT(*) FROM APPLICATIONS WHERE CID = ? AND UID = ?",array($r1['ID'],$ur['ID']))->fetchArray()[0];
            if (!$cait)
                continue;
        }

        printf('<tr>');
        printf('<td>%s</td>',$r1['ID']);
        printf('<td>%s</td>',$r1['MINISTRY']);
        printf('<td>%s</td>',$r1['CATEGORY']);
        printf('<td>%s</td>',$r1['DESCRIPTION']);
        printf('<td>%s</td>',date("Y-m-d",$r1['STARTDATE']));
        printf('<td>%s</td>',date("Y-m-d",$r1['ENDDATE']));
        printf('<td>');
        printf('<button class="button is-small is-warning autobutton block" href="applications.php?cid=%s">Προβολή Φορέων</button> ',$r1['ID']);
        if ($CanAct == 0 && $r1['MORIAVISIBLE'] >= 2)
            printf(' <button class="button is-small is-danger autobutton block" href="applications.php?results=%s">Προβολή Αποτελεσμάτων</button>',$r1['ID']);
        printf('</td>');

        printf('<td>');
        $q44 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? AND UID = ?",array($r1['ID'],$ur['ID']));
        while($r44 = $q44->fetchArray())
        {
            $placerow = Single("PLACES","ID",$r44['PID']);
            $posrow = Single("POSITIONS","ID",$r44['POS']);
            printf('<button class="is-success is-small button autobutton block" href="applications.php?&cid=%s&pid=%s&pos=%s">%s<br>Α.Π. %s<br><br>%s<br>%s</br>',$r44['CID'],$r44['PID'],$r44['POS'],date("d/m/Y H:i",$r44['DATE']),ApplicationProtocol($r44),$placerow['DESCRIPTION'],$posrow['DESCRIPTION']);
            if ($r1['MORIAVISIBLE'] >= 1)
                printf('Μόρια %s</button> <br>',ScoreForAitisi($r44['ID']));
        }
        printf('</td>');


        printf('</tr>');

    }

    echo '</tbody></table>';
    die;
}

$contestrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($req['cid']))->fetchArray();
if (!$contestrow)
    {
        redirect("applications.php");
        die;
    }

if (!array_key_exists("pid",$req))
    {
    PrintButtons(array(array("n" => "Πίσω","h" => "applications.php","s" => "is-danger"),array("n" => "Αρχική","h" => "index.php","s" => "is-warning")));

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
        printf('<button class="button is-small is-warning autobutton" href="applications.php?cid=%s&pid=%s">Επιλογή Φορέα</a>',$contestrow['ID'],$r2['ID']);
        printf('</td>');

        printf('<td>');
        $q44 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? AND UID = ? AND PID = ?",array($contestrow['ID'],$ur['ID'],$r2['ID']));
        while($r44 = $q44->fetchArray())
        {
            $placerow = Single("PLACES","ID",$r44['PID']);
            $posrow = Single("POSITIONS","ID",$r44['POS']);
            printf('<button class="is-success is-small button autobutton block" href="applications.php?&cid=%s&pid=%s&pos=%s">%s<br>Α.Π. %s<br><br>%s<br>%s</br></button> <br>',$r44['CID'],$r44['PID'],$r44['POS'],date("d/m/Y H:i",$r44['DATE']),ApplicationProtocol($r44),$placerow['DESCRIPTION'],$posrow['DESCRIPTION']);
            if ($contestrow['MORIAVISIBLE'] >= 1)
                printf('Μόρια %s</button> <br>',ScoreForAitisi($r44['ID']));
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
    PrintButtons(array(array("n" => "Πίσω","h" => sprintf("applications.php?cid=%s",$contestrow['ID']),"s" => "is-danger"),array("n" => "Αρχική","h" => "index.php","s" => "is-warning")));
    printf("%s<br>%s<hr>Επιλέξτε θέση που σας ενδιαφέρει:",$contestrow['DESCRIPTION'],$placerow['DESCRIPTION']);

    echo '<table class="table datatable" style="width: 100%">';
    printf('<thead>
                <th class="all">#</th>
                <th class="all">Περιγραφή</th>
                <th class="all">Θέσεις</th>
                <th class="all">Επιλογές</th>
                %s
                <th class="all">Αιτήσεις</th>
            </thead><tbody>',$contestrow['MORIAVISIBLE'] >= 1 ? '<th class="all">Μόρια</th>' : '');

    $q3 = QQ("SELECT * FROM POSITIONS  WHERE CID = ? AND PLACEID = ?",array($contestrow['ID'],$placerow['ID']));
    while($r3 = $q3->fetchArray())
    {
        printf('<tr>');
        printf('<td>%s</td>',$r3['ID']);
        printf('<td>%s</td>',$r3['DESCRIPTION']);
        printf('<td>%s</td>',$r3['COUNT']);

        printf('<td>');
        printf('<button class="button is-small is-warning autobutton" href="applications.php?cid=%s&pid=%s&pos=%s">Επιλογή θέσης</a>',$contestrow['ID'],$placerow['ID'],$r3['ID']);
        printf('</td>');

        if ($contestrow['MORIAVISIBLE'] >= 1)
        {
            printf('<td>');
            printf("%s",ScoreForThesi($ur['ID'],$contestrow['ID'],$placerow['ID'],$r3['ID']));
            printf('</td>');
        }
        printf('<td>');
        $q44 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? AND UID = ? AND PID = ? AND POS = ?",array($contestrow['ID'],$ur['ID'],$placerow['ID'],$r3['ID']));
        while($r44 = $q44->fetchArray())
        {
            $placerow = Single("PLACES","ID",$r44['PID']);
            $posrow = Single("POSITIONS","ID",$r44['POS']);
            printf('<button class="is-success is-small button autobutton block" href="applications.php?&cid=%s&pid=%s&pos=%s">%s<br>Α.Π. %s<br><br>%s<br>%s</br></button> <br>',$r44['CID'],$r44['PID'],$r44['POS'],date("d/m/Y H:i",$r44['DATE']),ApplicationProtocol($r44),$placerow['DESCRIPTION'],$posrow['DESCRIPTION']);
            if ($contestrow['MORIAVISIBLE'] >= 1)
                printf('Μόρια %s</button> <br>',ScoreForAitisi($r44['ID']));
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
        QQ("DELETE FROM APPLICATIONS WHERE UID = ? AND CID = ? AND PID = ? AND POS = ?",array($ur['ID'],$contestrow['ID'],$placerow['ID'],$posrow['ID']));
        QQ("INSERT INTO APPLICATIONS (UID,CID,PID,POS,DATE) VALUES (?,?,?,?,?)",array(
            $ur['ID'],$contestrow['ID'],$placerow['ID'],$posrow['ID'],time()
        ));
        PushAithsiCompleted($lastRowID);
    }
    else
        QQ("DELETE FROM APPLICATIONS WHERE ID = ? AND UID = ?",array($req['aid'],$ur['ID']));

    unset($req['aid']);
}


$CanAct = 1;
$t = time();
if ($contestrow['STARTDATE'] > $t || $contestrow['ENDDATE'] < $t)
{   
    $CanAct = 0;
}

if (!array_key_exists("aid",$req))
    {
        PrintButtons(array(array("n" => "Πίσω","h" => sprintf("applications.php?cid=%s&pid=%s",$contestrow['ID'],$placerow['ID']),"s" => "is-danger"),array("n" => "Αρχική","h" => "index.php","s" => "is-warning")));
        printf("%s<br>%s<br>%s<hr>",$contestrow['DESCRIPTION'],$placerow['DESCRIPTION'],$posrow['DESCRIPTION']);

    $app = QQ("SELECT * FROM APPLICATIONS WHERE UID = ? AND CID = ? AND PID = ? AND POS = ?",array(
        $ur['ID'],$contestrow['ID'],$placerow['ID'],$posrow['ID'],
    ))->fetchArray();
    $desc = array();        
    if (!$app)
    {
        if (!$CanAct)
        {
            echo 'H προθεσμία των αιτήσεων έληξε.';
        }
        else
        {
            $sc = ScoreForThesi($ur['ID'],$req['cid'],$req['pid'],$posrow['ID'],0,$desc);
            if ($sc >= 0)
                {
                    if ($contestrow['MORIAVISIBLE'] >= 1)
                        echo PrintDescriptionFromScore($desc,false);
                    if ($contestrow['MORIAVISIBLE'] >= 1)
                        printf('<br>Τα μόριά σας για αυτή τη θέση: <b>%s</b><br><br>',$sc);
                    printf('<button class="button is-primary  autobutton" href="applications.php?cid=%s&pid=%s&pos=%s&aid=0">Κάνε αίτηση</a>',$contestrow['ID'],$placerow['ID'],$posrow['ID']);
                }
            else
                printf('<br>Δεν μπορείτε να κάνετε αίτηση για αυτή τη θέση: <br><b>%s</b>',$rejr);
        }
//            echo PrintProsontaForThesi($req['cid'],$req['pid'],$req['pos']);
        }
    else
    {

        if ($CanAct)
            printf('<div class="notification is-info">Έγινε αίτηση (%s)<br>Α.Π. %s</div><button class="button is-danger sureautobutton" q="Θέλετε σίγουρα να ακυρώσετε την αίτηση;" href="applications.php?cid=%s&pid=%s&pos=%s&aid=%s">Διαγραφή</button><br><br>',date("d/m/Y H:i",$app['DATE']),ApplicationProtocol($app),$contestrow['ID'],$placerow['ID'],$posrow['ID'],$app['ID']);
        else
            printf('<div class="notification is-info">Έγινε αίτηση (%s)<br>Α.Π. %s</div>',date("d/m/Y H:i",$app['DATE']),ApplicationProtocol($app));
            $pref = AppPreference($app['ID']);

        $sc = ScoreForThesi($ur['ID'],$req['cid'],$req['pid'],$posrow['ID'],0,$desc,$pref == 1);
        if ($contestrow['MORIAVISIBLE'] >= 1)
            {
                echo PrintDescriptionFromScore($desc,true);
            printf("Σύνολο μορίων: %s<br>",$sc);
            }
    }
}
