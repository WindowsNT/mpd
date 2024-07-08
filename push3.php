<?php
//ini_set('display_errors', 1); error_reporting(E_ALL);

// push operations

require_once "config.php";
$Push3_dbname = $dbxx;
$Push3_db = new SQLite3($Push3_dbname);
$push3_lrid = 0;
$Push3_pushpub = 'BBU6HUCQKe8c6WZohMsUffZW4Afsux4Va-ZVzXJLf9Bt42L-bBeHjfVdWO62fJzDbKtOnnYTtz30y7IZfYzg5Go';
$Push3_pushpriv = 'ZrdiwLJKMNMQv0PGeHpsF-1UCucemkWKEJ4PrhjKYXQ';


$GLOBALS['Push3_path'] = $pageroot;
$GLOBALS['Push3_adminfile'] = 'index.php';
$GLOBALS['Push3_req'] = array_merge($_GET,$_POST);

$zd = $GLOBALS['Push3_req'];


require_once "not.php";

function Push3_redirect($filename) {
    if (!headers_sent())
        header('Location: '.$filename);
    else {
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$filename.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$filename.'" />';
        echo '</noscript>';
    }
}


function Push3_guidv4()
{
    if (function_exists('com_create_guid') === true)
        return trim(com_create_guid(), '{}');

    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function Push3_QQZ($dbs,$q,$arr)
{
    global $push3_lrid;
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
    $push3_lrid = $dbs->lastInsertRowID();
    return $a;
}

function Push3_QQ($q,$arr = array())
{
	global $Push3_db;
    if (!is_array($arr))
    {
        //   debug_print_backtrace();
        die("QQ passed not an array.");
    }
    return Push3_QQZ($Push3_db,$q,$arr);
}

function Push3_Send($msg,$arr = array())
{
    $c = 0;
    if (count($arr) == 0) 
      return $c;
      global $Push3_pushpub;
      global $Push3_pushpriv;

      $a = array();
      foreach($arr as $id)
      {
        $q2 = Push3_QQ("SELECT * FROM PUSHING WHERE CLSID = ?",array($id));
        while($r2 = $q2->fetchArray())
        {
            $qe = $r2['STR'];
            $sub = json_decode($qe,true);
            $auth = $sub['keys']['auth'];
            $p256dh = $sub['keys']['p256dh'];
            if (!$sub)
                continue;
            if (!$sub['endpoint'])
                continue;
  
              $aa = array(      'endpoint' => $sub['endpoint'],
          'publicKey' => $p256dh,
          'authToken' => $auth,
          'contentEncoding' => 'aesgcm');
              array_push($a,$aa);
  
            $c++;

        }
      }


      try
      {
          $res = NOT_PushMultiple2($a,$GLOBALS['Push3_path'].'/push3.php',$Push3_pushpub,$Push3_pushpriv,$msg);
          return $c;         
      }
      catch(Exception $e)
      {
          return 0;
      } 

}

function Push3_ShowScripts($id = 0,$output = 0)
{
    global $Push3_pushpub;
    NOT_Scripts();
    ?>
    <script>

        var clsid = '<?= Push3_guidv4() ?>';
        var pub = '<?= $Push3_pushpub ?>';
        var sub = null;
        var ru = 'push3.php?noti=<?= $id ?>';

        function askpush()
        {
        ha = "<?= sprintf("%s/push3.php?pushscript=",$GLOBALS['Push3_path']) ?>" + clsid;
        AskNotification("",ru,ha,pub);
        }


function sendpush(cid,msg)
{
    $.ajax({
            type: "GET",
            url: "push3.php",
            data: { targets:cid, send:msg },
            success: function(ddd)
                    {
                    }
        });
}

function disablepush(cid)
{
    var ep = '';
    if (sub)
        {
            ep = sub.endpoint;
            sub.unsubscribe();
        }
    window.location = '<?= "push3.php" ?>?deletepush=' + cid + '&ep=' + ep;
}


$(document).ready(
        function()
        {
            $('#enablesubs').show();
            if ('serviceWorker' in navigator && 'PushManager' in window) {
                navigator.serviceWorker.register('push3.php?pushscript')
                .then(function(swReg) {
                    sw = swReg;
                    swReg.pushManager.getSubscription()
                        .then(function(subscription) {
                            curser = JSON.stringify(subscription);
                            sub = subscription;
                            if (sub)
                                $("#notify_already_there").show();
                            else
                                {
                                    if (Notification.permission == "blocked" || Notification.permission == "denied")
                                       $("#notify_disabled").show();
                                    else
                                       $("#notify_enable_button").show();
                                }
                            $('.divs').each(
                                function(index)
                                {
                                    var te = JSON.stringify($(this).data("text"));
                                    if (te == curser)
                                    {
                                        $(this).show();
                                        $('#enable').hide();
                                    }
                                }
                                );


                        });
                })
            } else {
            }

        });


</script>
<?php
}

function Push3_ShowOptions($id = 0,$output = 0,$style = 0)
{  
$str = sprintf('
<div id="notify_div" class="content" style="margin: 20px">
<div id="notify_enable_button" style="display:none;" >Μπορείτε να ενεργοποιήσετε push notifications για αυτόν τον browser.<br><button class="btn btn-primary button is-primary" onclick="askpush();">Ενεργοποίηση</button></div>
<div id="notify_already_there" style="display:none;">Push Notifications Ενεργοποιημένες<br><button class="btn button btn-success is-success" onclick="sendpush(\'%s\',\'Αυτό είναι ένα δοκιμαστικό μήνυμα\');">Δοκιμή</button> <button class="btn btn-danger button is-danger" onclick="disablepush(\'%s\');">Απενεργοποίηση</button></div>
<button id="notify_disabled" style="display:none;" class="btn button btn-warning is-warning" >Οι ειδοποιήσεις έχουν απαγορευτεί σε αυτόν τον Browser</button>
</div>',$id,$id);
if ($output)
{
$str .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/1.0.0/css/bulma.min.css" integrity="sha512-+oEiKVGJRHutsibRYkkTIfjI0kspDtgJtkIlyPCNTCFCdhy+nSe25nvrCw7UpHPwNbdmNw9AkgGA+ptQxcjPug==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';
}
return $str;
}

if (array_key_exists("send",$GLOBALS['Push3_req']))
{
    echo Push3_Send($GLOBALS['Push3_req']['send'],explode(",",$GLOBALS['Push3_req']['targets']));
    return;
}
else
if (isset($push3_admin))
{
}
else
if (array_key_exists("pushvalue",$GLOBALS['Push3_req']))
{
    $s = sprintf("%s/%s",$GLOBALS['Push3_path'],$GLOBALS['Push3_adminfile']);
   if ($GLOBALS['Push3_req']['pushvalue'] == "granted")
    {
        $noti = json_decode($GLOBALS['Push3_req']['str'],true);
        Push3_QQ("INSERT INTO PUSHING (STR,CLSID,ENDPOINT) VALUES(?,?,?)",array($GLOBALS['Push3_req']['str'],$GLOBALS['Push3_req']['noti'],$noti['endpoint']));
        echo $s;
    }
    else
    {
        Push3_redirect($s);
    }
    die; // OK
}
else
if (array_key_exists("deletepush",$GLOBALS['Push3_req']))
{
    Push3_QQ("DELETE FROM PUSHING WHERE CLSID = ? AND ENDPOINT = ?",array($GLOBALS['Push3_req']['deletepush'],$GLOBALS['Push3_req']['ep']));
    Push3_redirect(sprintf("%s/%s",$GLOBALS['Push3_path'],$GLOBALS['Push3_adminfile']));
    die; // OK
}
else
{
// Endpoint for Pushing
header("Content-Type: text/javascript");
$pc = '';
if (array_key_exists("pushscript",$GLOBALS['Push3_req']))
    $pc = $GLOBALS['Push3_req']['pushscript'];
?>
'use strict';
<?php
    printf("var pu = '%s';\r\n",$pc);
    printf("var schname = '%s';\r\n",'Μητρώο Προσόντων και Διαγωνισμών');
    printf("var url = '%s/%s';\r\n",$GLOBALS['Push3_path'],$GLOBALS['Push3_adminfile']);

?>
self.addEventListener('push', function(event) {
  console.log('[Service Worker] Push Received.');
  console.log(`[Service Worker] Push had this data: "${event.data.text()}"`);
  const title = schname;
  const options = {
    body: event.data.text(),
    icon: 'icon.png',
    badge: 'i9.png'

  };
  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
  console.log('[Service Worker] Notification click Received.');

  event.notification.close();

  event.waitUntil(
    clients.openWindow(url)
  );
});

<?php
}