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





$ra = HasContestAccess($req['cid'],$ur['ID'],0);
$wa = HasContestAccess($req['cid'],$ur['ID'],1);
if (!$ra)
    die;

if (array_key_exists("reset",$req) && $wa)
{
    QQ("DELETE FROM WINTABLE WHERE CID = ?",array($req['cid']));
    redirect(sprintf("contest.php",$req['cid']));
    die;
}
$cr = Single("CONTESTS","ID",$req['cid']);

function moria_sort($a, $b) {
    if ($a['score'] == $b['score']) return 0;
    return ($a['score'] < $b['score']) ? -1 : 1;
  }

$checking_prefefence = 1;
if (array_key_exists("pref",$req))
  $checking_prefefence = $req['pref'];
$exu = array();

$completed = QQ("SELECT * FROM WINTABLE WHERE CID = ?",array($req['cid']));
while ($co = $completed->fetchArray())
{
    $exu[] = $co['UID'];
}


$changed = 0;


$place_query = QQ("SELECT * FROM PLACES WHERE CID = ?",array($req['cid']));
while($place = $place_query->fetchArray())
{
    if (!array_key_exists("run",$req))
        break;
    if (!$wa)
        break;
    $position_query = QQ("SELECT * FROM POSITIONS WHERE CID = ? AND PLACEID = ?",array($req['cid'],$place['ID']));
    while($position = $position_query->fetchArray())
    {
        // completed?
        $completed = QQ("SELECT COUNT(*) FROM WINTABLE WHERE CID = ? AND PID = ? AND POS = ?",array($req['cid'],$place['ID'],$position['ID']))->fetchArray()[0];
        if ($completed == $position['COUNT'])
            continue;

        // collect aithseis
        $apps = array();
        $app_query = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? AND PID = ? AND POS = ? ORDER BY FORCERESULT DESC",array($req['cid'],$place['ID'],$position['ID']));
        while($app = $app_query->fetchArray())
        {
            if ($app['INACTIVE'] == 1)
                continue;

            // Excluded users?
            if (in_array($app['UID'],$exu))
                continue;
            
            $pref = AppPreference($app['ID']);
            if ($pref == 0)
                continue; // duh

            if ($pref < $checking_prefefence && $app['FORCERESULT'] == 0)
                continue;

            if ($app['FORCERESULT'] == -1)
                continue;


            if ($app['FORCERESULT'] == 1)
            {
                // Run now
                QQ("INSERT INTO WINTABLE (CID,PID,POS,UID,AID) VALUES(?,?,?,?,?)",array(
                    $req['cid'],$place['ID'],$position['ID'],$app['UID'],$app['ID']
                ));
                $changed++;
                $exu[] = $app['UID'];
                $apps = array();
                break;
            }

            $apps [] = array("uid" => $app['UID'],"app" => $app,"appid" => $app['ID'],"pref" => $pref,"score" => ScoreForAitisi($app['ID']));            
        }

          // sort by moria
          if (count($apps) == 0) continue;
          usort($apps, "moria_sort");
          $app = $apps[0];
          if ($app['pref'] == $checking_prefefence)
          {
            // Do it!
            QQ("INSERT INTO WINTABLE (CID,PID,POS,UID,AID) VALUES(?,?,?,?,?)",array(
                $req['cid'],$place['ID'],$position['ID'],$app['uid'],$app['appid']
            ));
            $changed++;
            $exu[] = $app['uid'];
        }
    }
    
}

?>
    <table class="table datatable" style="width: 100%">
    <thead>
                <th class="all">#</th>
                <th class="all">Φορέας</th>
                <th class="all">Θέση</th>
                <th class="all">Χρήστης</th>
                <th class="all">Αίτηση</th>
                <th class="all">Προτίμηση</th>
                <th class="all">Μόρια</th>
            </thead><tbody>
<?php
    $q1 = QQ("SELECT * FROM WINTABLE WHERE CID = ? ",array($req['cid']));
    while($r1 = $q1->fetchArray())
    {
        printf('<tr>');
        printf('<td>%s</td>',$r1['ID']);
        $place = Single("PLACES","ID",$r1['PID']);
        printf('<td>%s</td>',$place['DESCRIPTION']);
        $position = Single("POSITIONS","ID",$r1['POS']); 
        printf('<td>%s</td>',$position['DESCRIPTION']);
        $person = Single("USERS","ID",$r1['UID']);
        printf('<td>%s %s</td>',$person['LASTNAME'],$person['FIRSTNAME']);
        printf('<td>%s</td>',$r1['AID']);
        printf('<td>%s</td>',AppPreference($r1['AID']));
        printf('<td>%s</td>',ScoreForAitisi($r1['AID']));
        printf('</tr>');
    }
    echo '</tbody></table>';
    printf("<br><br>Εκτελέστηκαν: %s<br><br>",$changed);

    if ($changed == 0 && array_key_exists("run",$req))
        $checking_prefefence++;
    if ($wa)
    {
        printf('<button class="autobutton is-primary button" href="win.php?&cid=%s&pref=%s&run=1">Continue PREF = %s</button> ',$req['cid'],$checking_prefefence,$checking_prefefence);
        printf('<button class="sureautobutton is-danger button" href="win.php?&cid=%s&reset=1">Reset</button>',$req['cid']);
    }

?>
