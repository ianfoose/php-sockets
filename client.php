#!/usr/local/bin/php -q
<?php
error_reporting(E_ALL);

/* Get the port for the WWW service. */
$service_port = getservbyname('www', 'tcp');

$service_port = 4000;

/* Get the IP address for the target host. */
$address = gethostbyname('127.0.0.1');

/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
}

echo "Attempting to connect to '$address' on port $service_port...";
$result = socket_connect($socket, $address, $service_port);
if ($result === false) {
    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
} else {
    echo "\nConnected.\n";
}

$out = '';

while($f = fgets(STDIN)){
    socket_write($socket, $f, strlen($f));
}

echo "Reading response:\n\n";
while ($out = socket_read($socket, 2048, PHP_NORMAL_READ)) {
    echo $out;
}

echo "Closing socket...";
socket_close($socket);
echo "OK.\n\n";
?>