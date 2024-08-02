<?php


//print('<xmp>');print_r($_SERVER); die;

$dbxx = 'mpdtest.db';
//$dbxx = 'sylviamichael.hopto.org:7015';
$required_check_level = 2;
$test_users = 1;
$psd_login = 'https://www.msa-apps.com/slogin.php';
$taxis_login = 'https://www.msa-apps.com/taxis.php';

$pageroot = 'https://www.msa-apps.com/mpd';

if ($_SERVER['SERVER_NAME'] == "musicschools.minedu.gov.gr")
{
    $pageroot = 'https://musicschools.minedu.gov.gr/mpd';
    $dbxx = 'mpdlive.db';
    $test_users = 0;
    $psd_login = 'https://musicschools.minedu.gov.gr/slogin.php';
    $taxis_login = 'https://musicschools.minedu.gov.gr/taxis2.php';
}


// Comment check 14 in WebAuthn for biometric login to work!

/* If MySQL

mysql.exe -P <port> -u root
CREATE USER 'umpd' IDENTIFIED BY 'e4ea15be-4dea-7754-bdde-c305a932bfa1';
GRANT ALL PRIVILEGES ON *.* TO 'umpd' WITH GRANT OPTION;
FLUSH PRIVILEGES;
CREATE DATABASE mpd;
ALTER DATABASE mpd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON * . * TO 'umpd';

*/