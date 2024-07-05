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

$rolerow = QQ("SELECT * FROM ROLES WHERE ID = ?",array($req['t']))->fetchArray();
if ($rolerow['UID'] != $ur['ID'])
{
    redirect("index.php");
    die;
}

$cid = $req['cid'];
$cidrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($req['cid']))->fetchArray();
if (!$cidrow)
{
    redirect("index.php");
    die;
}

// cid contest,  pid place id or 0
$pid = $req['pid'];
$pidrow = QQ("SELECT * FROM PLACES WHERE ID = ?",array($req['pid']))->fetchArray();
$if0 = 2;



printf('<button href="contest.php?t=%s" class="autobutton button  is-danger">Πίσω</button> ',$req['t']);
    EnsureProsonLoaded();
    $q1 = QQ("SELECT * FROM REQUIREMENTS WHERE CID = ? AND POSID = ? AND IFPOS0TYPE = 2",array($cidrow['ID'],$pid));
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
    while($r1 = $q1->fetchArray())
    {
    }
?>
</tbody></table>
<?php
// always pid=0 pass because we set by name
printf('<a class="autobutton is-primary button" href="prosonta.php?t=%s&cid=%s&pid=0&if0=2&name=%s">Προσθήκη</a>',$rolerow['ID'],$cid,$pid == 0 ? "Προσόντα Διαγωνισμού {$cidrow['DESCRIPTION']}" : "Προσόντα Φορέα {$pidrow['DESCRIPTION']}");

