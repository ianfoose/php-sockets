#!/usr/bin/env php
<?php
error_reporting(E_ALL);

// Set your timezone here
date_default_timezone_set('UTC');

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

$address = '127.0.0.1';
$port = 4000;

// server started
if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "socket_create() failed: " . socket_strerror(socket_last_error()) . "\n";
} else {
    echo "Server started on ".date("Y-m-d g:i:a",time())."\n";
}

// socket connect
if (socket_bind($sock, $address, $port) === false) {
    echo "socket_bind() failed: " . socket_strerror(socket_last_error($sock)) . "\n";
}

// socket error
if (socket_listen($sock, 5) === false) {
    echo "socket_listen() failed: " . socket_strerror(socket_last_error($sock)) . "\n";
}

//clients array
$clients = array();

do {
    $read = array();
    $read[] = $sock;
    $write = null;
    $except = null;

    $read = array_merge($read,$clients);
    
    // Set up a blocking call to socket_select
    if(socket_select($read,$write, $except, $tv_sec = 5) < 1) {
        //    SocketServer::debug("Problem blocking socket_select?");
        continue;
    }
    
    // Handle new Connections
    if (in_array($sock, $read)) {        
        if (($msgsock = socket_accept($sock)) === false) {
            echo "socket_accept() failed: " . socket_strerror(socket_last_error($sock)) . "\n";
            break;
        }

        $clients[] = $msgsock;
        $key = array_keys($clients, $msgsock);
        
        echo "Client Connected\n";

         /* Send instructions. */
        $msg = "\nWelcome to the PHP Socket Server. \n" .
        "To quit, type 'quit'.\n";

        socket_write($msgsock, $msg, strlen($msg));
    }
    
    // Handle Input
    foreach ($clients as $key => $client) { // for each client        
        if (in_array($client, $read)) {
           if (false === (@$buf = socket_read($client, 2048, PHP_NORMAL_READ))) {
               // echo "socket_read() failed: " . socket_strerror(socket_last_error($client)) . "\n";
                echo "Client {$key} Disconnected\n";
                unset($clients[$key]);
                socket_close($client);
            }
            if (!$buf = trim($buf)) {
                continue;
            }
            if ($buf == 'quit') {
                unset($clients[$key]);
                socket_close($client);
                break;
            }

            $talkback = "Client {$key}: said '$buf'.\n";
            socket_write($client, $talkback, strlen($talkback));
            echo $talkback;
        }
    }        
} while (true);

socket_close($sock);
?>