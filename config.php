<?php

$dbxx = 'mpd.db';
//$dbxx = 'sylviamichael.hopto.org:7015';
$pageroot = 'https://www.msa-apps.com/mpd';
$required_check_level = 2;
$test_users = 1;

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