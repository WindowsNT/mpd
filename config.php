<?php

$dbxx = 'mpd.db';
$pageroot = 'https://www.msa-apps.com/mpd';
$first_pref_score = 2;
$required_check_level = 2;


// Comment check 14 in WebAuthn for biometric login to work!

/* If MySQL

mysql.exe -P <port> -u root
CREATE USER 'umpd' IDENTIFIED BY 'e4ea15be-4dea-7754-bdde-c305a932bfa1';
GRANT ALL PRIVILEGES ON *.* TO 'umpd' WITH GRANT OPTION;
FLUSH PRIVILEGES;
CREATE DATABASE mpd;
GRANT ALL PRIVILEGES ON * . * TO 'umpd';

*/