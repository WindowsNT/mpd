<?php

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

$xml_proson = <<<XML
<root>
    <classes>
        <c n="1" t="Πτυχία Πανεπιστημίου" >
            <classes>
                <c n="101" t="Πτυχίο" el="6">
                    <params>
                        <p n="Ιδρυμα" id="1" t="0" />
                        <p n="Σχολή" id="2" t="0" />
                        <p n="Τμήμα" id="3" t="0" />
                        <p n="Βαθμός" id="4" t="2" min="5" max="10"/>
                    </params>
                </c>
                <c n="102" t="Μεταπτυχιακό" el="7"/>
                <c n="103" t="Διδακτορικό" el="8" >
                    <params>
                        <p n="Ιδρυμα" id="1" t="0" />
                        <p n="Σχολή" id="2" t="0" />
                        <p n="Τμήμα" id="3" t="0" />
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
                <c n="401" t="Δίπλωμα Πιάνου">
                    <params>
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="402" t="Πτυχίο Αρμονίας">
                    <params>
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="403" t="Πτυχίο Αντίστιξης">
                    <params>
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="404" t="Πτυχίο Φούγκας">
                    <params>
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="405" t="Πτυχίο Σύνθεσης">
                    <params>
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
            </classes>
        </c>


    </classes>
</root>
XML;

$xmlp = null;
function  EnsureProsonLoaded()
{
    global $xmlp,$xml_proson;
    if (!$xmlp)
        $xmlp = simplexml_load_string($xml_proson);
}

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


function PrepareDatabase($msql = 0)
{
    $j = 'AUTO_INCREMENT';
    global $lastRowID;
    if ($msql == 0)
        $j = '';
    QQ(sprintf("CREATE TABLE IF NOT EXISTS USERS (ID INTEGER PRIMARY KEY %s,MAIL TEXT,AFM TEXT,LASTNAME TEXT,FIRSTNAME TEXT)",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS ROLES (ID INTEGER PRIMARY KEY %s,UID INTEGER,ROLE INTEGER,ROLEPARAMS TEXT,FOREIGN KEY (UID) REFERENCES USERS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS ROLEPAR (ID INTEGER PRIMARY KEY %s,RID INTEGER,PIDX INTEGER,PVALUE TEXT,FOREIGN KEY (RID) REFERENCES ROLES(ID))",$j));
    /*
        Role 1 -> Checker, PAR1 : AFM list, PAR2 : Level of Checking
        Role 2 -> Test Creator
    */
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSON (ID INTEGER PRIMARY KEY %s,UID INTEGER,CLSID TEXT,DESCRIPTION TEXT,CLASSID INTEGER,STARTDATE INTEGER,ENDDATE INTEGER,STATE INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSONFILE (ID INTEGER PRIMARY KEY %s,UID INTEGER,PID INTEGER,CLSID TEXT,DESCRIPTION TEXT,FNAME TEXT,TYPE TEXT,FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (PID) REFERENCES PROSON(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSONPAR (ID INTEGER PRIMARY KEY %s,PID INTEGER,PIDX INTEGER,PVALUE TEXT,FOREIGN KEY (PID) REFERENCES PROSON(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSONEV (ID INTEGER PRIMARY KEY %s,UID INTEGER,EVUID INTEGER,RESULT INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (PID) REFERENCES PROSON(ID))",$j));

    QQ(sprintf("CREATE TABLE IF NOT EXISTS CONTESTS (ID INTEGER PRIMARY KEY %s,UID INTEGER,DESCRIPTION TEXT,STARTDATE INTEGER,ENDDATE INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PLACES (ID INTEGER PRIMARY KEY %s,CID INTEGER,PARENTPLACEID INTEGER,DESCRIPTION TEXT,FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PARENTPLACEID) REFERENCES PLACES(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS POSITIONS (ID INTEGER PRIMARY KEY %s,CID INTEGER,PLACEID INTEGER,DESCRIPTION TEXT,COUNT INTEGER,FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PLACEID) REFERENCES PLACES(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS REQUIREMENTS (ID INTEGER PRIMARY KEY %s,CID INTEGER,POSID INTEGER,PROSONTYPE INTEGER,SCORE TEXT,FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (POSID) REFERENCES POSITIONS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS REQRESTRICTIONS (ID INTEGER PRIMARY KEY %s,RID INTEGER,PID INTEGER,RESTRICTION TEXT,FOREIGN KEY (RID) REFERENCES REQUIREMENTS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS APPLICATIONS (ID INTEGER PRIMARY KEY %s,UID INTEGER,CID INTEGER,PID INTEGER,POS INTEGER,DATE INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PID) REFERENCES PLACES(ID),FOREIGN KEY (POS) REFERENCES POSITIONS(ID))",$j));

    // Test set
    QQ("INSERT INTO USERS (MAIL,AFM,LASTNAME,FIRSTNAME) VALUES ('u1@example.org','1001001001','ΠΑΠΑΔΟΠΟΥΛΟΣ','ΝΙΚΟΣ')");
    $u1Id = $lastRowID;
    QQ("INSERT INTO USERS (MAIL,AFM,LASTNAME,FIRSTNAME) VALUES ('u2@example.org','1001001002','ΓΕΩΡΓΙΟΥ','ΒΑΣΙΛΕΙΟΣ')");
    $u2Id = $lastRowID;
    QQ("INSERT INTO USERS (MAIL,AFM,LASTNAME,FIRSTNAME) VALUES ('u3@example.org','1001001003','ΝΙΚΟΛΑΟΥ','ΠΑΝΑΓΙΩΤΗΣ')");
    $u3Id = $lastRowID;
    QQ("INSERT INTO ROLES (UID,ROLE) VALUES($u2Id,1)");
    $r1id = $lastRowID;
    QQ("INSERT INTO ROLEPAR (RID,PIDX,PVALUE) VALUES($r1id,1,'1001001001')");
    QQ("INSERT INTO ROLEPAR (RID,PIDX,PVALUE) VALUES($r1id,2,'1')");
    QQ("INSERT INTO ROLES (UID,ROLE) VALUES($u3Id,2)");
    QQ("INSERT INTO USERS (MAIL,AFM,LASTNAME,FIRSTNAME) VALUES ('u4@example.org','1001001004','ΠΑΠΑΖΟΓΛΟΥ','ΜΙΧΑΗΛ')");
    $u4Id = $lastRowID;
    $rparam1 = serialize(array(
        "n1" => 1,
        "n2" => 101,
        "p1" => "ΕΚΠΑ",
        "p2" => "ΦΙΛΟΣΟΦΙΚΗ",
        "p3" => "ΤΜΗΜΑ ΜΟΥΣΙΚΩΝ ΣΠΟΥΔΩΝ",
    )); 
    QQ("INSERT INTO ROLES (UID,ROLE,ROLEPARAMS) VALUES(?,?,?)",array($u4Id,3,$rparam1));



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
        $s .= '&nbsp;';
    }
    return $s;
}

function PrintForeisContest($t,$cid,$rootfor = 0,$deep = 0)
{
    $s = '';
    $q1 = QQ("SELECT * FROM PLACES WHERE CID = ? AND PARENTPLACEID = ?",array($cid,$rootfor));
    while($r1 = $q1->fetchArray())
    {
        $s .= deepx($deep);
        $s .= sprintf('<b>%s</b> <button class="is-small is-info autobutton button" href="contest.php?editplace=1&t=%s&pid=%s">Επεξεργασία</button> <button class="button is-small is-link autobutton" href="positions.php?t=%s&cid=%s&pid=%s">Θέσεις</button> <button class="block sureautobutton is-small is-danger button" href="contest.php?deleteplace=1&t=%s&pid=%s">Διαγραφή</button><br>',$r1['DESCRIPTION'],$t,$r1['ID'],$t,$cid,$r1['ID'],$t,$r1['ID']);
        $s .= deepx($deep);
        $s .= PrintForeisContest($t,$cid,$r1['ID'],$deep + 1);
        $s .= sprintf('<a href="contest.php?addplace=1&t=%s&cid=%s&par=%s">Προσθήκη κάτω από %s</a><br>',$t,$cid,$r1['ID'],$r1['DESCRIPTION']);
    }
    if ($deep == 0)
        $s .= sprintf('<hr><a href="contest.php?addplace=1&t=%s&cid=%s&par=%s">Προσθήκη</a><br>',$t,$cid,$rootfor);

    return $s;
}
function PrintContests($t,$uid)
{
    $s = '<table class="table datatable">';
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
        $s .= sprintf('<tr>');
        $s .= sprintf('<td>%s</td>',$r1['ID']);
        $s .= sprintf('<td>%s</td>',$r1['DESCRIPTION']);
        $s .= sprintf('<td>%s &mdash; %s</td>',date("d/m/Y",$r1['STARTDATE']),date("d/m/Y",$r1['ENDDATE']));
        $s .= sprintf('<td>');
        $s .= PrintForeisContest($t,$r1['ID']);
        $s .= sprintf('</td>');
        $s .= sprintf('<td><button class="autobutton button is-small is-success" href="results.php?t=%s&cid=%s">Αποτελέσματα</button></td>',$t,$r1['ID']);

        $s .= sprintf('</tr>');
    }           

    return $s;
}

function PrintProsonta($uid,$veruid = 0,$rolerow = null)
{
    global $xmlp;
    EnsureProsonLoaded();


    $s = '<table class="table datatable">';
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

function CheckLevel($who,$target)
{
    $q1 = QQ("SELECT * FROM ROLES WHERE UID = ?",array($who));
    $cr = QQ("SELECT * FROM USERS WHERE ID = ?",array($target))->fetchArray();
    if (!$cr)
        return 0;
    $afms = array();
    while($r1 = $q1->fetchArray())
    {
        if ($r1['ROLE'] != 1)
            continue;

            $afms = array();
            $q2 = QQ("SELECT * FROM ROLEPAR WHERE RID = ?",array($r1['ID']));
            while($r2 = $q2->fetchArray())
            {
                if ($r2['PIDX'] == 1)
                    {
                        $list =  explode(",",$r2['PVALUE']);
                        foreach($list as $li)
                        $afms[] = $li;
                    }
            }
    }
    if (in_array($cr['AFM'],$afms))
        return 1;
    return 0;
}


$rejr = '';
function ScoreForThesi($uid,$posid)
{
    global $rejr,$xmlp;
    EnsureProsonLoaded();
    $pr = QQ("SELECT * FROM USERS WHERE ID = ?",array($uid))->fetchArray();
    $posr = QQ("SELECT * FROM POSITIONS WHERE ID = ?",array($posid))->fetchArray();
    if (!$pr || !$posr)
        return -2;
    $contestrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($posr['CID']))->fetchArray();
    if (!$contestrow)
        return -2;

    $score = 0;
    $q1 = QQ("SELECT * FROM REQUIREMENTS WHERE POSID = ?",array($posid));
    while($r1 = $q1->fetchArray())
    {
        // $r1 is  a proson requirement row
        // does user have this?
        $fail = 0;
        $r2 = QQ("SELECT * FROM PROSON WHERE UID = ? and CLASSID = ? AND STATE > 0 AND STARTDATE < ? AND (ENDDATE == 0 OR ENDDATE > ?)",array($uid,$r1['PROSONTYPE'],time(),$contestrow['ENDDATE']))->fetchArray();
        if (!$r2)
        {
            $fail = 1;
            if ($r1['SCORE'] == 0)
                {
                    $missingp = $r1['PROSONTYPE'];
                    $rootc = RootForClassId($xmlp->classes,$missingp);
                    $rejr = sprintf('Λείπει προαπαιτούμενο προσόν: %s',$rootc->attributes()['t']);
                    return -1; // This is a requirement, he doesn't have it
                }
            continue;
        }
        // Yes he has it

        $q3 = QQ("SELECT * FROM REQRESTRICTIONS WHERE RID = ?",array($r1['ID']));
        $fail2 = '';
        while($r3 = $q3->fetchArray())
        {
            $param = $r3['PID'];
            $what = QQ("SELECT * FROM PROSONPAR WHERE PID = ? AND PIDX = ?",array($r2['ID'],$param))->fetchArray();
            if (!$what)
            {
                $fail = 1;
            }
            else
            {
                $de = $r3['RESTRICTION'];
                if (strstr($de,"==") || strstr($de,"!=") || strstr($de,">=") || strstr($de,"<=") || strstr($de," < ") || strstr($de," > "))
                {
                    $rj = str_replace("%s",$what['PVALUE'],$de);
                    $rr = eval("return ".$rj.';');
                    if (!$rr)
                        {
                            $fail  = 1;
                            $fail2 = $de;
                        }
                }
                else
                {
                    if (preg_match($de,$what['PVALUE']) != 1)
                    {
                        $fail  = 1;
                        $fail2 = $de;
                    }
                }
            }
        }

        if ($fail)
        {
            if ($r1['SCORE'] == 0)
            {
                $missingp = $r1['PROSONTYPE'];
                $rootc = RootForClassId($xmlp->classes,$missingp);
                $rejr = sprintf('Λείπει προαπαιτούμενο προσόν: %s %s',$rootc->attributes()['t'],$fail2);
                return -1; // This is a requirement, he doesn't have it
            }
            continue;
        }

        $score += $r1['SCORE'];
    }

    return $score;
}   


function WinTable($cid)
{
    $contestrow = QQ("SELECT * FROM CONTESTS WHERE ID = ?",array($cid))->fetchArray();

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



        /*
            All positions in all places and return is 

        */
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

        //printf("<xmp>");        print_r($positions); die;
}
