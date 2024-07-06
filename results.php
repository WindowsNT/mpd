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


//print_r($req); die;
$uid = $ur['ID'];
$ra = HasContestAccess($req['cid'],$uid,0);    
$wa = HasContestAccess($req['cid'],$uid,1);    


printf('<button href="contest.php?" class="autobutton button  is-danger">Πίσω</button> ');
WinTable($req['cid']);

$contestrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($req['cid']))->fetchArray();
if (!$contestrow)
{
    redirect("index.php");
    die;
}



?>
<table class="table datatable">
<thead>
    <th>#</th>
    <th>Φορέας</th>
    <th>Θέσεις</th>
</thead>
<tbody>
<?php

$q1 = QQ("SELECT * FROM PLACES WHERE CID = ?",array($req['cid']));
while($r1 = $q1->fetchArray())
{
    printf('<tr>');

    printf('<td>%s</td>',$r1['ID']);
    printf('<td>%s</td>',$r1['DESCRIPTION']);

    $q2 = QQ("SELECT * FROM POSITIONS WHERE CID = ? AND PLACEID = ?",array($req['cid'],$r1['ID']));
    printf('<td>');
    $fullfill = array();
    while($r2 = $q2->fetchArray())
    {
        ?>
        <table class="table datatable">
        <thead>
            <th>#</th>
            <th>Θέση</th>
            <th>Θέσεις</th>
            <th>Αιτήσεις</th>
        </thead>
        <tbody>
        <?php
            printf('<tr>');
            printf('<td>%s</td>',$r2['ID']);
            printf('<td>%s</td>',$r2['DESCRIPTION']);
            printf('<td>%s</td>',$r2['COUNT']);
            printf('<td>');

            // Calculate moria
            $sort_moria = array();
            $q3 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? AND POS = ?",array($req['cid'],$r2['ID']));
            while($r3 = $q3->fetchArray())
            {
                if (in_array($r3['ID'],$fullfill))
                    continue;
                $sc = ScoreForThesi($r3['UID'],$req['cid'],$r3['PID'],$r3['POS']);
                if ($sc >= 0)
                {
                    $sort_moria [$r3['UID']] = $sc;
                }
            }

            //
            $q3 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ? AND POS = ?",array($req['cid'],$r2['ID']));
            while($r3 = $q3->fetchArray())
            {
                if (in_array($r3['ID'],$fullfill))
                    continue;

                ?>
                <table class="table datatable">
                <thead>
                    <th>#</th>
                    <th>Όνομα</th>
                    <th>Αίτηση</th>
                    <th>Σκορ</th>
                </thead>
                <tbody>
                <?php

                
                $who = QQ("SELECT * FROM USERS WHERE ID = ?",array($r3['UID']))->fetchArray();
                printf('<tr>');
                printf('<td>%s</td>',$r3['ID']);
                printf('<td>%s %s</td>',$who['LASTNAME'],$who['FIRSTNAME']);
                printf('<td>%s</td>',date("d/m/Y H:i",$r3['DATE']));
                $sc = $sort_moria[$who['ID']];
                printf('<td>%s</td>',$sc);
                printf('</tr>');
            } 
            ?>
            </tbody></table>
            <?php
                
            printf('</td>');
            printf('</tr>');
        ?>
        </tbody></table>
        <?php
    }    
    printf('</td>');

    printf('</tr>');
}

?>
</tbody></table>
<?php
