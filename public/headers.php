<?php
header('Content-Type: text/plain');
echo "=== REQUEST HEADERS ===\n\n";
foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}

echo "\n=== SERVER VARIABLES ===\n\n";
foreach ($_SERVER as $name => $value) {
    if (strpos($name, 'HTTP_') === 0 || in_array($name, ['HTTPS', 'SERVER_PROTOCOL', 'REMOTE_ADDR', 'SERVER_PORT', 'REQUEST_SCHEME'])) {
        echo "$name: $value\n";
    }
}
