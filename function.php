<?php

/*
*/

ini_set('display_errors', 1); error_reporting(E_ALL);
if (session_status() == PHP_SESSION_NONE) 
    session_start();
$req = array_merge($_GET,$_POST);

require_once "config.php";

// Database functions
$lastRowID = 0;
$db = null;
$mustprepare = 0;
$mysqli = null;
$superadmin = 0;

$def_xml_proson = <<<XML
<root>
    <classes>
        <c n="1" t="Πτυχία Πανεπιστημίου" >
            <classes>
                <c n="101" t="Πτυχίο" el="6">
                    <params>
                        <p n="Ιδρυμα" id="1" t="0" />
                        <p n="Σχολή" id="2" t="0" />
                        <p n="Τμήμα" id="3" t="0" unique="1" />
                        <p n="Ειδίκευση" id="5" t="0" />
                        <p n="Βαθμός" id="4" t="2" min="5" max="10"/>
                    </params>
                </c>
                <c n="102" t="Μεταπτυχιακό" el="7">
                <params>
                        <p n="Ιδρυμα" id="1" t="0" />
                        <p n="Σχολή" id="2" t="0" />
                        <p n="Τμήμα" id="3" t="0" unique="1" />
                        <p n="Ειδίκευση" id="5" t="0" />
                        <p n="Τίτλος" id="6" t="0" unique="1" />
                        <p n="Βαθμός" id="4" t="2" min="5" max="10"/>
                        <p n="Integrated Master" id="7" t="1" min="0" max="1" />
                    </params>
                </c>
                <c n="103" t="Διδακτορικό" el="8" >
                    <params>
                        <p n="Ιδρυμα" id="1" t="0" />
                        <p n="Σχολή" id="2" t="0" />
                        <p n="Τμήμα" id="3" t="0" unique="1" />
                        <p n="Ειδίκευση" id="5" t="0" />
                        <p n="Τίτλος" id="6" t="0" unique="1" />
                        <p n="Βαθμός" id="4" t="2" min="5" max="10"/>
                    </params>
                </c>
            </classes>
        </c>
        <c n="2" t="Ξένη Γλώσσα" >
            <params>
                    <p n="Γλώσσα" id="1" unique="1" />
                    <p n="Επίπεδο" id="2" t="1" min="1" max="4" list="B1,B2,C1,C2"/>
            </params>
        </c>

        <c n="3" t="Εργασιακή Εμπειρία" >
            <classes>
                <c n="301" t="Δημόσιο" unique="1">
                    <params>
                        <p n="Μήνες" id="1" t="2" />
                    </params>
                </c>
                <c n="302" t="Ιδιωτικό" unique="1">
                    <params>
                          <p n="Μήνες" id="1" t="2" />
                    </params>
                </c>
            </classes>
        </c>

        <c n="4" t="Διπλώματα/Πτυχία Μουσικής από Ωδείο" >
            <classes>
                <c n="405" t="Πτυχίο Οργάνου">
                    <params>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Όργανο" id="3" t="0" unique="1" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="401" t="Δίπλωμα Οργάνου">
                    <params>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Όργανο" id="3" t="0" unique="1" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="408" t="Πτυχίο Ανώτερων Θεωρητικών" unique="1">
                    <params>
                        <p n="Επίπεδο" id="3" t="1" min="1" max="5" list="Πτυχίο Ωδικής,Πτυχίο Αρμονίας,Πτυχίο Αντίστιξης,Πτυχίο Φούγκας,Πτυχίο Σύνθεσης"/>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="407" t="Δίπλωμα Βυζαντινής" unique="1">
                    <params>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
            </classes>
        </c>

        <c n="5" t="Κοινωνικά κριτήρια" >
            <classes>
                <c n="501" t="Εντοπιότητα" unique="1">
                    <params>
                        <p n="Περιοχή" id="1" t="0" />
                    </params>
                </c>
                <c n="502" t="Οικογενειακή κατάσταση" unique="1">
                    <params>
                        <p n="Γάμος" id="1" t="2" min="0" max="1" />
                        <p n="Παιδιά" id="2" t="2" />
                        <p n="Αναπηρία" id="3" t="2" min="0" max="100" />
                    </params>
                </c>
            </classes>
        </c>

        <c n="6" t="Υπηρεσιακή Κατάσταση">
            <classes>
                <c n="601" t="Υπηρεσία" unique="1">
                    <params>
                        <p n="Κλάδος" id="1" t="0" />
                        <p n="Αριθμός Μητρώου" id="2" t="2" />
                        <p n="Μουσική Ειδίκευση" id="8" t="0" />
                        <p n="Εκπαιδευτική Προϋπηρεσία" id="3" t="3" />
                        <p n="Προϋπηρεσία στα Μουσικά" id="4" t="3" />
                        <p n="Περιοχή Οργανικής" id="5" t="0" />
                        <p n="Τύπος Οργανικής" id="6" t="0" />
                        <p n="Οργανική Θέση" id="7" t="0" />
                        <p n="Συνολική Προϋπηρεσία" id="9" t="3" />
                    </params>
                </c>
                <c n="602" t="Υπηρεσία Στελέχους">
                    <params>
                        <p n="Επίπεδο" id="1" t="1" min="1" max="8" list="Προϊστάμενος Τμήματος,Προϊστάμενος Διεύθυνσης,Προϊστάμενος Γενικής Διεύθυνσης,Υπηρεσιακός Γραμματέας,Γενικός Γραμματέας,Υφυπουργός,Υπουργός"/>
                        <p n="Υπουργείο" id="2" t="0" />
                        <p n="Γενική Γραμματεία" id="3" t="0" />
                        <p n="Γενική Διεύθυνση" id="4" t="0" />
                        <p n="Διεύθυνση" id="5" t="0" />
                    </params>
                </c>
            </classes>
        </c>

        <c n="7" t="ΤΠΕ" >
            <classes>
                <c n="701" t="Επιμόρφωση" unique="1">
                <params>
                        <p n="Επίπεδο" id="1" t="1" min="1" max="4" list="A,B1,B2,Επιμορφωτής B Επιπέδου"/>
                    </params>
                </c>
            </classes>
        </c>

        <c n="8" t="Επιστημονικό Έργο" >
            <classes>
                <c n="801" t="Συνέδριο" >
                    <params>
                        <p n="Τίτλος" id="1" t="0" unique="1"/>
                        <p n="Ίδρυμα" id="2" t="0"/>
                        <p n="Διεθνές" id="3" t="1" min="0" max="1" />
                        <p n="ISSN" id="4" />
                    </params>
                </c>
                <c n="802" t="Δημοσίευση" >
                    <params>
                        <p n="Τίτλος" id="1" t="0" unique="1"/>
                        <p n="Περιοδικό" id="2" t="0"/>
                        <p n="Διεθνής" id="3" t="1" min="0" max="1" />
                        <p n="ISSN" id="4" />
                    </params>
                </c>
                <c n="803" t="Βιβλίο" >
                    <params>
                        <p n="Τίτλος" id="1" t="0" unique="1"/>
                        <p n="Εκδτοικός Οίκος" id="2" t="0"/>
                        <p n="Διεθνής" id="3" t="1" min="0" max="1" />
                        <p n="ISSN" id="4" />
                    </params>
                </c>
            </classes>
        </c>

    </classes>
</root>
XML;

$xmlp = null;
$xml_proson = '';

function QQZ_SQLite($dbs,$q,$arr = array(),$stmtx = null)
{
    global $lastRowID;

	$stmt = $stmtx;
    if (!$stmt)
        $stmt = $dbs->prepare($q);
    if (!$stmt)
        return null;
    $i = 1;
    foreach($arr as $a)
    {
        $stmt->bindValue($i,$a);
        $i++;
    }
    $a = $stmt->execute();
    $lastRowID = $dbs->lastInsertRowID();
    if ($a === FALSE)
        {
            die("Database busy, please try later.");
        }
    return $a;
}

class msql_wrap
{
    public $rx;

    public function fetchArray()
    {
        if (!$this->rx)
            return null;
        if ($this->rx->num_rows == 0)
            return null;
        return $this->rx->fetch_assoc();
    }
};


function QQZ_MySQL($dbs,$q,$arr = array(),$stmt = null)
{
    global $lastRowID;
    if (!is_array($arr)) die("QQZ_MySQL passed not an array.");

    if (!$stmt)
	    $stmt = $dbs->prepare($q);
    if (!$stmt)
        return null;
    $arx = array();
    $bp = "";
    foreach($arr as $a)
        $bp .= "s";

    if (count($arr) > 0)
    {
        $arx [] = &$bp;
        foreach($arr as &$a)
             $arx [] = &$a;
        call_user_func_array (array($stmt,'bind_param'),$arx);
    }

    $stmt->execute();
    $a = $stmt->get_result();
    $lastRowID = $dbs->insert_id;
    $m = new msql_wrap;
    $m->rx = $a;

    return $m;
}


function QQZ($dbs,$q,$arr = array(),$stmt = null)
{
    global $mysqli;
    if ($mysqli)
        return QQZ_MySQL($mysqli,$q,$arr,$stmt);
    else
        return QQZ_SQLite($dbs,$q,$arr,$stmt);
}

function QQ($q,$arr = array(),$stmt = null)
{
	global $db;
    if (!is_array($arr)) die("QQ passed not an array.");
    return QQZ($db,$q,$arr,$stmt);
}


function CountDB($q,$arr)
{
	global $db;
    if (!is_array($arr)) die("QQ passed not an array.");
    global $mysqli;
    if ($mysqli)
        return QQZ($db,"SELECT COUNT(ID) FROM $q",$arr)->fetchArray()[0];
    else
        return QQZ($db,"SELECT COUNT(*) FROM $q",$arr)->fetchArray()[0];
}



define('ROLE_CHECKER', 1);
define('ROLE_CREATOR',2);
define('ROLE_UNI',3);
define('ROLE_GLOBALPROSONEDITOR',4);
define('ROLE_FOREASSETPLACES',5);
define('ROLE_ROLEEDITOR',6);
define('ROLE_SUPERADMIN',99);

function RoleToText($r)
{
    if ($r == ROLE_CHECKER) return 'Ελεγκτής Προσόντων';
    if ($r == ROLE_CREATOR) return 'Χειριστής Διαγωνισμών';
    if ($r == ROLE_UNI) return 'Ίδρυμα';
    if ($r == ROLE_GLOBALPROSONEDITOR) return 'Διορθωτής XML Προσόντων';
    if ($r == ROLE_FOREASSETPLACES) return 'Διορθωτής Κενών Φορέα';
    if ($r == ROLE_ROLEEDITOR) return 'Ελεγκτής Ρόλων';
    return '';
}

function PrepareDatabase($msql = 0)
{
    $j = 'AUTO_INCREMENT';
    global $lastRowID,$xml_proson;
    if ($msql == 0)
        $j = '';
    QQ("CREATE TABLE IF NOT EXISTS GLOBALXML (ID INTEGER PRIMARY KEY,XML TEXT)");
    QQ("INSERT INTO GLOBALXML (XML) VALUES (?)",array($xml_proson));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS USERS (ID INTEGER PRIMARY KEY %s,MAIL TEXT,AFM TEXT,LASTNAME TEXT,FIRSTNAME TEXT,CLSID TEXT)",$j));
    QQ("CREATE TABLE IF NOT EXISTS BIO_INFO (ID INTEGER PRIMARY KEY,UID INTEGER,T1 TEXT,T2 TEXT,FOREIGN KEY (UID) REFERENCES USERS(ID))");
    QQ(sprintf("CREATE TABLE IF NOT EXISTS ROLES (ID INTEGER PRIMARY KEY %s,UID INTEGER,ROLE INTEGER,ROLEPARAMS TEXT,FOREIGN KEY (UID) REFERENCES USERS(ID))",$j));

    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSON (ID INTEGER PRIMARY KEY %s,UID INTEGER,CLSID TEXT,DESCRIPTION TEXT,CLASSID INTEGER,STARTDATE INTEGER,ENDDATE INTEGER,STATE INTEGER,FAILREASON TEXT,FOREIGN KEY (UID) REFERENCES USERS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSONFILE (ID INTEGER PRIMARY KEY %s,UID INTEGER,PID INTEGER,CLSID TEXT,DESCRIPTION TEXT,FNAME TEXT,TYPE TEXT,FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (PID) REFERENCES PROSON(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSONPAR (ID INTEGER PRIMARY KEY %s,PID INTEGER,PIDX INTEGER,PVALUE TEXT,FOREIGN KEY (PID) REFERENCES PROSON(ID))",$j));
//      QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSONEV (ID INTEGER PRIMARY KEY %s,UID INTEGER,EVUID INTEGER,RESULT INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (PID) REFERENCES PROSON(ID))",$j));

    QQ(sprintf("CREATE TABLE IF NOT EXISTS CONTESTS (ID INTEGER PRIMARY KEY %s,UID INTEGER,MINISTRY TEXT,CATEGORY TEXT,DESCRIPTION TEXT,STARTDATE INTEGER,ENDDATE INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PLACES (ID INTEGER PRIMARY KEY %s,CID INTEGER,PARENTPLACEID INTEGER,DESCRIPTION TEXT,FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PARENTPLACEID) REFERENCES PLACES(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS POSITIONS (ID INTEGER PRIMARY KEY %s,CID INTEGER,PLACEID INTEGER,DESCRIPTION TEXT,COUNT INTEGER,FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PLACEID) REFERENCES PLACES(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS POSITIONGROUPS (ID INTEGER PRIMARY KEY %s,CID INTEGER,GROUPLIST TEXT,FOREIGN KEY (CID) REFERENCES CONTESTS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS APPLICATIONS (ID INTEGER PRIMARY KEY %s,UID INTEGER,CID INTEGER,PID INTEGER,POS INTEGER,DATE INTEGER,FORCEDMORIA TEXT,INACTIVE INTEGER,FORCERESULT INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PID) REFERENCES PLACES(ID),FOREIGN KEY (POS) REFERENCES POSITIONS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS WINTABLE (ID INTEGER PRIMARY KEY %s,CID INTEGER,PID INTEGER,POS INTEGER,UID INTEGER,AID INTEGER,EXTRA TEXT,FOREIGN KEY (AID) REFERENCES APPLICATIONS(ID),FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PID) REFERENCES PLACES(ID),FOREIGN KEY (POS) REFERENCES POSITIONS(ID))",$j));

    QQ(sprintf("CREATE TABLE IF NOT EXISTS REQS2 (ID INTEGER PRIMARY KEY %s,CID INTEGER,PLACEID INTEGER,POSID INTEGER,FORTHESI INTEGER,NAME TEXT,PROSONTYPE INTEGER,SCORE TEXT,ANDLINK INTEGER,ORLINK INTEGER,NOTLINK INTEGER,REGEXRESTRICTIONS TEXT,MINX INTEGER,MAXX INTEGER,
        FOREIGN KEY (PLACEID) REFERENCES PLACES(ID),
        FOREIGN KEY (CID) REFERENCES CONTESTS(ID),
        FOREIGN KEY (POSID) REFERENCES POSITIONS(ID),
        FOREIGN KEY (ORLINK) REFERENCES REQS2(ID),
        FOREIGN KEY (NOTLINK) REFERENCES REQS2(ID))",$j));

    QQ("CREATE TABLE IF NOT EXISTS PUSHING (ID INTEGER PRIMARY KEY $j,CLSID TEXT,STR TEXT,ENDPOINT TEXT)");


    // Test set
    QQ("INSERT INTO USERS (MAIL,AFM,LASTNAME,FIRSTNAME,CLSID) VALUES ('u1@example.org','1001001001','ΠΑΠΑΔΟΠΟΥΛΟΣ','ΝΙΚΟΣ',?)",array(guidv4()));
    $u1Id = $lastRowID;
    QQ("INSERT INTO USERS (MAIL,AFM,LASTNAME,FIRSTNAME,CLSID) VALUES ('u2@example.org','1001001002','ΓΕΩΡΓΙΟΥ','ΒΑΣΙΛΕΙΟΣ',?)",array(guidv4()));
    $u2Id = $lastRowID;
    QQ("INSERT INTO USERS (MAIL,AFM,LASTNAME,FIRSTNAME,CLSID) VALUES ('u3@example.org','1001001003','ΝΙΚΟΛΑΟΥ','ΠΑΝΑΓΙΩΤΗΣ',?)",array(guidv4()));
    $u3Id = $lastRowID;
    QQ("INSERT INTO USERS (MAIL,AFM,LASTNAME,FIRSTNAME,CLSID) VALUES ('u4@example.org','1001001005','ΜΑΡΗΣ','ΦΩΤΗΣ',?)",array(guidv4()));
    $u4Id = $lastRowID;
    QQ("INSERT INTO USERS (MAIL,AFM,LASTNAME,FIRSTNAME,CLSID) VALUES ('u5@example.org','1001001006','ΜΑΡΙΝΟΥ','ΕΥΤΥΧΙΑ',?)",array(guidv4()));
    $u5Id = $lastRowID;
    QQ("INSERT INTO ROLES (UID,ROLE) VALUES($u2Id,1)");
    $r1id = $lastRowID;
    QQ("INSERT INTO ROLES (UID,ROLE) VALUES($u3Id,ROLE_CREATOR)");
    QQ("INSERT INTO ROLES (UID,ROLE) VALUES($u4Id,ROLE_GLOBALPROSONEDITOR)");
    QQ("INSERT INTO USERS (MAIL,AFM,LASTNAME,FIRSTNAME,CLSID) VALUES ('u4@example.org','1001001004','ΠΑΠΑΖΟΓΛΟΥ','ΜΙΧΑΗΛ',?)",array(guidv4()));
    $u4Id = $lastRowID;

    QQ("INSERT INTO USERS (MAIL,AFM,LASTNAME,FIRSTNAME,CLSID) VALUES ('u8@example.org','1001001008','ΦΟΥΡΗΣ','ΑΓΑΜΕΜΝΩΝ',?)",array(guidv4()));

    $rparam1 = '<root>
    <classes>
        <c n="1" t="Πτυχία Πανεπιστημίου" >
            <classes>
                <c n="101" t="Πτυχίο" el="6">
                    <params>
                        <p n="Ιδρυμα" id="1" t="0" v="ΕΚΠΑ"/>
                        <p n="Σχολή" id="2" t="0" v="ΦΙΛΟΣΟΦΙΚΗ"/>
                        <p n="Τμήμα" id="3" t="0" v="ΜΟΥΣΙΚΩΝ ΣΠΟΥΔΩΝ"/>
                    </params>
                </c>
            </classes>
        </c>
    </classes>
</root>';
    QQ("INSERT INTO ROLES (UID,ROLE) VALUES(?,?,?)",array($u4Id,ROLE_UNI));
    $r4id = $lastRowID;



}

function PrepareDatabaseMySQL()
{
    PrepareDatabase(1);
}


// Sqlite
if (strstr($dbxx,':'))
{
    // MySQL
    $mysqli = new \mysqli($dbxx,"root","root","db1");
    PrepareDatabaseMySQL();
}
else
{
    if (!file_exists($dbxx)) 
        $mustprepare = 1;
    $db = new \SQLite3($dbxx);
    $db->busyTimeout(10000);
    $db->exec('PRAGMA journal_mode = wal;');
    if ($mustprepare)
        PrepareDatabase();
}



$xml_proson_row = Single("GLOBALXML","ID",1);
if ($xml_proson_row)
 $xml_proson = $xml_proson_row['XML'];
else
 $xml_proson = $def_xml_proson;


function redirect($filename,$u = 0) {
    if (!headers_sent() && $u == 0)
        header('Location: '.$filename);
    else {
        if ($u == 0)
        {
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$filename.'";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="'.$u.';url='.$filename.'" />';
            echo '</noscript>';
        }
        else
            echo '<meta http-equiv="refresh" content="'.$u.';url='.$filename.'" />';
    }
}

function guidv4()
{
    if (function_exists('com_create_guid') === true)
        return trim(com_create_guid(), '{}');

    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}


function  EnsureProsonLoaded()
{
    global $xmlp,$xml_proson;
    if (!$xmlp)
        $xmlp = simplexml_load_string($xml_proson);
}


function Single($table,$column,$id)
{
    return QQ(sprintf("SELECT * FROM %s WHERE %s = ?",$table,$column),array($id))->fetchArray();
}

// Access functions
function HasPlaceAccessForKena($pid,$uid)
{
    global $superadmin;
    if ($superadmin)
        return true;
    $j = QQ("SELECT * FROM ROLES WHERE UID = ? AND ROLE = ?",array($uid,ROLE_FOREASSETPLACES));
    while($r = $j->fetchArray())
    {
        $params = json_decode($r['ROLEPARAMS'],true);
        $places = $params['places'];
        if (in_array($pid,$places))
            return true;

    }
    return false;
}


function eval2($e,$uid = 0,$cid = 0,$placeid = 0,$posid = 0)
{
    try {
        return eval($e);
    } 
    catch (ParseError $t) 
    {
        echo '<i>'.$t->getMessage().'</i><br>';
        return false;
    }
}


function HasContestAccess($cid,$uid,$wr = 0) 
{
    global $superadmin; if ($superadmin) return true;
    $crow = Single("CONTESTS","ID",$cid);
    if (!$crow)
        return false;
    $j = QQ("SELECT * FROM ROLES WHERE UID = ? AND ROLE = ?",array($uid,ROLE_CREATOR));
    while($r = $j->fetchArray())
    {
        $params = json_decode($r['ROLEPARAMS'],true);
        $contests = $params['contests'];
        if (in_array(0,$contests))
            return true;
        if (in_array($cid,$contests))
            return true;
        }

    if ($wr == 0) 
    {
        $t = time();
        if ($t >= $crow['STARTDATE'] && $t <= $crow['ENDDATE'])           
            return true;
        return false;
    }

    return false;
}

function FromActualTo360($v)
{
    $y = date("Y",$v);
    $m = date("m",$v);
    $d = date("d",$v);

    while($d >= 30)
    {
        $m++;
        $d -= 30;
    }
    while($m >= 12)
        {
            $m -= 12;
            $y++;
        }
    return ($y - 1970)*360 + $m*30 + $d;
}

function From360ToActual($v)
{
    $years = (int)($v / 360);
    $v %= 360;
    $months = (int)($v / 30);
    $v %= 30;
    $days = $v;
    return mktime(0,0,0,$months,$days,$years + 1970);
}

function HasProsonAccess($pid,$uid,$wr = 0)
{
    global $superadmin; if ($superadmin) return true;
    $pr = Single("PROSON","ID",$pid);
    if (!$pr)
        return false;
    if ($pr['UID'] == $uid)
        return true;
    if ($wr == 1)
        return false;

    $belongsto = Single("USERS","ID",$pr['UID']);
    if (!$belongsto)
        return false;

    // Check if it is a checker
    $roles = QQ("SELECT * FROM ROLES WHERE UID = ?",array($uid));
    while($r1 = $roles->fetchArray())
    {
        if ($r1['ROLE'] != ROLE_CHECKER)
            continue;
        $params = json_decode($r1['ROLEPARAMS'],true);
        $afms = $params['afms'];
        if (in_array($belongsto['AFM'],$afms))
            return true;
    }

    // Check if it is a university
    $roles = QQ("SELECT * FROM ROLES WHERE UID = ?",array($uid));
    while($r1 = $roles->fetchArray())
    {
        if ($r1['ROLE'] != ROLE_UNI)
            continue;
        $params = json_decode($r1['ROLEPARAMS'],true);
        $xmlx = base64_decode($params['restriction']);

        $xml2 = simplexml_load_string($xmlx);
        $rootc = RootForClassId($xml2->classes,$pr['CLASSID']);

        if (!$rootc)
            continue;
        if (!$rootc->attributes())
            continue;

        if ($rootc->attributes()['n'] != $pr['CLASSID'])
            continue;

        $proson_par = QQ("SELECT * FROM PROSONPAR WHERE PID = ?",array($pid));
        $proson_parameters = array();
        while($prp = $proson_par->fetchArray())
           $proson_parameters[] = $prp;

        $fail = 0;
        foreach($proson_parameters as $prp)
        {
            $pidx = $prp['PIDX'];
            $pv = $prp['PVALUE'];

            foreach($rootc->params->children() as $ch)
            {
                if (!$ch->attributes())
                    continue;
                if ($ch->attributes()['id'] == $pidx)
                {
                    $fail = 1;
                    if ($ch->attributes()['v'] == $pv)
                    {
                        $fail = 0;
                        break;
                    }
                }
            }

            if ($fail)
                break;
        }
        
        if (!$fail)
            return true;
    }


    return false;
}

function HasFileAccess($fid,$uid,$wr = 0)
{
    $pr = Single("PROSONFILE","ID",$fid);
    if (!$pr)
        return false;
    return HasProsonAccess($pr['PID'],$uid,$wr);
}

function GetAllClassesInXML($x,&$a)
{
    if (!$x)
        return;
    foreach($x->c as $c)
    {
        $attr = $c->attributes();
        $n = (string)$attr['n'];
        $a[] = $n;
        if ($c->classes)
            GetAllClassesInXML($c->classes,$a);        
    }    
}

function RootForClassId($x,$cid,&$pars = array())
{
    if (!$x)
        return 0;

    if ($cid == 0)
        return $x;
    
    foreach($x->c as $c)
    {
        $attr = $c->attributes();
        if ($attr['n'] == $cid)
        {
            if ($c->classes)
                return $c->classes;
            return $c;
        }
        $rv = RootForClassId($c->classes,$cid,$pars);
        if ($rv)
            {
                $pars[] = $c;
                return $rv;
            }
    }
    return null;
}

function deepx($de)
{
    $s = '';
    while($de > 0)
    {
        $de--;
        $s .= '&nbsp';
    }
    return $s;
}

function PrintForeisContest($cid,$rootfor = 0,$deep = 0)
{
    $s = '';
    $q1 = QQ("SELECT * FROM PLACES WHERE CID = ? AND PARENTPLACEID = ?",array($cid,$rootfor));
    while($r1 = $q1->fetchArray())
    {
        $s .= deepx($deep);
        $s .= sprintf('<b>%s</b><br> <button class="is-small is-info autobutton button block" href="contest.php?editplace=1&pid=%s">Επεξεργασία</button> <button class="button is-small is-link autobutton block" href="positions.php?cid=%s&pid=%s">Θέσεις</button> <button class="button autobutton is-small  block is-warning" href="contest.php?addplace=1&cid=%s&par=%s">Προσθήκη κάτω</button> <button class="autobutton block button is-small is-link" href="prosonta3.php?cid=%s&placeid=%s">Προσόντα Φορεα</button> <button class="block sureautobutton is-small is-danger button  block" href="contest.php?deleteplace=1&pid=%s">Διαγραφή</button><br>',$r1['DESCRIPTION'],$r1['ID'],$cid,$r1['ID'],$cid,$r1['ID'],$cid,$r1['ID'],$r1['ID']);
        $s .= deepx($deep);
        $s .= PrintForeisContest($cid,$r1['ID'],$deep + 1);
    }
    if ($deep == 0)
        $s .= sprintf('<hr><button class="button is-primary is-small autobutton" href="contest.php?addplace=1&cid=%s&par=%s">Προσθήκη</button><br>',$cid,$rootfor);

    return $s;
}
function PrintContests($uid)
{
    global $superadmin;
    $s = '<table class="table datatable" style="width: 100%">';
    $s .= '<thead>
                <th class="all">#</th>
                <th class="all">Υπουργείο</th>
                <th class="all">Κατηγορία</th>
                <th class="all">Περιγραφή</th>
                <th class="all">Ημερομηνίες</th>
                <th class="all">Φορείς</th>
                <th class="all">Ενέργειες</th>
            </thead><tbody>';

    $q1 = QQ("SELECT * FROM CONTESTS WHERE UID = ?",array($uid));
    if ($superadmin)
        $q1 = QQ("SELECT * FROM CONTESTS");
    while($r1 = $q1->fetchArray())
    {
        $ra = HasContestAccess($r1['ID'],$uid,0);
        $wa = HasContestAccess($r1['ID'],$uid,1);
        if (!$ra)
            continue;
        $s .= sprintf('<tr>');
        $s .= sprintf('<td>%s</td>',$r1['ID']);
        $s .= sprintf('<td>%s</td>',$r1['MINISTRY']);
        $s .= sprintf('<td>%s</td>',$r1['CATEGORY']);
        $s .= sprintf('<td>%s</td>',$r1['DESCRIPTION']);
        $s .= sprintf('<td>%s &mdash; %s</td>',date("d/m/Y",$r1['STARTDATE']),date("d/m/Y",$r1['ENDDATE']));
        $s .= sprintf('<td>');
        $s .= PrintForeisContest($r1['ID']);
        $s .= sprintf('</td>');
        // printf('',$req['t']);
        $s .= '<td>';
        if ($wa == 1)
        {
            $s .= sprintf('<button class="is-small is-info autobutton button block" href="contest.php?c=%s">Επεξεργασία</button> ',$r1['ID']);
            $s .= sprintf('<button class="autobutton button is-small is-link block" href="positiongroups.php?cid=%s">Προσόντα Κοινών Θέσεων</button> ',$r1['ID']);
            $s .= sprintf('<button class="autobutton button is-small is-primary block" href="listapps.php?cid=%s">Λίστα Αιτήσεων</button> ',$r1['ID']);
            $s .= sprintf('<button class="autobutton button is-small is-link block" href="prosonta3.php?cid=%s&placeid=0">Προσόντα Διαγωνισμού</button> ',$r1['ID']);
            if ($r1['ID'] != 1)
            $s .= sprintf('<div class="dropdown is-hoverable">
  <div class="dropdown-trigger">
    <button class="button is-secondary is-small block" aria-haspopup="true" aria-controls="dropdown-menu4">
      <span>ΟΠΣΥΔ</span>
    </button>
  </div>
  <div class="dropdown-menu" id="dropdown-menu4" role="menu">
    <div class="dropdown-content">
      <div class="dropdown-item">
            <button class="sureautobutton button is-small is-danger" href="opsyd.php?cid=%s&f=1">Εισαγωγή Κενών από CSV ΟΠΣΥΔ</button>
      </div>
      <div class="dropdown-item">
            <button class="sureautobutton button is-small is-danger"  href="opsyd.php?cid=%s&f=2&from=1">Αντιγραφή Προσόντων Θέσεως</button>
      </div>
      <div class="dropdown-item">
            <button class="sureautobutton button is-small is-danger"  href="opsyd.php?cid=%s&f=3&from=1">Αντιγραφή Προσόντων Διαγωνισμού</button>
      </div>
      <div class="dropdown-item">
            <button class="sureautobutton button is-small is-danger" href="opsyd.php?cid=%s&f=4&from=1">Αντιγραφή Προσόντων Φορέων</button>
      </div>
    </div>
  </div>
</div> ',$r1['ID'],$r1['ID'],$r1['ID'],$r1['ID']);
            $s .= sprintf('<button class="autobutton button is-small is-success block" href="win.php?cid=%s">Αποτελέσματα</button> ',$r1['ID']);
           $s .= sprintf('<button class="sureautobutton button is-small is-danger block" href="kill.php?cid=%s">Διαγραφή</button></td>',$r1['ID']);
        }
        $s .= sprintf('</tr>');
    }           

    return $s;
}

function PrintProsonta($uid,$veruid = 0,$rolerow = null,$level = 1)
{
    global $xmlp,$required_check_level;
    EnsureProsonLoaded();


    $s = '<table class="table datatable" style="width: 100%">';
    $s .= '<thead>
                <th class="all">#</th>
                <th class="all">Περιγραφή</th>
                <th class="all">Κατηγορία</th>
                <th class="all">Ισχύς</th>
                <th class="all">Παράμετροι</th>
                <th class="all">Αρχεία</th>
                <th class="all">Κατάσταση</th>
                <th class="all">Εντολές</th>
            </thead><tbody>';

            
    $q1 = QQ("SELECT * FROM PROSON WHERE PROSON.UID = ? ",array($uid));
    if ($rolerow)
        $q1 = QQ("SELECT * FROM PROSON WHERE PROSON.UID = ? ORDER BY STATE",array($uid));

    while($r1 = $q1->fetchArray())
    {
        $s .= sprintf('<tr>');
        $s .= sprintf('<td>%s</td>',$r1['ID']);
        $s .= sprintf('<td>%s</td>',$r1['DESCRIPTION']);

        $parnames = array();
        $partypes = array();
        $parlist = array();
        $pars = array();
        $croot = RootForClassId($xmlp->classes,$r1['CLASSID'],$pars);
        $attr = $croot->attributes();
        $s .= sprintf('<td>');
        foreach($pars as $par)
        {
            $attrp = $par->attributes();
            $s .= sprintf('%s<br>',$attrp['t']);
        }
        $s .= sprintf('%s',$attr['t']);
        $s .= sprintf('</td>',$attr['t']);

        $params_root = $croot->params;
        if ($params_root)
        foreach($params_root->p as $param)
        {
            $pa = $param->attributes();                              
            $parnames[(int)$pa['id']] = $pa['n'];                       
            $partypes[(int)$pa['id']] = $pa['t'];                       
            $parlist[(int)$pa['id']] = (string)$pa['list'];                       
        }    

        $s .= sprintf('<td>%s<br>%s</td>',date("d/m/Y",$r1['STARTDATE']),$r1['ENDDATE'] ? date("d/m/Y",$r1['ENDDATE']) : '∞');

        // Parameters
        $s .= sprintf('<td>');
        $q2 = QQ("SELECT * FROM PROSONPAR WHERE PID = ? ",array($r1['ID']));
        while($r2 = $q2->fetchArray())
        {
            $vvv = $r2['PVALUE'];
            if ($partypes[$r2['PIDX']] == 3)
            {
                $startwhen = $r1['STARTDATE'];
                $now = time();
                if ($now > $startwhen)
                {
                    $a1 = From360ToActual($vvv);
                    $a1 += ($now - $startwhen);
                    $vvv = FromActualTo360($a1);
                }
            }
            // Check list
            $vvv2 = '';
            if ($parlist[(int)$r2['PIDX']])
            {
                $ch = explode(",",$parlist[(int)$r2['PIDX']]);
                $vvv2 = ' - '.$ch[((int)$vvv) - 1];

            }
            
            $s .= sprintf('<b>%s</b><br>%s %s<br>',$parnames[$r2['PIDX']],$vvv,$vvv2);
        }
        $s .= sprintf('</td>');


        $s .= sprintf('<td>');
        $q3 = QQ("SELECT * FROM PROSONFILE WHERE PID = ? ",array($r1['ID']));
        while($r3 = $q3->fetchArray())
        {
            $s .= sprintf('<b><a href="viewfile.php?f=%s" target="_blank">%s</a><br>',$r3['ID'],$r3['DESCRIPTION']);
        }

        if ($veruid == 0)
            {
                if ($r1['STATE'] > 0)
                    $s .= sprintf('<br><br><button q="Αν αλλάξετε το προσόν θα ακυρωθεί η έγκρισή του και θα πρέπει να το εγκρίνουν ξανά! Συνέχεια;" class="sureautobutton button is-small is-link" href="files.php?e=%s&f=0">Διαχείριση Αρχείων</button>',$r1['ID']);
                else
                    $s .= sprintf('<br><br><button class="autobutton button is-small is-link" href="files.php?e=%s&f=0">Διαχείριση Αρχείων</button>',$r1['ID']);
            }
        $s .= sprintf('</td>');
        $s .= sprintf('<td>');
        if ($r1['STATE'] == 0) $s .= 'Αναμονή';
        if ($r1['STATE'] < 0) $s .= sprintf('Απόρριψη<br>%s',$r1['FAILREASON']);
        if ($r1['STATE'] >= 1) $s .= sprintf('Έγκριση<br>Επίπεδο %s %s',$r1['STATE'],$r1['STATE'] < $required_check_level ? "<br><br>[Απαιτείται επίπεδο έγκρισης $required_check_level]" : '');
        $s .= sprintf('</td>');
        $s .= sprintf('<td>');
        if ($veruid)
        {
            $CanModify = 0;
            if ($r1['STATE'] == ($level - 1))
               $CanModify = 1;
            if ($CanModify) 
            {
                $s .= sprintf('<button class="block sureautobutton button is-small is-success" href="check.php?t=%s&approve=%s">Έγκριση</button> ',$rolerow['ID'],$r1['ID']);
                $s .= sprintf('<button class="block button is-small is-danger" onclick="rejectproson(%s,%s);">Απόρριψη</button>',$rolerow['ID'],$r1['ID']);
            }
        }
        else
        {
            if ($r1['STATE'] > 0)
                $s .= sprintf('<button q="Αν αλλάξετε το προσόν θα ακυρωθεί η έγκρισή του και θα πρέπει να το εγκρίνουν ξανά! Συνέχεια;" class="sureautobutton button is-small is-link block" href="proson.php?e=%s">Διόρθωση</button> <button class="sureautobutton button is-small is-danger" href="proson.php?delete=%s">Διαγραφή</button>',$r1['ID'],$r1['ID']);
            else
                $s .= sprintf('<button class="autobutton button is-small is-link block" href="proson.php?e=%s">Διόρθωση</button> <button class="sureautobutton button is-small is-danger" href="proson.php?delete=%s">Διαγραφή</button>',$r1['ID'],$r1['ID']);
        }
        $s .= sprintf('</td>');



        $s .= sprintf('</tr>');
    }

    $s .= '</tbody></table>';
    return $s;
}

function ApplicationProtocol($row)
{
    return  sprintf("%s%04s",date("YmdHis",$row['DATE']),$row['ID']);
}
function DeleteProsonFile($id,$uid = 0)
{
    if ($uid)
        $e = QQ("SELECT * FROM PROSONFILE WHERE ID = ? AND UID = ?",array($id,$uid))->fetchArray();
    else
        $e = Single("PROSONFILE","ID",$id);
    if (!$e)
        return false;

    unlink(sprintf("./files/%s",$e['CLSID']));
    QQ("DELETE FROM PROSONFILE WHERE ID = ?",array($id));
    QQ("UPDATE PROSON SET STATE = 0 WHERE ID = ? AND STATE != 0",array($e['PID']));
    return true;
}

function DeleteProson($id,$uid = 0)
{
    if ($uid)
        $e = QQ("SELECT * FROM PROSON WHERE ID = ? AND UID = ?",array($id,$uid))->fetchArray();
    else
        $e = Single("PROSON","ID",$id);
    if (!$e)
        return;

    $q1 = QQ("SELECT * FROM PROSONFILE WHERE PID = ?",array($id)); 
    while($r1 = $q1->fetchArray())
    {
        DeleteProsonFile($r1['ID'],$uid);
    }

    QQ("DELETE FROM PROSONPAR WHERE PID = ?",array($id));
    QQ("DELETE FROM PROSON WHERE ID = ?",array($id));
}

function jpegrecompress($d)
{
    $f1 = tempnam(sys_get_temp_dir(), rand());
    if (!file_put_contents($f1,$d))
        return $d;
    $f2 = tempnam(sys_get_temp_dir(), rand());
    unlink($f2);
    exec("jpeg-recompress $f1 $f2");
    $d2 = file_get_contents($f2);
    unlink($f1);
    unlink($f2);
    if (strlen($d2) == 0 || strlen($d2) > strlen($d))
        return $d;
    return $d2;
}


$rejr = '';

function HasProson($uid,$reqid,$deep = 0,&$reason = '')
{
    global $required_check_level;
    $reqrow = Single("REQS2","ID",$reqid);
    if (!$reqrow)
        return -1;


    $rex = explode("###",$reqrow['REGEXRESTRICTIONS'] ? $reqrow['REGEXRESTRICTIONS'] : '');
    $time = time();
    $q = QQ("SELECT * FROM PROSON WHERE UID = ? AND CLASSID = ? AND STATE >= ? AND STARTDATE < ? AND (ENDDATE > ? OR ENDDATE = 0)",array($uid,$reqrow['PROSONTYPE'],$required_check_level,$time,$time));
    while($r = $q->fetchArray())
    {
        $fail = 0;
        foreach($rex as $rex2)
        {
            $rex3 = explode("##",$rex2);
            if (count($rex3) != 2)
                continue;

            $fail = 1;
            $proson_par = QQ("SELECT * FROM PROSONPAR WHERE PID = ?",array($r['ID']));
            while($r2 = $proson_par->fetchArray())
            {
                $pidx = $r2['PIDX'];
                $pv = $r2['PVALUE'];

                if ($pidx != $rex3[0])
                    continue;


                    if (strstr($rex3[1],'$value'))
                    {
                        if ($pv == '')
                            $pv = 0;
                        $x = str_replace('$value',$pv,$rex3[1]);
                        try
                        {
                            $res = eval2($x,$uid);
                            if ($res == true)
                            {
                                $fail = 0;
                                break;
                            }
                            else
                                $reason = $x;
                        }
                        catch(Exception $e)
                        {

                        }
                    }
                    else
                    {
                    // check $pv against $rex3[1]
                    if (preg_match($rex3[1],$pv))
                        {
                            $fail = 0;
                            $reason = sprintf("%s/%s",$rex3[1],$pv);
                            break;
                        }
                    }
            }
            if ($fail == 1)
                break;
        }
        if ($fail)
            continue;
        if ($deep > 0)
        {
            $deep--;
            continue;
        }
        return 1;
    }
    return -1;
}

function AppPreference($apid)
{
    $apr = Single("APPLICATIONS","ID",$apid);
    if (!$apr)
        return 0;
    $e = QQ("SELECT * FROM APPLICATIONS WHERE UID = ? ORDER BY DATE ASC",array($apr['UID']));
    $j = 0;
    while($ee = $e->fetchArray())
    {
        $j++;
        if ($ee['ID'] == $apr['ID'])
            break;
    }
    return $j;
}

function ScoreForAitisi($apid)
{
    global $first_pref_score;
    $score = 0;
    $apr = Single("APPLICATIONS","ID",$apid);
    if (!$apr)
        return -1;

    if ((int)$apr['FORCEDMORIA'] != 0)
        return $apr['FORCEDMORIA'];

    $pref = AppPreference($apid);
    if ($pref == 1)
        $score += $first_pref_score;


   
    $score += ScoreForThesi($apr['UID'],$apr['CID'],$apr['PID'],$apr['POS']);
    return $score;
}

function ProsonResolutAndOrNot($uid,$pid,&$checked = array(),$deep = 0,&$reason = '')
{
    if (array_key_exists($pid,$checked))
        return -1;
    $prow  = QQ("SELECT * FROM REQS2 WHERE ID = ?",array($pid))->fetchArray();
    if (!$prow)
        return -1;
    $checked[] = $pid;
    $y = HasProson($uid,$pid,$deep,$reason);
    if ($y == -1)
    {
        if ($prow['ANDLINK'] != 0)
            return -1; // we don't have it, so entire chain fails
        if ($prow['ORLINK'] == 0)
            return -1; // nothing more to search
        return ProsonResolutAndOrNot($uid,$prow['ORLINK'],$checked,$deep,$reason);
    }
    else
    {
        // We have it
        if ($prow['NOTLINK'] != 0)   
        {
            if (ProsonResolutAndOrNot($uid,$prow['NOTLINK'],$checked,$deep,$reason) == 1)            
                {
                    return -1; // XOR fail
                }
        }

        if ($prow['ANDLINK'] != 0)
        {
            return ProsonResolutAndOrNot($uid,$prow['ANDLINK'],$checked,$deep,$reason);
        }
        return 1;
    }

}

function CalculateScore($uid,$cid,$placeid,$posid,$debug = 0)
{
    global $rejr,$xmlp,$required_check_level;
    EnsureProsonLoaded();
    $pr = Single("USERS","ID",$uid);
    if (!$pr)
        return -1;
    $contestrow = Single("CONTESTS","ID",$cid); 
    if (!$contestrow)
        return -1;
    $posr = Single("POSITIONS","ID",$posid); 
    $score = 0;



    // If we have a posid, search for generic by thesi name
    // else we search for generic
    $thesiname = '';
    if ($posr)
        $thesiname = $posr['DESCRIPTION'];
    $CountGeneral = QQ("SELECT COUNT(*) FROM REQS2 WHERE CID = ? AND PLACEID = 0 AND POSID = 0 AND FORTHESI = ?",array($cid,$thesiname))->fetchArray()[0];
    if ($CountGeneral && $thesiname != '')
        $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = 0 AND POSID = 0 AND FORTHESI = ?",array($cid,$thesiname));
    else
        $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = ? AND POSID = ? AND (FORTHESI IS NULL OR FORTHESI = '')",array($cid,$placeid,$posid));
    while($r1 = $q1->fetchArray())
    {
        $sp = $r1['SCORE'];
        $rootc = RootForClassId($xmlp->classes,$r1['PROSONTYPE']);
        if ($rootc)
            $params_root = $rootc->params;
        if ($params_root)
            {
                foreach($params_root->p as $param)
                {
                    $pa = $param->attributes();                              
                    $partypes[(int)$pa['id']] = $pa['t'];                       
                }    
            }
        $wouldeval = 0;
        if (strstr($sp,'$values'))
        {
            $wouldeval = 1;
        }

        $min_needed = 1;
        if ((int)$r1['MINX'] > 0)
            $min_needed = (int)$r1['MINX'];
        $max_needed = 0;
        if ((int)$r1['MAXX'] > 0)
            $max_needed = (int)$r1['MAXX'];

        for($deep = 0 ; ; $deep++)
        {
            if ($deep > 0 && $max_needed > 0 && $deep >= $max_needed)
                break;
            $checked = array();
            $reason = '';

            $has = ProsonResolutAndOrNot($uid,$r1['ID'],$checked,$deep,$reason);

            if ($has != 1)
            {
                if ($sp > 0 || $wouldeval == 1 || $deep >= $min_needed)
                    break; // not required
                $rootc = RootForClassId($xmlp->classes,$r1['PROSONTYPE']);
                $rejr = sprintf('Λείπει προαπαιτούμενο προσόν: %s %s x%s',$rootc->attributes()['t'],$reason,$min_needed);
                return -1;    
            }

            // He has it, 
            if ($wouldeval)
            {
                $sp = $r1['SCORE'];
                $deeps = $deep;
                $time = time();
                $qpr = QQ("SELECT * FROM PROSON WHERE UID = ? AND CLASSID = ? AND STATE >= ? AND STARTDATE < ? AND (ENDDATE > ? OR ENDDATE = 0)",array($uid,$r1['PROSONTYPE'],$required_check_level,$time,$time));
                while($rpr = $qpr->fetchArray())
                {
                    if ($deeps > 0)
                    {
                        $deeps--;
                        continue;
                    }
                    $pars = QQ("SELECT * FROM PROSONPAR WHERE PID = ?",array($rpr['ID']));
                    while($par = $pars->fetchArray())
                    {
                        $p_idx = $par['PIDX'];
                        $p_val = $par['PVALUE'];

                        // Check if it's date
                        if ($partypes[$p_idx] == 3)
                        {
                            $startwhen = $rpr['STARTDATE'];
                            $now = time();
                            if ($now > $startwhen)
                            {
                                $a1 = From360ToActual($p_val);
                                $a1 += ($now - $startwhen);
                                $p_val = FromActualTo360($a1);
                            }
                        }
                        if ($p_val == '') $p_val = 0;
                        $sp = str_replace(sprintf('$values[%s]',$p_idx),$p_val,$sp);
                    }
                }
                if (strstr($sp,'$values'))
                    $sp = 0;
                else
                    $sp = eval2($sp,$uid,$cid,$placeid,$posid);
            }

        if ($debug)
            {
                if ($sp > 0)
                    printf("%s: %s<br>",$rootc->attributes()['t'],$sp);
            }
        $score += $sp;
        }

        
    }


    if ($posid)
    {
        $v = CalculateScore($uid,$cid,$placeid,0,$debug);;
        if ($v == -1)
            return -1;
        $score += $v;
    }
    else
    if ($placeid)
    {
        $v =  CalculateScore($uid,$cid,0,0,$debug);
        if ($v == -1)
            return -1;
        $score += $v;
    }

    return $score;

}

$push3_admin = 1;
require_once "push3.php";

function ScoreForThesi($uid,$cid,$placeid,$posid,$debug = 0)
{
    return CalculateScore($uid,$cid,$placeid,$posid,$debug);
}   


function WinTable($cid)
{
/*    $contestrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($cid))->fetchArray();

    $positions = array();
    $applications = array();

    $q1 = QQ("SELECT * FROM APPLICATIONS WHERE CID = ?",array($contestrow['ID']));
    while($r1 = $q1->fetchArray())
    {
        $applications[] = array("uid" => $r1['UID'],"pos" => $r1['POS']);
    }

    $q1 = QQ("SELECT * FROM PLACES WHERE CID = ?",array($contestrow['ID']));
    while($r1 = $q1->fetchArray())
    {
        $q2 = QQ("SELECT * FROM POSITIONS WHERE CID = ? AND PLACEID = ?",array($contestrow['ID'],$r1['ID']));
        while($r2 = $q2->fetchArray())
        {
            $count = $r2['COUNT'];
            $positions[] = array("pos" => $r2['ID'],"count" => $count,"allscores" => array());


        }
    }


        foreach($positions as &$position)
        {
            foreach($applications as &$app)
            {
                if ($app['pos'] == $position['pos'])
                    {
                        $st = ScoreForThesi($app['uid'],$position['pos']);
                        if ($st >= 0)
                            $position['allscores'][] = array("uid" => $app['uid'],"s" => $st);
                    }
            }
        }
*/
        //printf("<xmp>");        print_r($positions); die;
}

function ProsonDescription($id)
{
    $r = QQ("SELECT * FROM REQS2 WHERE ID = ?",array($id))->fetchArray();
    global $xmlp,$xml_proson;
    EnsureProsonLoaded();    
    $pars = array();
    $croot = RootForClassId($xmlp->classes,$r['PROSONTYPE'],$pars);
    $attr = $croot->attributes();
    $s = sprintf('#%s<br>',$r['ID']);
    foreach($pars as $par)
    {
        $attrp = $par->attributes();
        $s .= sprintf('%s<br>',$attrp['t']);
    }
    $s .= sprintf('%s<br>',$attr['t']);


    $regex = explode("###",$r['REGEXRESTRICTIONS']? $r['REGEXRESTRICTIONS'] : '');
    foreach($regex as $r2)
    {
        $x = explode("##",$r2);
        if (count($x) == 2)
        {
            foreach($croot->params->children() as $ch)
            {
                if ($ch->attributes()->id == $x[0])
                {
                    $parx = $ch;
                    $s .= $ch->attributes()->n.' '.$x[1].'<br>';
                }
            }
        }
    }   
    if ($r['SCORE'] == 0)
        $s .= '[Προαπαιτούμενο]<br>';
    else
        $s .= sprintf('[Μόρια %s]',$r['SCORE']);

    if ($r['ORLINK'] != 0)
        $s .= sprintf(' [Εναλλακτικό %s]',$r['ORLINK']);
    $s .= '<br><br>';
    return $s;
}

function PrintProsontaForThesi($cid,$placeid,$posid)
{
    // Contest-only
    $s = '<div class="notification is-info">
    <b>Προσόντα επιπέδου διαγωνισμού</b><br><br>';
    $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = 0 AND POSID = 0 AND (FORTHESI IS NULL OR FORTHESI = '')",array($cid));
    while ($r1 = $q1->fetchArray())
    {
        $s .= ProsonDescription($r1['ID']);
    }
    $s .= '</div>';
    $s .= '<div class="notification is-info">
    <b>Προσόντα επιπέδου φορέα</b><br><br>';
    $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = ? AND POSID = 0 AND (FORTHESI IS NULL OR FORTHESI = '')",array($cid,$placeid));
    while ($r1 = $q1->fetchArray())
    {
        $s .= ProsonDescription($r1['ID']);
    }
    $s .= '</div>';

    $s .= '<div class="notification is-info">
    <b>Προσόντα επιπέδου θέσης</b><br><br>';
    $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = ? AND POSID = ? AND (FORTHESI IS NULL OR FORTHESI = '')",array($cid,$placeid,$posid));
    while ($r1 = $q1->fetchArray())
    {
        $s .= ProsonDescription($r1['ID']);
    }
    $s .= '</div>';

    return $s;
}

function PushProsonState($prid)
{
    $r = Single("PROSON","ID",$prid);
    if (!$r) return;

    $u = Single("USERS","ID",$r['UID']);
    if (!$u)
        return;

    if ($r['STATE'] < 0)
        Push3_Send(sprintf("To προσόν %s δεν έγινε δεκτό! [%s]",$r['DESCRIPTION'],$r['FAILREASON']),array($u['CLSID']));
    if ($r['STATE'] > 0)
        Push3_Send(sprintf("To προσόν %s έγινε δεκτό!",$r['DESCRIPTION']),array($u['CLSID']));

}

function PushAithsiCompleted($appid)
{
    $r = Single("APPLICATIONS","ID",$appid);
    if (!$r) return;

    $u = Single("USERS","ID",$r['UID']);
    if (!$u)
        return;

    Push3_Send("Έγινε η αίτηση!",array($u['CLSID']));
}

function KillUser($uid)
{
    $ur = Single("USERS","ID",$uid);
    if (!$ur)
        return;
    QQ("BEGIN TRANSACTION");
    QQ("DELETE FROM APPLICATIONS WHERE IID = ?",array($uid));
    $q1 = QQ("SELECT * FROM PROSON WHERE UID = ?",array($uid));
    while($r1 = $q1->fetchArray())
    {
        DeleteProson($r1['ID'],$uid);
    }
    QQ("DELETE FROM PUSHING WHERE CLSID = ?",array($ur['CLSID']));
    QQ("DELETE FROM ROLES WHERE UID = ?",array($uid));
    QQ("DELETE FROM WINTABLE WHERE UID = ?",array($uid));
    QQ("COMMIT");
}


function Kill($cid,$placeid,$posid,$appid)
{
    QQ("BEGIN TRANSACTION");
    if ($appid)
    {
        QQ("DELETE FROM APPLICATIONS WHERE ID = ?",array($appid));
    }
    else
    {
        if ($posid)
        {
            QQ("DELETE FROM APPLICATIONS WHERE POS = ?",array($posid));
            QQ("DELETE FROM POSITIONS WHERE ID = ?",array($posid));
            QQ("DELETE FROM REQS2 WHERE POSID = ?",array($posid));
        }
        else
        {
            if ($placeid)
            {
                QQ("DELETE FROM APPLICATIONS WHERE PID = ?",array($placeid));
                QQ("DELETE FROM POSITIONS WHERE PLACEID = ?",array($placeid));
                QQ("DELETE FROM REQS2 WHERE PLACEID = ?",array($placeid));
                QQ("DELETE FROM PLACES WHERE ID = ?",array($placeid));
            }
            else
            {
                if ($cid)
                {
                    QQ("DELETE FROM APPLICATIONS WHERE CID = ?",array($cid));
                    QQ("DELETE FROM POSITIONS WHERE CID = ?",array($cid));
                    QQ("DELETE FROM REQS2 WHERE CID = ?",array($cid));
                    QQ("DELETE FROM PLACES WHERE CID = ?",array($cid));
                    QQ("DELETE FROM POSITIONGROUPS WHERE CID = ?",array($cid));
                    QQ("DELETE FROM WINTABLE WHERE CID = ?",array($cid));
                    QQ("DELETE FROM CONTESTS WHERE ID = ?",array($cid));
                }
                else
                {
                    // Can't delete all !       
                }                    
            }    
        }

    }
    QQ("COMMIT");
}