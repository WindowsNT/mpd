<?php

/*
*/

ini_set('display_errors', 1); error_reporting(E_ALL);
date_default_timezone_set("Europe/Athens");
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

define('ROLE_CHECKER', 1);
define('ROLE_CREATOR',2);
define('ROLE_UNI',3);
define('ROLE_GLOBALPROSONEDITOR',4);
define('ROLE_FOREASSETPLACES',5);
define('ROLE_ROLEEDITOR',6);
define('ROLE_CONTESTVIEWER',7);
define('ROLE_SUPERADMIN',99);

function RoleToText($r)
{
    if ($r == ROLE_CHECKER) return 'Ελεγκτής Προσόντων';
    if ($r == ROLE_CREATOR) return 'Χειριστής Διαγωνισμών';
    if ($r == ROLE_UNI) return 'Ίδρυμα';
    if ($r == ROLE_GLOBALPROSONEDITOR) return 'Διορθωτής XML Προσόντων';
    if ($r == ROLE_FOREASSETPLACES) return 'Διορθωτής Κενών Φορέα';
    if ($r == ROLE_ROLEEDITOR) return 'Ελεγκτής Ρόλων';
    if ($r == ROLE_CONTESTVIEWER) return 'Προβολή Διαγωνισμών';
    return '';
}

$music_schools = 'Κανένα,Μουσικό Σχολείο Αγρινίου,Μουσικό Σχολείο Αθηνών,Μουσικό Σχολείο Αλίμου,Μουσικό Σχολείο Αλεξανδρούπολης,Μουσικό Σχολείο Αμφισσας,Μουσικό Σχολείο Αμύνταιου,Μουσικό Σχολείο Αργολίδας,Μουσικό Σχολείο Αρτας,Μουσικό Σχολείο Βέροιας,Μουσικό Σχολείο Βαρθολομιού,Μουσικό Σχολείο Βόλου,Μουσικό Σχολείο Γιαννιτσών,Μουσικό Σχολείο Δράμας,Μουσικό Σχολείο Δυτικής Λέσβου,Μουσικό Σχολείο Ζακύνθου,Μουσικό Σχολείο Ηγουμενίτσας,Μουσικό Σχολείο Ηρακλείου,Μουσικό Σχολείο Θέρισου,Μουσικό Σχολείο Θεσσαλονίκης,Μουσικό Σχολείο Ιλίου,Μουσικό Σχολείο Ιωαννίνων,Μουσικό Σχολείο Κέρκυρας,Μουσικό Σχολείο Καβάλας,Μουσικό Σχολείο Καλαμάτας,Μουσικό Σχολείο Καρδίτσας,Μουσικό Σχολείο Καστοριάς,Μουσικό Σχολείο Κατερίνης,Μουσικό Σχολείο Κομοτηνής,Μουσικό Σχολείο Κορίνθου,Μουσικό Σχολείο Λάρισας,Μουσικό Σχολείο Λαμίας,Μουσικό Σχολείο Λασιθίου,Μουσικό Σχολείο Λευκάδας,Μουσικό Σχολείο Λιβαδειάς,Μουσικό Σχολείο Μυτιλήνης,Μουσικό Σχολείο Ξάνθης,Μουσικό Σχολείο Πάτρας,Μουσικό Σχολείο Παλλήνης,Μουσικό Σχολείο Πειραιά,Μουσικό Σχολείο Πρέβεζας,Μουσικό Σχολείο Πτολεμαΐδας,Μουσικό Σχολείο Ρεθύμνου,Μουσικό Σχολείο Ρόδου,Μουσικό Σχολείο Σάμου,Μουσικό Σχολείο Σερρών,Μουσικό Σχολείο Σιάτιστας,Μουσικό Σχολείο Σπάρτης,Μουσικό Σχολείο Τρικάλων,Μουσικό Σχολείο Τριπόλεως,Μουσικό Σχολείο Χίου,Μουσικό Σχολείο Χαλκίδας';
$music_eidik = 'Μη Πτυχίο ΤΜΣ,Πτυχίο ΤΜΣ χωρίς ειδίκευση,Ακορντεόν,Βιολί,Βιολί (Παραδοσιακό),Βιολοντσέλο,Βιόλα,Θεωρητικά Βυζαντινής Μουσικής,Θεωρητικά Ευρωπαϊκής Μουσικής,Κανονάκι,Κιθάρα Ηλεκτρική,Κιθάρα Κλασική,Κλαρινέτο,Κρουστά Ευρωπαϊκά (Κλασικά - Σύγχρονα),Κρουστά παραδοσιακά,Λαούτο,Μαντολίνο,Μπουζούκι (Τρίχορδο),Πιάνο,Σαξόφωνο (Άλτο - Βαρύτονο - Τενόρο),Ταμπουράς,Τρομπέτα,Τρομπόνι,Φλάουτο';

/*$s1 = explode(",",$music_scools);
sort($s1);
$s2 = implode(",",$s1);
printdie($s2); 
*/

// ClassTypes for contests
// 101 Mousika Sxoleia Metathesis


$def_xml_proson = <<<XML
<root>
    <classes>
        <c n="10" t="Βιογραφικό Σημείωμα" unique="1" autoaccept="1" nodates="1">
        </c>

        <c n="1" t="Πτυχία Πανεπιστημίου" >
            <classes>
                <c n="101" t="Πτυχίο" el="6" candior="1">
                    <params>
                        <p n="Ιδρυμα" id="1" t="0" />
                        <p n="Σχολή" id="2" t="0" />
                        <p n="Τμήμα" id="3" t="0" unique="1" />
                        <p n="Ειδίκευση" id="5" t="0" />
                        <p n="Βαθμός" id="4" t="2" min="5" max="10"/>
                        <p n="Μουσική Ειδίκευση" id="6" t="4" min="0" max="4" list="--TMS--" />
                    </params>
                </c>
                <c n="102" t="Μεταπτυχιακό" el="7" >
                <params>
                        <p n="Ιδρυμα" id="1" t="0" />
                        <p n="Σχολή" id="2" t="0" />
                        <p n="Τμήμα" id="3" t="0" unique="1" />
                        <p n="Ειδίκευση" id="5" t="0" />
                        <p n="Τίτλος" id="6" t="0" unique="1" />
                        <p n="Βαθμός" id="4" t="2" min="5" max="10"/>
                        <p n="Είναι Integrated Master;" id="7" t="1" min="1" max="2" list="Όχι,Ναι" />
                        <p n="Μουσική Ειδίκευση" id="8" t="4" min="0" max="4" list="--TMS--" />
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
                        <p n="Μουσική Ειδίκευση" id="7" t="4" min="0" max="4" list="--TMS--" />
                    </params>
                </c>
                <c n="104" t="Μεταδιδακτορικό" el="8" >
                    <params>
                        <p n="Ιδρυμα" id="1" t="0" />
                        <p n="Σχολή" id="2" t="0" />
                        <p n="Τμήμα" id="3" t="0" unique="1" />
                        <p n="Ειδίκευση" id="5" t="0" />
                        <p n="Τίτλος" id="6" t="0" unique="1" />
                        <p n="Βαθμός" id="4" t="2" min="5" max="10"/>
                        <p n="Μουσική Ειδίκευση" id="7" t="4" min="0" max="4" list="--TMS--" />
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
                <c n="301" t="Δημόσιο" unique="1" >
                    <params>
                        <p n="Μήνες" id="1" t="2" />
                    </params>
                </c>
                <c n="302" t="Ιδιωτικό" unique="1" >
                    <params>
                          <p n="Μήνες" id="1" t="2" />
                    </params>
                </c>
            </classes>
        </c>

        <c n="4" t="Διπλώματα/Πτυχία Μουσικής από Ωδείο και Καλλιτεχνικός Φάκελος" >
            <classes>
                <c n="405" t="Πτυχίο Οργάνου" >
                    <params>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Όργανο" id="3" t="4" unique="1" list="--TMS2--"/>
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="401" t="Δίπλωμα Οργάνου" candior="1">
                    <params>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Όργανο" id="3" t="4" unique="1" list="--TMS2--"/>
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="408" t="Πτυχία Ανωτέρων Θεωρητικών" unique="1" candior="1">
                    <params>
                        <p n="Επίπεδο" id="3" t="1" min="1" max="5" list="Πτυχίο Ωδικής,Πτυχίο Αρμονίας,Πτυχίο Αντίστιξης,Πτυχίο Φούγκας,Δίπλωμα Σύνθεσης"/>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="407" t="Άλλα Διπλώματα" candior="1">
                    <params>
                        <p n="Επιλογή Διπλώματος" id="3" t="1" min="1" max="3" list="Δίπλωμα Διεύθυνσης Χορωδίας,Δίπλωμα Βυζαντινής Μουσικής,Δίπλωμα Διεύθυνσης Ορχήστρας" unique="1"/>
                        <p n="Ιδρυμα" id="2" t="0" />
                        <p n="Βαθμός" id="1" t="2" min="8" max="10"/>
                    </params>
                </c>
                <c n="411" t="Καλλιτεχνικός Φάκελος"  unique="1" autoaccept="1" nodates="1">
                </c>
            </classes>
        </c>

        <c n="5" t="Κοινωνικά κριτήρια" >
            <classes>
                <c n="501" t="Εντοπιότητα" unique="1" >
                    <params>
                        <p n="Περιοχή" id="1" t="0" />
                        <p n="Περιοχή Μουσικού Σχολείου" id="2" t="4" list="--MS--"/>
                    </params>
                </c>
                <c n="502" t="Οικογενειακή κατάσταση" unique="1" >
                    <params>
                        <p n="Γάμος" id="1" t="1" min="1" max="2"  list="Όχι,Ναι" />
                        <p n="Παιδιά" id="2" t="2" />
                        <p n="Αναπηρία (Ποσοστό)" id="3" t="2" min="0" max="100" />
                        <p n="Μονογονεϊκή Οικογένεια" id="4" t="1" min="1" max="2" list="Όχι,Ναι"  />
                    </params>
                </c>
                <c n="503" t="Συνυπηρέτηση" unique="1" >
                    <params>
                        <p n="Περιοχή" id="1" t="0" />
                        <p n="Περιοχή Μουσικού Σχολείου" id="2" t="4" list="--MS--"/>
                    </params>
                </c>
            </classes>
        </c>

        <c n="6" t="Υπηρεσιακή Κατάσταση">
            <classes>
                <c n="601" t="Υπηρεσία" unique="1" >
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
                <c n="602" t="Υπηρεσία Στελέχους" canend="2">
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
                        <p n="Διεθνές" id="3" t="1" min="1" max="2" list="Όχι,Ναι" />
                        <p n="ISSN" id="4" />
                    </params>
                </c>
                <c n="802" t="Δημοσίευση" >
                    <params>
                        <p n="Τίτλος" id="1" t="0" unique="1"/>
                        <p n="Περιοδικό" id="2" t="0"/>
                        <p n="Διεθνής" id="3" t="1" min="1" max="2" list="Όχι,Ναι" />
                        <p n="ISSN" id="4" />
                    </params>
                </c>
                <c n="803" t="Βιβλίο" >
                    <params>
                        <p n="Τίτλος" id="1" t="0" unique="1"/>
                        <p n="Εκδοτικός Οίκος" id="2" t="0"/>
                        <p n="Διεθνές" id="3" t="1" min="1" max="2" list="Όχι,Ναι" />
                        <p n="ISSN" id="4" />
                    </params>
                </c>
            </classes>
        </c>

        <c n="999" t="Άλλα προσόντα" >
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

function BeginTransaction()
{
    global $mysqli;
    if ($mysqli)
        QQ("START TRANSACTION");
    else    
        QQ("BEGIN TRANSACTION");
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



function printr($v)
{
    printf("<xmp>");
    print_r($v);
    printf("</xmp>");
}

function printdie($v)
{
    printr($v);
    die;
}

function PrepareDatabase($msql = 0)
{
    $j = 'AUTO_INCREMENT';
    global $lastRowID,$xml_proson;
    if ($msql == 0)
        $j = '';
    global $test_users;

    if ($msql && Single("GLOBALXML","ID",1))
        return;
    BeginTransaction();
    
    QQ("CREATE TABLE IF NOT EXISTS GLOBALXML (ID INTEGER PRIMARY KEY $j,XML TEXT)");
    QQ("INSERT INTO GLOBALXML (XML) VALUES (?)",array($xml_proson));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS USERS (ID INTEGER PRIMARY KEY %s,MAIL TEXT,AFM TEXT,LASTNAME TEXT,FIRSTNAME TEXT,CLSID TEXT,PASSWORD TEXT,TYPE INTEGER)",$j));
    QQ("CREATE TABLE IF NOT EXISTS BIO_INFO (ID INTEGER PRIMARY KEY $j,UID INTEGER,T1 TEXT,T2 TEXT,FOREIGN KEY (UID) REFERENCES USERS(ID))");
    QQ(sprintf("CREATE TABLE IF NOT EXISTS ROLES (ID INTEGER PRIMARY KEY %s,UID INTEGER,ROLE INTEGER,ROLEPARAMS TEXT,FOREIGN KEY (UID) REFERENCES USERS(ID))",$j));

    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSON (ID INTEGER PRIMARY KEY %s,UID INTEGER,CLSID TEXT,DESCRIPTION TEXT,CLASSID INTEGER,STARTDATE INTEGER,ENDDATE INTEGER,STATE INTEGER,FAILREASON TEXT,DIORISMOS INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSONFILE (ID INTEGER PRIMARY KEY %s,UID INTEGER,PID INTEGER,CLSID TEXT,DESCRIPTION TEXT,FNAME TEXT,TYPE TEXT,FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (PID) REFERENCES PROSON(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSONPAR (ID INTEGER PRIMARY KEY %s,PID INTEGER,PIDX INTEGER,PVALUE TEXT,FOREIGN KEY (PID) REFERENCES PROSON(ID))",$j));

    QQ(sprintf("CREATE TABLE IF NOT EXISTS CONTESTS (ID INTEGER PRIMARY KEY %s,UID INTEGER,MINISTRY TEXT,CATEGORY TEXT,DESCRIPTION TEXT,LONGDESCRIPTION TEXT,FIRSTPREFSCORE TEXT,MORIAVISIBLE INTEGER,STARTDATE INTEGER,ENDDATE INTEGER,OBJSTARTDATE INTEGER,OBJENDDATE INTEGER,CLASSTYPE INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS PLACES (ID INTEGER PRIMARY KEY %s,CID INTEGER,PARENTPLACEID INTEGER,DESCRIPTION TEXT,FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PARENTPLACEID) REFERENCES PLACES(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS POSITIONS (ID INTEGER PRIMARY KEY %s,CID INTEGER,PLACEID INTEGER,DESCRIPTION TEXT,COUNT INTEGER,FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PLACEID) REFERENCES PLACES(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS POSITIONGROUPS (ID INTEGER PRIMARY KEY %s,CID INTEGER,GROUPLIST TEXT,FOREIGN KEY (CID) REFERENCES CONTESTS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS APPLICATIONS (ID INTEGER PRIMARY KEY %s,UID INTEGER,CID INTEGER,PID INTEGER,POS INTEGER,DATE INTEGER,FORCEDMORIA TEXT,INACTIVE INTEGER,FORCERESULT INTEGER,FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PID) REFERENCES PLACES(ID),FOREIGN KEY (POS) REFERENCES POSITIONS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS WINTABLE (ID INTEGER PRIMARY KEY %s,CID INTEGER,PID INTEGER,POS INTEGER,UID INTEGER,AID INTEGER,EXTRA TEXT,FOREIGN KEY (AID) REFERENCES APPLICATIONS(ID),FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PID) REFERENCES PLACES(ID),FOREIGN KEY (POS) REFERENCES POSITIONS(ID))",$j));
    QQ(sprintf("CREATE TABLE IF NOT EXISTS OBJECTIONS (ID INTEGER PRIMARY KEY %s,AID INTEGER,OBJTEXT TEXT,DATE INTEGER,OBJANSWER TEXT,RESULT INTEGER,FOREIGN KEY (AID) REFERENCES APPLICATIONS(ID))",$j));

    QQ(sprintf("CREATE TABLE IF NOT EXISTS PROSONFORCE (ID INTEGER PRIMARY KEY %s,UID INTEGER,CID INTEGER,PLACEID INTEGER,POS INTEGER,PIDCLASS INTEGER,PRID INTEGER,SCORE TEXT,FOREIGN KEY (UID) REFERENCES USERS(ID),FOREIGN KEY (CID) REFERENCES CONTESTS(ID),FOREIGN KEY (PLACEID) REFERENCES PLACES(ID),FOREIGN KEY (POS) REFERENCES POSITIONS(ID),FOREIGN KEY (PRID) REFERENCES PROSON(ID))",$j));

    QQ(sprintf("CREATE TABLE IF NOT EXISTS REQS2 (ID INTEGER PRIMARY KEY %s,CID INTEGER,PLACEID INTEGER,POSID INTEGER,FORTHESI INTEGER,NAME TEXT,PROSONTYPE INTEGER,SCORE TEXT,ANDLINK INTEGER,ORLINK INTEGER,NOTLINK INTEGER,REGEXRESTRICTIONS TEXT,MINX INTEGER,MAXX INTEGER,
        FOREIGN KEY (PLACEID) REFERENCES PLACES(ID),
        FOREIGN KEY (CID) REFERENCES CONTESTS(ID),
        FOREIGN KEY (POSID) REFERENCES POSITIONS(ID),
        FOREIGN KEY (ORLINK) REFERENCES REQS2(ID),
        FOREIGN KEY (NOTLINK) REFERENCES REQS2(ID))",$j));

    QQ("CREATE TABLE IF NOT EXISTS PUSHING (ID INTEGER PRIMARY KEY $j,CLSID TEXT,STR TEXT,ENDPOINT TEXT)");


    // Test set
    if ($test_users == 1)
    {
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
        QQ("INSERT INTO ROLES (UID,ROLE) VALUES($u3Id,?)",array(ROLE_CREATOR));
        QQ("INSERT INTO ROLES (UID,ROLE) VALUES($u4Id,?)",array(ROLE_GLOBALPROSONEDITOR));
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
        QQ("INSERT INTO ROLES (UID,ROLE) VALUES(?,?)",array($u4Id,ROLE_UNI));
        $r4id = $lastRowID;
    }

    QQ("COMMIT");

}

function PrepareDatabaseMySQL()
{
    PrepareDatabase(1);
}


// Sqlite
if (strstr($dbxx,':'))
{
    // MySQL
    $mysqli = new \mysqli($dbxx,"umpd","e4ea15be-4dea-7754-bdde-c305a932bfa1","mpd");
    $mysqli->set_charset("utf8");
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

function HasAppAccess($aid,$uid,$wr = 0)
{
    global $superadmin; if ($superadmin) return true;
    $app = Single("APPLICATIONS","ID",$aid);
    if (!$app)
        return 0;

    if ($app['UID'] == $uid)
        return 1;

    return HasContestAccess($app['CID'],$uid,$wr);
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

    if ($wr == 0)
    {
        $j = QQ("SELECT * FROM ROLES WHERE UID = ? AND ROLE = ?",array($uid,ROLE_CONTESTVIEWER));
        while($r = $j->fetchArray())
        {
            $params = json_decode($r['ROLEPARAMS'],true);
            $contests = $params['contests'];
            if (in_array(0,$contests))
                return true;
            if (in_array($cid,$contests))
                return true;
        }
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
        if (in_array($belongsto['AFM'],$afms) || in_array(  0,$afms))
            return true;
    }

    if ($wr == 1)
        return false;

    // Check if it is a creator
    $roles = QQ("SELECT * FROM ROLES WHERE UID = ?",array($uid));
    while($r1 = $roles->fetchArray())
    {
        if ($r1['ROLE'] != ROLE_CREATOR)
            continue;
        $params = json_decode($r1['ROLEPARAMS'],true);
        if (count($params['contests']) == 1 && $params['contests'][0] == 0)
            return true;
        foreach($params['contests'] as $coid)
        {
            $zq1 = QQ("SELECT * FROM REQS2 WHERE CID = ?",array($coid));
            while($zr1 = $zq1->fetchArray())
            {
                if ($zr1['PROSONTYPE'] == $pr['CLASSID'])
                    return true;
            }        
        }
    }
        
      // Check if it is a viewer
      $roles = QQ("SELECT * FROM ROLES WHERE UID = ?",array($uid));
      while($r1 = $roles->fetchArray())
      {
          if ($r1['ROLE'] != ROLE_CONTESTVIEWER)
              continue;
          $params = json_decode($r1['ROLEPARAMS'],true);
          if (count($params['contests']) == 1 && $params['contests'][0] == 0)
              return true;
          foreach($params['contests'] as $coid)
          {
              $zq1 = QQ("SELECT * FROM REQS2 WHERE CID = ?",array($coid));
              while($zr1 = $zq1->fetchArray())
              {
                  if ($zr1['PROSONTYPE'] == $pr['CLASSID'])
                      return true;
              }        
          }
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

function PrintForeisContest($uid,$cid,$rootfor = 0,$deep = 0)
{
    $s = '';
    $cr = Single("CONTESTS","ID",$cid);
    if (!$cr)
        return $s;
    $ra = HasContestAccess($cid,$uid,0);
    $wa = HasContestAccess($cid,$uid,1);
    if (!$ra)
        return $s;

    $q1 = QQ("SELECT * FROM PLACES WHERE CID = ? AND PARENTPLACEID = ?",array($cid,$rootfor));
    while($r1 = $q1->fetchArray())
    {
        $s .= deepx($deep);
        $aitcount = QQ("SELECT COUNT(*) FROM APPLICATIONS WHERE CID = ? AND PID = ?",array($cid,$r1['ID']))->fetchArray()[0];

        $s .= sprintf('<b>%s</b><br>',$r1['DESCRIPTION']);
        if ($wa)
            $s .= sprintf('<button class="is-small is-info autobutton button block" href="contest.php?editplace=1&pid=%s">Επεξεργασία</button> ',$r1['ID']);
        $s .= sprintf('<button class="button is-small is-link autobutton block" href="positions.php?cid=%s&pid=%s">Θέσεις</button> ',$cid,$r1['ID']);
        if ($wa)
            $s .= sprintf('<button class="button autobutton is-small  block is-warning" href="contest.php?addplace=1&cid=%s&par=%s">Προσθήκη κάτω</button> ',$cid,$r1['ID'],$cid,$r1['ID'],$aitcount,$r1['ID']);

        if ($cr['CLASSID'] == 0)
            $s .= sprintf('<button class="autobutton block button is-small is-link" href="prosonta3.php?cid=%s&placeid=%s">Προσόντα Φορεα</button> ',$cid,$r1['ID']);

        $s .= sprintf('<button class="autobutton button is-small is-primary block" href="listapps.php?cid=%s&pid=%s">Λίστα Αιτήσεων (%s)</button> ',$cid,$r1['ID'],$aitcount);
        if ($wa)
            $s .= sprintf('<button class="block sureautobutton is-small is-danger button  block" href="contest.php?deleteplace=1&pid=%s">Διαγραφή</button>',$r1['ID']);
        $s .= '<br>';
        $s .= deepx($deep);
        $s .= PrintForeisContest($uid,$cid,$r1['ID'],$deep + 1);
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
        $s .= sprintf('<td>%s<br><br>%s</td>',$r1['DESCRIPTION'],$r1['LONGDESCRIPTION']);
        $s .= sprintf('<td>Αιτήσεις<br>%s &mdash; %s<br><br>Ενστάσεiς<br>%s &mdash; %s</td>',date("d/m/Y",$r1['STARTDATE']),date("d/m/Y",$r1['ENDDATE']),date("d/m/Y",$r1['OBJSTARTDATE']),date("d/m/Y",$r1['OBJENDDATE']));
        $s .= sprintf('<td>');
        $s .= PrintForeisContest($uid,$r1['ID']);
        $s .= sprintf('</td>');
        // printf('',$req['t']);
        $s .= '<td>';
        $aitcount = QQ("SELECT COUNT(*) FROM APPLICATIONS WHERE CID = ?",array($r1['ID']))->fetchArray()[0];
        if ($wa)
            $s .= sprintf('<button class="is-small is-info autobutton button block" href="contest.php?c=%s">Επεξεργασία</button> ',$r1['ID']);
        if ($r1['CLASSID'] == 0)
            $s .= sprintf('<button class="autobutton button is-small is-link block" href="positiongroups.php?cid=%s">Προσόντα Κοινών Θέσεων</button> ',$r1['ID']);
        $s .= sprintf('<button class="autobutton button is-small is-primary block" href="listapps.php?cid=%s">Λίστα Αιτήσεων (%s)</button> ',$r1['ID'],$aitcount);
        if ($r1['CLASSID'] == 0)
            $s .= sprintf('<button class="autobutton button is-small is-link block" href="prosonta3.php?cid=%s&placeid=0">Προσόντα Διαγωνισμού</button> ',$r1['ID']);
        if ($r1['ID'] != 1 && $wa)
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
        <button class="sureautobutton button is-small is-danger"  href="opsyd.php?cid=%s&f=2&from=1">Αντιγραφή Προσόντων Θέσεως από Διαγωνισμό 1</button>
    </div>
    <div class="dropdown-item">
        <button class="sureautobutton button is-small is-danger"  href="opsyd.php?cid=%s&f=3&from=1">Αντιγραφή Προσόντων Διαγωνισμού από Διαγωνισμό 1</button>
    </div>
    <div class="dropdown-item">
        <button class="sureautobutton button is-small is-danger" href="opsyd.php?cid=%s&f=4&from=1">Αντιγραφή Προσόντων Φορέων από Διαγωνισμό 1</button>
    </div>
    <div class="dropdown-item">
        <button class="sureautobutton button is-small is-danger" href="opsyd.php?cid=%s&f=5">Δημιουργία Ατόμων</button>
    </div>
    <div class="dropdown-item">
        <button class="sureautobutton button is-small is-danger" href="opsyd.php?cid=%s&f=6">Δημιουργία ΠΥΜ και Αιτήσεων από CSV ΟΠΣΥΔ</button>
    </div>
    <div class="dropdown-item">
        <button class="sureautobutton button is-small is-danger" href="opsyd.php?cid=%s&f=7">Ανέβασμα προσόντων Ατόμων από φάκελο /DDD</button>
    </div>
</div>
</div>
</div> ',$r1['ID'],$r1['ID'],$r1['ID'],$r1['ID'],$r1['ID'],$r1['ID'],$r1['ID']);
        $s .= sprintf('<button class="autobutton button is-small is-success block" href="win.php?cid=%s">Αποτελέσματα</button> ',$r1['ID']);
        if ($wa)
            $s .= sprintf('<button class="sureautobutton button is-small is-danger block" href="kill.php?cid=%s">Διαγραφή</button></td>',$r1['ID']);
    
        $s .= sprintf('</tr>');
    }           

    return $s;
}

function PrintProsonta($uid,$veruid = 0,$rolerow = null,$level = 1)
{
    global $xmlp,$required_check_level,$music_schools,$music_eidik;
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

            
    $q1 = QQ("SELECT * FROM PROSON WHERE PROSON.UID = ? ORDER BY ID DESC",array($uid));
    if ($rolerow)
        $q1 = QQ("SELECT * FROM PROSON WHERE PROSON.UID = ? ORDER BY STATE",array($uid));

    $cntx = 0;
    while($r1 = $q1->fetchArray())
    {
        $cntx++;
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

        $s .= sprintf('<td>%s<br>%s<br>%s</td>',$r1['STARTDATE'] == 0 ? '' : date("d/m/Y",$r1['STARTDATE']),$r1['ENDDATE'] ? date("d/m/Y",$r1['ENDDATE']) : '∞',$r1['DIORISMOS'] == 1 ? "Προσόν Διορισμού" : "");

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
                if ($parlist[(int)$r2['PIDX']] == "--TMS--")
                {
                    $vvv2 = '';
                }
                else
                if ($parlist[(int)$r2['PIDX']] == "--TMS2--")
                {
                    $vvv2 = '';
                }
                else
                if ($parlist[(int)$r2['PIDX']] == "--MS--")
                {
                    $vvv2 = '';
                }
                else
                {
                    $ch = explode(",",$parlist[(int)$r2['PIDX']]);
                    $vvv2 = ' - '.$ch[((int)$vvv) - 1];
                }

            }
            
            $s .= sprintf('<b>%s</b><br>%s %s<br>',$parnames[$r2['PIDX']],$vvv,$vvv2);
        }
        $s .= sprintf('</td>');


        $s .= sprintf('<td>');
        $q3 = QQ("SELECT * FROM PROSONFILE WHERE PID = ? ",array($r1['ID']));
        $file_count = 0;
        while($r3 = $q3->fetchArray())
        {
            $file_count++;
            $s .= sprintf('<b><a href="viewfile.php?f=%s" target="_blank">%s</a><br>',$r3['ID'],$r3['DESCRIPTION']);
        }

        if ($veruid == 0)
            {
                $musttext = 'Διαχείριση Αρχείων';
                $bt = 'is-link';
                if ($file_count == 0)
                    {
                        $musttext = 'Πρέπει να ανεβάσω αρχεία!';
                        $bt = 'is-danger';
                    }
                if ($r1['STATE'] > 0)
                    $s .= sprintf('<br><br><button q="Αν αλλάξετε το προσόν θα ακυρωθεί η έγκρισή του και θα πρέπει να εγκριθεί ξανά! Συνέχεια;" class="sureautobutton button is-small %s" href="files.php?e=%s&f=0">%s</button>',$bt,$r1['ID'],$musttext);
                else
                    $s .= sprintf('<br><br><button class="autobutton button is-small %s" href="files.php?e=%s&f=0">%s</button>',$bt,$r1['ID'],$musttext);
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
                $_SESSION['proson_fcid'] = $r1['ID'];
                $s .= sprintf('<button class="block autobutton button is-small is-link" href="proson.php?e=0">Αλλαγή Κατηγορίας</button> ');
                $s .= sprintf('<button class="block sureautobutton button is-small is-success" href="check.php?t=%s&approve=%s">Έγκριση</button> ',$rolerow['ID'],$r1['ID']);
                $s .= sprintf('<button class="block button is-small is-danger" onclick="rejectproson(%s,%s);">Απόρριψη</button>',$rolerow['ID'],$r1['ID']);
            }
        }
        else
        {
            if ($r1['STATE'] > 0)
                $s .= sprintf('<button q="Αν αλλάξετε το προσόν θα ακυρωθεί η έγκρισή του και θα πρέπει να το εγκρίνουν ξανά! Συνέχεια;" class="sureautobutton button is-small is-link block" href="proson.php?e=%s">Διόρθωση</button> <button class="sureautobutton button block is-small is-danger" href="proson.php?delete=%s">Διαγραφή</button>',$r1['ID'],$r1['ID']);
            else
                $s .= sprintf('<button class="autobutton button is-small is-link block" href="proson.php?e=%s">Διόρθωση</button> <button class="sureautobutton button is-small is-danger" href="proson.php?delete=%s">Διαγραφή</button>',$r1['ID'],$r1['ID']);
        }
        $s .= sprintf('</td>');



        $s .= sprintf('</tr>');
    }

    $s .= '</tbody></table>';
    if ($cntx == 0)
        {
            $s = 'Δεν έχουν ανέβει προσόντα.';
            if ($veruid == 0)
                $s .= 'Ανεβάστε ένα με το κουμπί "Νέο Προσόν".';
        }
    return $s;
}

function ApplicationProtocol($row)
{
    return  sprintf("%s%04s",date("YmdHis",$row['DATE']),$row['ID']);
}


function PrintHeader($andback = '')
{
    global $ur,$afm,$superadmin;
    $superdata = '';
    if ($superadmin)
      $superdata = sprintf('<a class="button is-primary" href="superadmin.php">Superadmin</a> <a class="button is-warning" href="update.php">Update</a> ');

    $backx = '';
    if (strlen($andback))
        $backx = sprintf('<div class="navbar-item">
        <a class="button is-danger" href="%σ">
          <strong>Πίσω</strong>
        </a>
      </div>
',$andback);

    printf('<nav class="navbar" role="navigation" aria-label="main navigation">
<div class="navbar-brand">
  <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
    <span aria-hidden="true"></span>
    <span aria-hidden="true"></span>
    <span aria-hidden="true"></span>
    <span aria-hidden="true"></span>
  </a>
</div>

<div id="navbarBasicExample" class="navbar-menu">
  <div class="navbar-start">

    %s
    <a class="navbar-item" href="index.php">
      <b>%s %s</b>&nbsp; &mdash; %s ID %s
    </a>
  </div>

  <div class="navbar-end">
    <div class="navbar-item">
      <div class="buttons">
      %s
        <a class="button is-warning" href="settings.php">
          <strong>Ρυθμίσεις</strong>
        </a>
        <a class="button is-danger" href="auth.php?redirect=index.php&logout=1">
          <strong>Logout</strong>
        </a>
      </div>
    </div>
  </div>
</div>
</nav>',$backx,$ur['LASTNAME'],$ur['FIRSTNAME'],$afm,$ur['ID'],$superdata);

  
      printf('<hr>');
}


function PrintButtons($buttons = array())
{
    global $ur,$afm;

    $li = '';
    foreach($buttons as $b)
    {
        $li .= sprintf('<a class="button %s" href="%s"><strong>%s</strong></a>',$b['s'],$b['h'],$b['n']);
    }

    printf('<nav class="navbar" role="navigation" aria-label="main navigation">
<div class="navbar-brand">
  <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
    <span aria-hidden="true"></span>
    <span aria-hidden="true"></span>
    <span aria-hidden="true"></span>
    <span aria-hidden="true"></span>
  </a>
</div>

<div id="navbarBasicExample" class="navbar-menu">
  <div class="navbar-start">
      <div class="buttons">
      %s
      </div>
  </div>

  <div class="navbar-end">
  </div>
</div>
</nav>',$li);

  
      printf('<hr>');
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

function HasProson($uid,$reqid,$deep = 0,&$reason = '',&$haslist = array())
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
        $haslist[] = $r;
        return 1;
    }
    return -1;
}

function AppPreference($apid)
{
    $apr = Single("APPLICATIONS","ID",$apid);
    if (!$apr)
        return 0;
    $e = QQ("SELECT * FROM APPLICATIONS WHERE UID = ? AND CID = ? ORDER BY DATE ASC",array($apr['UID'],$apr['CID']));
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
    $apr = Single("APPLICATIONS","ID",$apid);
    if (!$apr)
        return -1;

    if ((int)$apr['FORCEDMORIA'] != 0)
        return $apr['FORCEDMORIA'];

    $pref = AppPreference($apid);
    $de = array();
    $score = ScoreForThesi($apr['UID'],$apr['CID'],$apr['PID'],$apr['POS'],0,$de,$pref == 1);
    return $score;
}

function ProsonResolutAndOrNot($uid,$pid,&$checked = array(),$deep = 0,&$reason = '',&$haslist = array())
{
    if (array_key_exists($pid,$checked))
        return -1;
    $prow  = QQ("SELECT * FROM REQS2 WHERE ID = ?",array($pid))->fetchArray();
    if (!$prow)
        return -1;
    $checked[] = $pid;
    $y = HasProson($uid,$pid,$deep,$reason,$haslist);
    if ($y == -1)
    {
        if ($prow['ANDLINK'] != 0)
            return -1; // we don't have it, so entire chain fails
        if ($prow['ORLINK'] == 0)
            return -1; // nothing more to search
        return ProsonResolutAndOrNot($uid,$prow['ORLINK'],$checked,$deep,$reason,$haslist);
    }
    else
    {
        // We have it
        if ($prow['NOTLINK'] != 0)   
        {
            if (ProsonResolutAndOrNot($uid,$prow['NOTLINK'],$checked,$deep,$reason,$haslist) == 1)            
                {
                    return -1; // XOR fail
                }
        }

        if ($prow['ANDLINK'] != 0)
        {
            return ProsonResolutAndOrNot($uid,$prow['ANDLINK'],$checked,$deep,$reason,$haslist);
        }
        return 1;
    }

}

require_once "score.php";

$push3_admin = 1;
require_once "push3.php";

function ScoreForThesi($uid,$cid,$placeid,$posid,$debug = 0,&$desc = array(),$is1 = 0)
{
    $linkssave = array();
    return CalculateScore($uid,$cid,$placeid,$posid,$debug,$linkssave,0,$desc,0,0,$is1 ? 1 : 0);
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

function ViewUserProsontaForContest($uid,$cid,$pid = 0,$pos = 0)
{
    $s = '';
  //  $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = ? AND POSID = ?",array($cid,$pid,$pos));
    $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = ? AND POSID = ?",array($cid,0,0));
    $viewed = array();
    while($r1 = $q1->fetchArray())
    {

        $ar = array();
//        if ($r1['PROSONTYPE'] == 2) xdebug_break();

        $q2 = QQ("SELECT * FROM PROSON WHERE UID = ? AND CLASSID = ?",array($uid,$r1['PROSONTYPE']));
        while($r2 = $q2->fetchArray())
        {
            if (in_array($r2['ID'],$viewed))
                continue;
            $viewed [] = $r2['ID'];

            $scoreforthis = CalculateScore($uid,$cid,$pid,$pos,0,$ar,$r2['ID']);

            $q3 = QQ("SELECT * FROM PROSONFILE WHERE PID = ?",array($r2['ID']));
            $s .= sprintf('%s. %s<br>',$r2['ID'],$r2['DESCRIPTION']);
            while($r3 = $q3->fetchArray())
            {
                $s .= sprintf('<a href="viewfile.php?f=%s" target="_blank">%s</a><br>',$r3['ID'],$r3['DESCRIPTION']);
            }    
            $s .= sprintf('%s<br>',$scoreforthis);
            $s .= '<br>';
        }
    }
    return $s;
}

function ProsonDescription($id,$showr = 1,$link = 0)
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
                    if ($showr)
                        $s .= $ch->attributes()->n.' '.$x[1].'<br>';
                }
            }
        }
    }   
    if ($showr)
    {
        if ($r['SCORE'] == 0)
            $s .= '[Προαπαιτούμενο]<br>';
        else
            $s .= sprintf('[Μόρια %s]',$r['SCORE']);
    }

    if ($r['ORLINK'] != 0)
        $s .= sprintf(' [Εναλλακτικό %s]',$r['ORLINK']);
    if ($r['ANDLINK'] != 0)
        $s .= sprintf(' [Συνδυαστικό %s]',$r['ANDLINK']);
    $s .= '<br><br>';
    return $s;
}

function PrintProsontaForThesi($cid,$placeid,$posid,$what = 0)
{
    // Contest-only
    if ($cid != 0)
    {
        $s = '<div class="notification is-info">
        <b>Προσόντα επιπέδου διαγωνισμού</b><br><br>';
        $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = 0 AND POSID = 0 AND (FORTHESI IS NULL OR FORTHESI = '')",array($cid));
        while ($r1 = $q1->fetchArray())
        {
            $s .= ProsonDescription($r1['ID'],$what == 1 ? 0 : 1);
        }
        $s .= '</div>';
    }
    if ($placeid != 0)
    {
        $s .= '<div class="notification is-info">
        <b>Προσόντα επιπέδου φορέα</b><br><br>';
        $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = ? AND POSID = 0 AND (FORTHESI IS NULL OR FORTHESI = '')",array($cid,$placeid));
        while ($r1 = $q1->fetchArray())
        {
            $s .= ProsonDescription($r1['ID'],$what == 1 ? 0 : 1);
        }
        $s .= '</div>';
    }
    if ($posid != 0)
    {
        $s .= '<div class="notification is-info">
        <b>Προσόντα επιπέδου θέσης</b><br><br>';
        $q1 = QQ("SELECT * FROM REQS2 WHERE CID = ? AND PLACEID = ? AND POSID = ? AND (FORTHESI IS NULL OR FORTHESI = '')",array($cid,$placeid,$posid));
        while ($r1 = $q1->fetchArray())
        {
            $s .= ProsonDescription($r1['ID'],$what == 1 ? 0 : 1);
        }
        $s .= '</div>';
    }
    return $s;
}

function PushProsonState($prid)
{
    global $required_check_level;
    $r = Single("PROSON","ID",$prid);
    if (!$r) return;

    $u = Single("USERS","ID",$r['UID']);
    if (!$u)
        return;

    if ($r['STATE'] < 0)
        Push3_Send(sprintf("To προσόν %s δεν έγινε δεκτό! [%s]",$r['DESCRIPTION'],$r['FAILREASON']),array($u['CLSID']));
    if ($r['STATE'] >= $required_check_level)
        Push3_Send(sprintf("To προσόν %s έγινε δεκτό!",$r['DESCRIPTION']),array($u['CLSID']));

}

function PushProsonCheckers($pid)
{
    
    $prr = Single("PROSON","ID",$pid);
    if (!$prr)
        return;

    $wu = Single("USERS","ID",$prr['UID']);
    if (!$wu)
        return;

    $where = array();
    $q1 = QQ("SELECT * FROM ROLES WHERE ROLE = ?",array(ROLE_CHECKER));
    while($r1 = $q1->fetchArray())
    {
        $y = 0;
        $params = json_decode($r1['ROLEPARAMS'],true);
        $afms = $params['afms'];
        if (count($afms) == 1 && $afms[0] == 0)
            $y = 1;
        else
        {
            if (in_array($wu['AFM'],$afms))
                $y = 1;
        }

        if ($y)
        {
            $wu2 = Single("USERS","ID",$r1['UID']);
            if ($wu2)
                $where[] = $wu2['CLSID'];
        }
    }

    $where = array_unique($where);
    Push3_Send(sprintf("Ανέβασμα νέου προσόντος\r\n%s %s",$wu['LASTNAME'],$wu['FIRSTNAME']),$where);
}


function PushAithsiRemoved($uid)
{
    $u = Single("USERS","ID",$uid);
    if (!$u)
        return;

    Push3_Send("Η αίτηση διαγράφηκε!",array($u['CLSID']));

}

function PushAithsiCompleted($appid)
{
    $r = Single("APPLICATIONS","ID",$appid);
    if (!$r) return;

    $u = Single("USERS","ID",$r['UID']);
    if (!$u)
        return;

    Push3_Send(sprintf("Έγινε η αίτηση!\r\nΑ.Π. %s",ApplicationProtocol($r)),array($u['CLSID']));

    // And checkers
    $where = array();
    $q1 = QQ("SELECT * FROM ROLES WHERE ROLE = ?",array(ROLE_CREATOR));
    while($r1 = $q1->fetchArray())
    {
        $y = 0;
        $params = json_decode($r1['ROLEPARAMS'],true);
        $afms = $params['contests'];
        if (count($afms) == 1 && $afms[0] == 0)
            $y = 1;
        else
        {
            if (in_array($r['CID'],$afms))
                $y = 1;
        }

        if ($y)
        {
            $wu2 = Single("USERS","ID",$r1['UID']);
            if ($wu2)
                $where[] = $wu2['CLSID'];
        }
    }

    $where = array_unique($where);
    $cr = Single("CONTESTS","ID",$r['CID']);
    $fr = Single("PLACES","ID",$r['PID']);
    $pr = Single("POSITIONS","ID",$r['POS']);
    Push3_Send(sprintf("Νέα αίτηση\r\n%s %s\r\n%s\r\n%s\r\n%s",$u['LASTNAME'],$u['FIRSTNAME'],$cr['DESCRIPTION'],$fr['DESCRIPTION'],$pr['DESCRIPTION']),$where);

}

function scanAllDir($dir,$dirs = false) {
    $result = [];
    foreach(scandir($dir) as $filename) {
      if ($filename[0] === '.') continue;
      $filePath = $dir . '/' . $filename;
      if (is_dir($filePath)) {
        if ($dirs) $result[] = $filename;
        foreach (scanAllDir($filePath,$dirs) as $childFilename) {
          $result[] = $filename . '/' . $childFilename;
        }
      } else {
        $result[] = $filename;
      }
    }
    return $result;
  }
  
function KillUser($uid,$trs = 0)
{
    $ur = Single("USERS","ID",$uid);
    if (!$ur)
        return;
    if ($trs == 0)
        BeginTransaction();
    QQ("DELETE FROM APPLICATIONS WHERE UID = ?",array($uid));
    $q1 = QQ("SELECT * FROM PROSON WHERE UID = ?",array($uid));
    while($r1 = $q1->fetchArray())
    {
        DeleteProson($r1['ID'],$uid);
    }
    QQ("DELETE FROM PUSHING WHERE CLSID = ?",array($ur['CLSID']));
    QQ("DELETE FROM ROLES WHERE UID = ?",array($uid));
    QQ("DELETE FROM WINTABLE WHERE UID = ?",array($uid));
    QQ("DELETE FROM USERS WHERE ID = ?",array($uid));
    if ($trs == 0)
        QQ("COMMIT");
}


function KillUsersType1()
{
    $uids = array();
    $q1 = QQ("SELECT * FROM USERS WHERE TYPE = 1");
    while($r1 = $q1->fetchArray())
    {
        $uids[] = $r1['ID'];
    }
    BeginTransaction();
    foreach($uids as $u)
        KillUser($u, true);
    QQ("COMMIT");
    QQ("VACUUM");
}


function IsMusName($n)
{
    if ($n == "ΤΜΗΜΑ ΜΟΥΣΙΚΩΝ ΣΠΟΥΔΩΝ") return true;
    return false;
}

function RemoveAccents1($e)
{
    $search = explode(",","ά,έ,ή,ί,ό,ύ,ώ,ϊ,ΐ,ϋ,ΰ");
    $replace = explode(",","α,ε,η,ι,ο,υ,ω,ι,ι,υ,υ");
    $ee = str_replace($search, $replace, $e);
    return $ee;
}

function RemoveAccents2($e)
{
    $search = explode(",","Ά,Έ,Ή,Ί,Ό,Ύ,Ώ,Ϊ");
    $replace = explode(",","Α,Ε,Η,Ι,Ο,Υ,Ω,Ι");
    $ee = str_replace($search, $replace, $e);
    return $ee;
}

function RemoveAccents($e)
{   
    return RemoveAccents2(RemoveAccents1($e));
}
function Kill($cid,$placeid,$posid,$appid)
{
    BeginTransaction();
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