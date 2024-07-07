<?php

/*


*/

ini_set('display_errors', 1); error_reporting(E_ALL);
if (session_status() == PHP_SESSION_NONE) 
    session_start();
$req = array_merge($_GET,$_POST);

// Database functions
$dbxx = 'mpd.db';
$lastRowID = 0;
$db = null;
$mustprepare = 0;
$mysqli = null;

$def_xml_proson = <<<XML
<root>
    <classes>
        <c n="1" t="Πτυχία Πανεπιστημίου" >
            <classes>
                <c n="101" t="Πτυχίο" el="6">
                    <params>
                        <p n="Ιδρυμα" id="1" t="0" />
                        <p n="Σχολή" id="2" t="0" />
                        <p n="Τμήμα" id="3" t="0" />
                        <p n="Ειδίκευση" id="5" t="0" />
                        <p n="Βαθμός" id="4" t="2" min="5" max="10"/>
                    </params>
                </c>
                <c n="102" t="Μεταπτυχιακό" el="7">
                <params>
                        <p n="Ιδρυμα" id="1" t="0" />
                        <p n="Σχολή" id="2" t="0" />
                        <p n="Τμήμα" id="3" t="0" />
                        <p n="Ειδίκευση" id="5" t="0" />
                        <p n="Βαθμός" id="4" t="2" min="5" max="10"/>
                    </params>
                </c>
                <c n="103" t="Διδακτορικό" el="8" >
                    <params>
                        <p n="Ιδρυμα" id="1" t="0" />
                        <p n="Σχολή" id="2" t="0" />
                        <p n="Τμήμα" id="3" t="0" />
                        <p n="Ειδίκευση" id="5" t="0" />
                        <p n="Βαθμός" id="4" t="2" min="5" max="10"/>
                    </params>
                </c>
            </classes>
        </c>
        <c n="2" t="Ξένες Γλώσσες" >
            <classes>
                <c n="201" t="B1">
                </c>
                <c n="202" t="B2">
                </c>
                <c n="203" t="C1">
                </c>
                <c n="204" t="C2">
                </c>
            </classes>
        </c>

        <c n="3" t="Εργασιακή Εμπειρία" >
            <classes>
                <c n="301" t="Δημόσιο">
                    <params>
                        <p n="Μήνες" id="1" t="2" />
                    </params>
                </c>
                <c n="302" t="Ιδιωτικό">
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
                        <p n="Όργανο" id="3" t="0" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="401" t="Δίπλωμα Οργάνου">
                    <params>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Όργανο" id="3" t="0" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="406" t="Πτυχίο Ωδικής">
                    <params>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="402" t="Πτυχίο Αρμονίας">
                    <params>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="403" t="Πτυχίο Αντίστιξης">
                    <params>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="404" t="Πτυχίο Φούγκας">
                    <params>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="408" t="Πτυχίο Σύνθεσης">
                    <params>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="407" t="Δίπλωμα Βυζαντινής">
                    <params>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
            </classes>
        </c>

        <c n="5" t="Κοινωνικά κριτήρια" >
            <classes>
                <c n="501" t="Εντοπιότητα">
                    <params>
                        <p n="Περιοχή" id="1" t="0" />
                    </params>
                </c>
                <c n="502" t="Οικογενειακή κατάσταση">
                    <params>
                        <p n="Γάμος" id="1" t="2" min="0" max="1" />
                        <p n="Παιδιά" id="2" t="2" />
                        <p n="Αναπηρία" id="3" t="2" min="0" max="100" />
                    </params>
                </c>
            </classes>
        </c>

        <c n="6" t="Υπηρεσιακή Κατάσταση" >
            <classes>
                <c n="601" t="Υπηρεσία">
                    <params>
                        <p n="Κλάδος" id="1" t="0" />
                        <p n="ΑΜ" id="2" t="2" />
                        <p n="Μουσική Ειδίκευση" id="8" t="0" />
                        <p n="Εκπαιδευτική Προϋπηρεσία" id="3" t="2" />
                        <p n="Προϋπηρεσία στα Μουσικά" id="4" t="2" />
                        <p n="Περιοχή Οργανικής" id="5" t="0" />
                        <p n="Τύπος Οργανικής" id="6" t="0" />
                        <p n="Οργανική Θέση" id="7" t="0" />
                        <p n="Συνολική Προϋπηρεσία" id="9" t="2" />
                    </params>
                </c>
            </classes>
        </c>

        <c n="7" t="ΤΠΕ" >
            <classes>
                <c n="701" t="Α">
                </c>
                <c n="702" t="B1">
                </c>
                <c n="703" t="Β2">
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

function PrepareDatabase($msql = 0)
{
    $j = 'AUTO_INCREMENT';
    global $lastRowID,$xml_proson;
    if ($msql == 0)
        $j = '';
    QQ("CREATE TABLE IF NOT EXISTS GLOBALXML (ID INTEGER PRIMARY KEY,XML TEXT)");
    QQ("INSERT INTO GLOBALXML (XML) VALUES (?)",array($xml_proson));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS USERS (ID INTEGER PRIMARY KEY %s,MAIL TEXT,AFM TEXT,LASTNAME TEXT,FIRSTNAME TEXT,CLSID TEXT)",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS ROLES (ID INTEGER PRIMARY KEY %s,UID INTEGER,ROLE INTEGER,ROLEPARAMS TEXT,FOREIGN KEY (UID) REFERENCES USERS(ID))",$j));

    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSON (ID INTEGER PRIMARY KEY %s,UID INTEGER,CLSID TEXT,DESCRIPTION TEXT,CLASSID INTEGER,STARTDATE INTEGER,ENDDATE INTEGER,STATE INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSONFILE (ID INTEGER PRIMARY KEY %s,UID INTEGER,PID INTEGER,CLSID TEXT,DESCRIPTION TEXT,FNAME TEXT,TYPE TEXT,FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (PID) REFERENCES PROSON(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSONPAR (ID INTEGER PRIMARY KEY %s,PID INTEGER,PIDX INTEGER,PVALUE TEXT,FOREIGN KEY (PID) REFERENCES PROSON(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSONEV (ID INTEGER PRIMARY KEY %s,UID INTEGER,EVUID INTEGER,RESULT INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (PID) REFERENCES PROSON(ID))",$j));

    QQ(sprintf("CREATE TABLE IF NOT EXISTS CONTESTS (ID INTEGER PRIMARY KEY %s,UID INTEGER,DESCRIPTION TEXT,STARTDATE INTEGER,ENDDATE INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PLACES (ID INTEGER PRIMARY KEY %s,CID INTEGER,PARENTPLACEID INTEGER,DESCRIPTION TEXT,FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PARENTPLACEID) REFERENCES PLACES(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS POSITIONS (ID INTEGER PRIMARY KEY %s,CID INTEGER,PLACEID INTEGER,DESCRIPTION TEXT,COUNT INTEGER,FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PLACEID) REFERENCES PLACES(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS POSITIONGROUPS (ID INTEGER PRIMARY KEY %s,CID INTEGER,GROUPLIST TEXT,FOREIGN KEY (CID) REFERENCES CONTESTS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS APPLICATIONS (ID INTEGER PRIMARY KEY %s,UID INTEGER,CID INTEGER,PID INTEGER,POS INTEGER,DATE INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PID) REFERENCES PLACES(ID),FOREIGN KEY (POS) REFERENCES POSITIONS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS WINTABLE (ID INTEGER PRIMARY KEY %s,CID INTEGER,PID INTEGER,POS INTEGER,UID INTEGER,AID INTEGER,EXTRA TEXT,FOREIGN KEY (AID) REFERENCES APPLICATIONS(ID),FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PID) REFERENCES PLACES(ID),FOREIGN KEY (POS) REFERENCES POSITIONS(ID))",$j));

    QQ(sprintf("CREATE TABLE IF NOT EXISTS REQS2 (ID INTEGER PRIMARY KEY %s,CID INTEGER,PLACEID INTEGER,POSID INTEGER,FORTHESI INTEGER,NAME TEXT,PROSONTYPE INTEGER,SCORE TEXT,ANDLINK INTEGER,ORLINK INTEGER,NOTLINK INTEGER,REGEXRESTRICTIONS TEXT,
        FOREIGN KEY (PLACEID) REFERENCES PLACES(ID),
        FOREIGN KEY (CID) REFERENCES CONTESTS(ID),
        FOREIGN KEY (POSID) REFERENCES POSITIONS(ID),
        FOREIGN KEY (ORLINK) REFERENCES REQS2(ID),
        FOREIGN KEY (NOTLINK) REFERENCES REQS2(ID))",$j));

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



$xml_proson_row = QQ("SELECT * FROM GLOBALXML WHERE ID = 1")->fetchArray();
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


// Access functions
function HasPlaceAccessForKena($pid,$uid)
{
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

function HasContestAccess($cid,$uid,$wr = 0) 
{
    $crow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($cid))->fetchArray();
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

function HasProsonAccess($pid,$uid,$wr = 0)
{
    $pr = QQ("SELECT * FROM PROSON WHERE ID = ?",array($pid))->fetchArray();
    if (!$pr)
        return false;
    if ($pr['UID'] == $uid)
        return true;
    if ($wr == 1)
        return false;

    $belongsto = QQ("SELECT * FROM USERS WHERE ID = ?",array($pr['UID']))->fetchArray();
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
    $pr = QQ("SELECT * FROM PROSONFILE WHERE ID = ?",array($fid))->fetchArray();
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
        $s .= sprintf('<b>%s</b><br> <button class="is-small is-info autobutton button" href="contest.php?editplace=1&pid=%s">Επεξεργασία</button> <button class="button is-small is-link autobutton" href="positions.php?cid=%s&pid=%s">Θέσεις</button> <button class="button autobutton is-small is-warning" href="contest.php?addplace=1&cid=%s&par=%s">Προσθήκη κάτω</button> <button class="autobutton button is-small is-link" href="prosonta3.php?cid=%s&placeid=%s">Προσόντα Φορεα</button> <button class="block sureautobutton is-small is-danger button" href="contest.php?deleteplace=1&pid=%s">Διαγραφή</button><br>',$r1['DESCRIPTION'],$r1['ID'],$cid,$r1['ID'],$cid,$r1['ID'],$cid,$r1['ID'],$r1['ID']);
        $s .= deepx($deep);
        $s .= PrintForeisContest($cid,$r1['ID'],$deep + 1);
    }
    if ($deep == 0)
        $s .= sprintf('<hr><button class="button is-primary is-small autobutton" href="contest.php?addplace=1&cid=%s&par=%s">Προσθήκη</button><br>',$cid,$rootfor);

    return $s;
}
function PrintContests($uid)
{

    $s = '<table class="table datatable" style="width: 100%">';
    $s .= '<thead>
                <th>#</th>
                <th>Περιγραφή</th>
                <th>Ημερομηνίες</th>
                <th>Φορείς</th>
                <th>Ενέργειες</th>
            </thead><tbody>';

    $q1 = QQ("SELECT * FROM CONTESTS WHERE UID = ?",array($uid));
    while($r1 = $q1->fetchArray())
    {
        $ra = HasContestAccess($r1['ID'],$uid,0);
        $wa = HasContestAccess($r1['ID'],$uid,1);
        if (!$ra)
            continue;
        $s .= sprintf('<tr>');
        $s .= sprintf('<td>%s</td>',$r1['ID']);
        $s .= sprintf('<td>%s</td>',$r1['DESCRIPTION']);
        $s .= sprintf('<td>%s &mdash; %s</td>',date("d/m/Y",$r1['STARTDATE']),date("d/m/Y",$r1['ENDDATE']));
        $s .= sprintf('<td>');
        $s .= PrintForeisContest($r1['ID']);
        $s .= sprintf('</td>');
        // printf('',$req['t']);
        $s .= '<td>';
        if ($wa == 1)
        {
            $s .= sprintf('<div class="dropdown is-hoverable">
  <div class="dropdown-trigger">
    <button class="button is-secondary is-small block" aria-haspopup="true" aria-controls="dropdown-menu4">
      <span>ΟΠΣΥΔ</span>
    </button>
  </div>
  <div class="dropdown-menu" id="dropdown-menu4" role="menu">
    <div class="dropdown-content">
      <div class="dropdown-item">
            <a href="opsyd.php?cid=%s&f=1">Εισαγωγή Κενών</a>
      </div>
      <div class="dropdown-item">
            <a href="opsyd.php?cid=%s&f=2&from=1">Αντιγραφή Προσόντων Θέσεως</a>
      </div>
      <div class="dropdown-item">
            <a href="opsyd.php?cid=%s&f=3&from=1">Αντιγραφή Προσόντων Διαγωνισμού</a>
      </div>
      <div class="dropdown-item">
            <a href="opsyd.php?cid=%s&f=4&from=1">Αντιγραφή Προσόντων Φορέων</a>
      </div>
    </div>
  </div>
</div> ',$r1['ID'],$r1['ID'],$r1['ID'],$r1['ID']);
            $s .= sprintf('<button class="is-small is-info autobutton button" href="contest.php?c=%s">Επεξεργασία</button> ',$r1['ID']);
            $s .= sprintf('<button class="autobutton button is-small is-link" href="positiongroups.php?cid=%s">Προσόντα Κοινών Θέσεων</button> ',$r1['ID']);
            $s .= sprintf('<button class="autobutton button is-small is-link" href="prosonta3.php?cid=%s&placeid=0">Προσόντα Διαγωνισμού</button> ',$r1['ID']);
            $s .= sprintf('<button class="autobutton button is-small is-success" href="win.php?cid=%s">Αποτελέσματα</button> ',$r1['ID']);
           $s .= sprintf('<button class="sureautobutton button is-small is-danger" href="kill.php?cid=%s">Διαγραφή</button></td>',$r1['ID']);
        }
        $s .= sprintf('</tr>');
    }           

    return $s;
}

function PrintProsonta($uid,$veruid = 0,$rolerow = null)
{
    global $xmlp;
    EnsureProsonLoaded();


    $s = '<table class="table datatable" style="width: 100%">';
    $s .= '<thead>
                <th>#</th>
                <th>Περιγραφή</th>
                <th>Κατηγορία</th>
                <th>Ισχύς</th>
                <th>Παράμετροι</th>
                <th>Αρχεία</th>
                <th>Κατάσταση</th>
                <th>Εντολές</th>
            </thead><tbody>';

            
    $q1 = QQ("SELECT * FROM PROSON WHERE PROSON.UID = ? ",array($uid));
    while($r1 = $q1->fetchArray())
    {
        $s .= sprintf('<tr>');
        $s .= sprintf('<td>%s</td>',$r1['ID']);
        $s .= sprintf('<td>%s</td>',$r1['DESCRIPTION']);

        $parnames = array();
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
        }    

        $s .= sprintf('<td>%s - %s</td>',date("d/m/Y",$r1['STARTDATE']),$r1['ENDDATE'] ? date("d/m/Y",$r1['ENDDATE']) : '∞');

        // Parameters
        $s .= sprintf('<td>');
        $q2 = QQ("SELECT * FROM PROSONPAR WHERE PID = ? ",array($r1['ID']));
        while($r2 = $q2->fetchArray())
        {
            $s .= sprintf('<b>%s</b><br>%s<br>',$parnames[$r2['PIDX']],$r2['PVALUE']);
        }
        $s .= sprintf('</td>');


        $s .= sprintf('<td>');
        $q3 = QQ("SELECT * FROM PROSONFILE WHERE PID = ? ",array($r1['ID']));
        while($r3 = $q3->fetchArray())
        {
            $s .= sprintf('<b><a href="viewfile.php?f=%s" target="_blank">%s</a><br>',$r3['ID'],$r3['FNAME']);
        }

        if ($veruid == 0)
            $s .= sprintf('<br><br><button class="autobutton button is-small is-link" href="files.php?e=%s&f=0">Διαχείριση Αρχείων</button>',$r1['ID']);
        $s .= sprintf('</td>');
        $s .= sprintf('<td>');
        if ($r1['STATE'] == 0) $s .= 'Αναμονή';
        if ($r1['STATE'] < 0) $s .= 'Απόρριψη';
        if ($r1['STATE'] >= 1) $s .= sprintf('Έγκριση Επίπεδο %s',$r1['STATE']);
        $s .= sprintf('</td>');
        $s .= sprintf('<td>');
        if ($veruid)
        {

            if ($r1['STATE'] == 0) 
            {
                $s .= sprintf('<button class="block sureautobutton button is-small is-success" href="check.php?t=%s&approve=%s">Έγκριση</button> ',$rolerow['ID'],$r1['ID']);
                $s .= sprintf('<button class="block sureautobutton button is-small is-danger" href="check.php?t=%s&reject=%s">Απόρριψη</button>',$rolerow['ID'],$r1['ID']);
            }
        }
        else
        {
            $s .= sprintf('<button class="autobutton button is-small is-link" href="proson.php?e=%s">Διόρθωση</button> <button class="sureautobutton button is-small is-danger" href="proson.php?delete=%s">Διαγραφή</button>',$r1['ID'],$r1['ID']);
        }
        $s .= sprintf('</td>');



        $s .= sprintf('</tr>');
    }

    $s .= '</tbody></table>';
    return $s;
}


function DeleteProsonFile($id,$uid = 0)
{
    if ($uid)
        $e = QQ("SELECT * FROM PROSONFILE WHERE ID = ? AND UID = ?",array($id,$uid))->fetchArray();
    else
        $e = QQ("SELECT * FROM PROSONFILE WHERE ID = ?",array($id))->fetchArray();
    if (!$e)
        return false;

    unlink(sprintf("./files/%s",$e['CLSID']));
    QQ("DELETE FROM PROSONFILE WHERE ID = ?",array($id));
    return true;
}

function DeleteProson($id,$uid = 0)
{
    if ($uid)
        $e = QQ("SELECT * FROM PROSON WHERE ID = ? AND UID = ?",array($id,$uid))->fetchArray();
    else
        $e = QQ("SELECT * FROM PROSON WHERE ID = ?",array($id))->fetchArray();
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



$rejr = '';

function HasProson($uid,$reqid)
{
    $reqrow = QQ("SELECT * FROM REQS2 WHERE ID = ?",array($reqid))->fetchArray();
    if (!$reqrow)
        return -1;


    $rex = explode("|||",$reqrow['REGEXRESTRICTIONS'] ? $reqrow['REGEXRESTRICTIONS'] : '');
    $q = QQ("SELECT * FROM PROSON WHERE UID = ? AND CLASSID = ? AND STATE > 0",array($uid,$reqrow['PROSONTYPE']));
    while($r = $q->fetchArray())
    {
        $fail = 0;
        foreach($rex as $rex2)
        {
            $rex3 = explode("||",$rex2);
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
                        $x = str_replace('$value',$pv,$rex3[1]);
                        try
                        {
                        $res = eval($x);
                        if ($res == true)
                            {
                                $fail = 0;
                                break;
                            }
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
                            break;
                        }
                    }
            }
                if ($fail == 1)
                break;
        }
        if ($fail)
            continue;
        return 1;
    }
    return -1;
}

function AppPreference($apid)
{
    $apr = QQ("SELECT * FROM APPLICATIONS WHERE ID = ?",array($apid))->fetchArray();
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
    $score = 0;
    $apr = QQ("SELECT * FROM APPLICATIONS WHERE ID = ?",array($apid))->fetchArray();
    if (!$apr)
        return -1;

    $pref = AppPreference($apid);
    if ($pref == 1)
        $score += 2.0;
   
    $score += ScoreForThesi($apr['UID'],$apr['CID'],$apr['PID'],$apr['POS']);
    return $score;
}

function ProsonResolutAndOrNot($uid,$pid,&$checked = array())
{
    if (array_key_exists($pid,$checked))
        return -1;
    $prow  = QQ("SELECT * FROM REQS2 WHERE ID = ?",array($pid))->fetchArray();
    if (!$prow)
        return -1;
    $checked[] = $pid;
    $y = HasProson($uid,$pid);
    if ($y == -1)
    {
        if ($prow['ANDLINK'] != 0)
            return -1; // we don't have it, so entire chain fails
        if ($prow['ORLINK'] == 0)
            return -1; // nothing more to search
        return ProsonResolutAndOrNot($uid,$prow['ORLINK'],$checked);
    }
    else
    {
        // We have it
        if ($prow['NOTLINK'] != 0)   
        {
            if (ProsonResolutAndOrNot($uid,$prow['NOTLINK'],$checked) == 1)            
                return -1; // XOR fail
        }

        if ($prow['ANDLINK'] != 0)
        {
            return ProsonResolutAndOrNot($uid,$prow['ANDLINK'],$checked);
        }
        return 1;
    }

}

function ScoreForThesi($uid,$cid,$placeid,$posid)
{
    global $rejr,$xmlp;
    EnsureProsonLoaded();
    $pr = QQ("SELECT * FROM USERS WHERE ID = ?",array($uid))->fetchArray();
    if (!$pr)
        return -1;
    $contestrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($cid))->fetchArray();
    if (!$contestrow)
        return -1;
    $posr = QQ("SELECT * FROM POSITIONS WHERE ID = ?",array($posid))->fetchArray();
    $score = 0;

    // Has general contest
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
        if (strstr($sp,'$values'))
        {
            $qpr = QQ("SELECT * FROM PROSON WHERE UID = ? AND CLASSID = ? AND STATE > 0",array($uid,$r1['PROSONTYPE']));
            while($rpr = $qpr->fetchArray())
            {
                $pars = QQ("SELECT * FROM PROSONPAR WHERE PID = ?",array($rpr['ID']));
                while($par = $pars->fetchArray())
                {
                    $p_idx = $par['PIDX'];
                    $p_val = $par['PVALUE'];
                    $sp = str_replace(sprintf('$values[%s]',$p_idx),$p_val,$sp);
                }
            }
            $sp = eval($sp);
        }
        $has = ProsonResolutAndOrNot($uid,$r1['ID']);
        if ($has == 1)
            {
                $score += $sp;
                continue;
            }
        if ($sp > 0)
            continue; // not required
        $rootc = RootForClassId($xmlp->classes,$r1['PROSONTYPE']);
        $rejr = sprintf('Λείπει προαπαιτούμενο προσόν: %s',$rootc->attributes()['t']);
        return -1;
    }


    if ($posid)
    {
        $v = ScoreForThesi($uid,$cid,$placeid,0);;
        if ($v == -1)
            return -1;
        $score += $v;
    }
    else
    if ($placeid)
    {
        $v =  ScoreForThesi($uid,$cid,0,0);
        if ($v == -1)
            return -1;
        $score += $v;
    }

    return $score;
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


    $regex = explode("|||",$r['REGEXRESTRICTIONS']? $r['REGEXRESTRICTIONS'] : '');
    foreach($regex as $r2)
    {
        $x = explode("||",$r2);
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