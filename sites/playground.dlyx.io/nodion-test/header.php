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

// Erweiterte Suche nach Geo-, IP- und Standort-bezogenen Server-Variablen
$geoPatterns = [
    'IP', 'COUNTRY', 'CITY', 'REGION', 'STATE', 'PROVINCE',
    'LAT', 'LON', 'LONG', 'GEO', 'LOC', 'FORWARD',
    'CONTINENT', 'TIMEZONE', 'POSTAL', 'ZIP', 'METRO',
    'ASN', 'ORG', 'ISP', 'PROXY', 'REAL', 'REMOTE',
    'GEOIP', 'MM_', 'MAXMIND', 'CF_', 'CDN', 'EDGE',
    'X_APPENGINE', 'X_AWS', 'X_AZURE', 'X_GCP',
    'FLYIO', 'VERCEL', 'NETLIFY', 'AKAMAI',
];

foreach ($_SERVER as $key => $val) {
    $keyUpper = strtoupper($key);
    foreach ($geoPatterns as $pattern) {
        if (strpos($keyUpper, $pattern) !== false) {
            echo str_pad($key . ":", 40) . $val . "\n";
            break;
        }
    }
}


echo "\n\n=== KOMPLETTES \$_SERVER ARRAY ===\n\n";

// Fallback: Alle $_SERVER Variablen ausgeben, damit nichts übersehen wird
foreach ($_SERVER as $key => $val) {
    if (is_string($val)) {
        echo str_pad($key . ":", 40) . $val . "\n";
    }
}
?>