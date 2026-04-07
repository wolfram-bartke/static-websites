<?php
// Setze den Content-Type auf reinen Text, damit die Ausgabe im Browser schön formatiert bleibt
header('Content-Type: text/plain; charset=utf-8');

echo "=== ALLE EINGEHENDEN HTTP-HEADER ===\n\n";

$headers = [];

// Variante 1: Standard-Funktion (falls vom Webserver unterstützt)
if (function_exists('getallheaders')) {
    $headers = getallheaders();
} else {
    // Variante 2: Sicherer Fallback über das $_SERVER Array
    foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            // Formatiert 'HTTP_X_FORWARDED_FOR' zu 'X-Forwarded-For'
            $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
            $headers[$headerName] = $value;
        }
    }
}

// Header alphabetisch sortieren für eine bessere Übersicht
ksort($headers);

// Ausgabe der Header
foreach ($headers as $key => $val) {
    echo str_pad($key . ":", 30) . $val . "\n";
}


echo "\n\n=== RELEVANTE SERVER-VARIABLEN (Zusatz-Check) ===\n\n";

// Manchmal verstecken Cloud-Provider IPs oder Geo-Daten in eigenen Server-Variablen
$relevantKeys = ['REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CF_IPCOUNTRY'];

foreach ($_SERVER as $key => $val) {
    // Wir filtern nur nach typischen Proxy-, Geo- und IP-Variablen
    if (strpos($key, 'IP') !== false || strpos($key, 'COUNTRY') !== false || strpos($key, 'FORWARD') !== false || in_array($key, $relevantKeys)) {
        echo str_pad($key . ":", 30) . $val . "\n";
    }
}
?>