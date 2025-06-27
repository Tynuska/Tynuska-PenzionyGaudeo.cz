<?php
// Weblink 3 (WL3) skript pro integraci do stránek klienta
// 2009 – 2020 © ABX software s.r.o.

//--- konfigurace pro připojení k ABX Weblink 3 serveru ---
//--- zde nastavte ---

$wl3numid = 63;         // WL3 numerické id, např. 55
$wl3id    = 'gAU26as97Q';         // WL3 uživatelské jméno, např. 'alcron'
$wl3pwd   = 'VR45pl781D';         // WL3 heslo

$wl3host  = 'hotelovysystem.eu';  // WL3 host, zmenit jen ve specialnich pripadech!
$wl3back  = '/WL301/back';        // kde je na $wl3host WL3 server, zmenit jen ve specialnich pripadech!

//--- konfigurace vzhledu ---

// Volitelné téma a favicon. Ponechat prázdné ('') pokud se má použít default.
$theme   = 'https://penzionygaudeo.cz/wl3.css';            //jinak se pouzije https://hotelovysystem.eu/WL301/themes/default.301.css';
$favicon = '';            //jinak se pouzije https://hotelovysystem.eu/WL301/themes/favicon.301.png';

// výchozí jazyk (jedno z: 'cs', 'en', 'de')
// možné předat v iframe (?lang=en), pokud nepředaný, použít jazyk definovaný zde:
if (! ($lang = @$_REQUEST['lang']))
  $lang = 'cs';

//--- konfigurace Mini Recepce (R3 služba) ---

$r3host  = '193.86.2.226';  // IP adresa R3 služby (veřejná adresa počítače s R3 daty)
$r3port  = 5555;               // port R3 služby (nastavte v konfiguraci programu WL2.exe, port otevrit v routeru)
$r3id    = 'gAU26as97Q';            // uživatelské id pro přihlášení do R3 služby (nastavte v konfiguraci programu WL2.exe)
$r3pwd   = 'VR45pl781D';           // heslo pro přihlášení do R3 služby (nastavte v konfiguraci programu WL2.exe)

//--- Konec konfigurace ---
//--- Dál už nic neměňte! ---

// unique session name (to allow simultaneous WL3 sessions in window tabs)
$sessName = uniqid('WL3-');

// WL3 server location
$wl3root = "https://$wl3host$wl3back";
// this script for reload
$wl3php  = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

// login to WL3 server
$options = array('http' => array(
  'method'  => 'POST',
  'header'  => "Host: $wl3host\r\nContent-type: application/x-www-form-urlencoded\r\nConnection: Close\r\n",
  'content' => http_build_query(array(
    'sn' => $sessName, 'wl3php' => $wl3php,
    'wl3numid' => $wl3numid, 'wl3id' => $wl3id, 'wl3pwd' => $wl3pwd,
    'r3host' => $r3host, 'r3port' => $r3port, 'r3id' => $r3id, 'r3pwd' => $r3pwd,
    'theme' => $theme, 'favicon' => $favicon, 'lang' => $lang,
  ))
));

if ( ! (@$fp = fopen("$wl3root/login.php", 'r', false, stream_context_create($options)))) {
  echo "ABX Weblink 3 server is not available";
  die;
}

// server replies:
$line = trim(fgets($fp));
if ('=' != substr($line, 0, 1)) {
  // returned an error message
  echo $line;
  die;
}

// Successful login, the server returned a session id. Redirect to WL3 server pages.
$options['http']['content'] = http_build_query(array(
  'sn' => $sessName, 'sid' => substr($line,1),
));

if ( ! (@$fp=fopen("$wl3root/redirect.php", 'r', false, stream_context_create($options)))) {
  echo "ABX Weblink 3 pages are not available";
  die;
}

// Server returns the HTML of the first page:
echo @stream_get_contents($fp);
die;

// eof
