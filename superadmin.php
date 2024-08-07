<?php

require_once "function.php";
require_once "auth.php";

if (!$afm || !$ur || !$superadmin)
    {
        redirect("index.php");
        die;
    }

if (array_key_exists("vacuum",$req))
{
    QQ("VACUUM");
    redirect("superadmin.php");
    die;
}

if (array_key_exists("users",$req))
{
    $q1 = QQ("SELECT * FROM USERS");
    require_once "output.php";
    echo '<div class="content" style="margin: 20px">';

    $s = '<table class="table datatable" style="width: 100%">';
    $s .= '<thead>
                <th class="all">#</th>
                <th class="all">ΑΦΜ</th>
                <th class="all">Επίθετο</th>
                <th class="all">Όνομα</th>
                <th class="all">Ενέργειες</th>
            </thead><tbody>';

    while($r1 = $q1->fetchArray())
    {
        $s .= sprintf('<tr>');
        $s .= sprintf('<td>%s</td>',$r1['ID']);
        $s .= sprintf('<td>%s</td>',$r1['AFM']);
        $s .= sprintf('<td>%s</td>',$r1['LASTNAME']);
        $s .= sprintf('<td>%s</td>',$r1['FIRSTNAME']);
        $s .= sprintf('<td><a href="impersonate.php?u=%s">Impersonate</td>',$r1['ID']);
        $s .= sprintf('</tr>');
    }
    $s .= '</tbody></table>';
    echo $s;
    die;
}

if (array_key_exists("backup",$req))
{
    try
    {
        $bn = tempnam(sys_get_temp_dir(), 'MyFileName').'.db';
        $bnz = $bn.".zip";
        QQ(sprintf("VACUUM main INTO '%s'",$bn));
//        $dbn = $dbxx;
 //       exec("sqlite3 $dbn \".backup $bn\" ");
  //      exec("sqlite3 $bn \"vacuum;\" ");
        $zip = new ZipArchive();
        $zip->open($bnz, ZipArchive::CREATE);
        $zip->addFile($bn,$bn);
        $zip->close();
        unlink($bn);
        $rs = file_get_contents($bnz);
        unlink($bnz);
        header("Content-type: application/zip");
        header('Content-Disposition: attachment; filename="mpd.zip"');
        echo $rs;
        die;
    }
    catch(Exception $v)
    {
    }
    redirect("superadmin.php");
    die;
}


function get_dir_size($directory){
    $size = 0;
    $files = glob($directory.'/*');
    foreach($files as $path){
        is_file($path) && $size += filesize($path);
        is_dir($path)  && $size += get_dir_size($path);
    }
    return $size;
} 

if (array_key_exists("backupfiles",$req))
{
    try
    {
        $bnz = tempnam(sys_get_temp_dir(), 'MyFileName').'.zip';
        $zip = new ZipArchive();
        $zip->open($bnz, ZipArchive::CREATE);

        $f = scandir("./files");
        foreach($f as $file)
        {
            if (is_dir(($file)))
                continue;
            $zip->addFile("./files/".$file,$file);
        }
    

        $zip->close();
        header("Content-type: application/zip");
        header('Content-Disposition: attachment; filename="mpdfiles.zip"');
        readfile($bnz);
        die;
    }
    catch(Exception $v)
    {
    }
    redirect("superadmin.php");
    die;
}

if (array_key_exists("fileclean",$req))
{
    $nones = array();
    $f = scandir("./files");
    foreach($f as $file)
    {
        if (is_dir(($file)))
            continue;
        $q1 = Single("PROSONFILE","CLSID",$file);
        if (!$q1)
            $nones [] = $files;
    }
    printf("%s files orphaned.",count($nones));
    die;
    redirect("superadmin.php");
}


if (array_key_exists("userclean",$req))
{
    $q1 = QQ("SELECT * FROM USERS");
    while($r1 = $q1->fetchArray())
    {
    }
    redirect("superadmin.php");
    die;
}


if (array_key_exists("killtype1",$req))
{
    KillUsersType1();
    redirect("superadmin.php");
    die;
}


require_once "output.php";
echo '<div class="content" style="margin: 20px">';

printf('<button class="button autobutton  is-danger block" href="index.php">Πίσω</button> ');
printf('<hr>');

printf('<a class="button  is-primary block" href="superadmin.php?users=1">Χρήστες</a> ');
printf('<a class="button  is-primary block" href="superadmin.php?backup=1">Backup</a> ');
printf('<a class="button  is-primary block" href="superadmin.php?backupfiles=1">Backup Προσόντων [%.2f MB]</a> ',get_dir_size("./files")/(1024*1024));
printf('<button class="button autobutton  is-primary block" href="superadmin.php?vacuum=1">Vacuum [%.2f MB]</button> ',filesize($dbxx)/(1024*1024));
printf('<button class="button autobutton  is-primary block" href="superadmin.php?fileclean=1">File Cleanup</button> ');
printf('<button class="button autobutton  is-primary block" href="superadmin.php?userclean=1">User Cleanup</button> ');
printf('<button class="button autobutton  is-primary block" href="superadmin.php?killtype1=1">Kill Type 1 Users</button> ');
