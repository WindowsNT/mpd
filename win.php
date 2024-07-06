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





if (!HasContestAccess($req['cid'],$ur['ID'],1))
    die;

if (array_key_exists("reset",$req))
{
    QQ("DELETE FROM WINTABLE WHERE CID = ?",array($req['cid']));
    redirect(sprintf("contest.php",$req['cid']));
    die;
}
$cr = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($req['cid']))->fetchArray();

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
    $position_query = QQ("SELECT * FROM POSITIONS WHERE CID = ? AND PLACEID = ?",array($req['cid'],$place['ID']));
    while($position = $position_query->fetchArray())
    {
        // completed?
        $completed = QQ("SELECT * FROM WINTABLE WHERE CID = ? AND PID = ? AND POS = ?",array($req['cid'],$place['ID'],$position['ID']))->fetchArray();
        if ($completed)
            continue;

        // collect aithseis
        $apps = array();
        $app_query = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? AND PID = ? AND POS = ?",array($req['cid'],$place['ID'],$position['ID']));
        while($app = $app_query->fetchArray())
        {
            // Excluded users?
            if (in_array($app['UID'],$exu))
                continue;
            
            $pref = AppPreference($app['ID']);
            if ($pref == 0)
                continue; // duh

            if ($pref < $checking_prefefence)
                continue;

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
          }
    }
    
}

?>
    <table class="table datatable" style="width: 100%">
    <thead>
                <th>#</th>
                <th>Φορέας</th>
                <th>Θέση</th>
                <th>Χρήστης</th>
                <th>Αίτηση</th>
                <th>Προτίμηση</th>
                <th>Μόρια</th>
            </thead><tbody>
<?php
    $q1 = QQ("SELECT * FROM WINTABLE WHERE CID = ? ",array($req['cid']));
    while($r1 = $q1->fetchArray())
    {
        printf('<tr>');
        printf('<td>%s</td>',$r1['ID']);
        $place = QQ("SELECT * FROM PLACES WHERE ID = ?",array($r1['PID']))->fetchArray();
        printf('<td>%s</td>',$place['DESCRIPTION']);
        $position = QQ("SELECT * FROM POSITIONS WHERE ID = ?",array($r1['POS']))->fetchArray();
        printf('<td>%s</td>',$position['DESCRIPTION']);
        $person = QQ("SELECT * FROM USERS WHERE ID = ?",array($r1['UID']))->fetchArray();
        printf('<td>%s %s</td>',$person['LASTNAME'],$person['FIRSTNAME']);
        printf('<td>%s</td>',$r1['AID']);
        printf('<td>%s</td>',AppPreference($r1['AID']));
        printf('<td>%s</td>',ScoreForAitisi($r1['AID']));
        printf('</tr>');
    }
    echo '</tbody></table>';
    printf("Εκτελέστηκαν: %s<br>",$changed);

    if ($changed == 0)
        $checking_prefefence++;
    printf('<button class="autobutton is-primary button" href="win.php?&cid=%s&pref=%s">Continue</button> ',$req['cid'],$checking_prefefence);
    printf('<button class="sureautobutton is-danger button" href="win.php?&cid=%s&reset=1">Reset</button>',$req['cid']);

?>
