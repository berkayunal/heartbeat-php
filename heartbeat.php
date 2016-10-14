<?php

$conf['mysql'][] = array("hostname" => "localhost", "db" => "heartbeat", "username" => "root", "password" => "root", "table" => "test");
$conf['memcache'][] = array("hostname" => "localhost", "port" => "11211");
$conf['redis'][] = array("hostname" => "localhost", "port" => "6379");
$conf['file'][] = array("path" => "heartbeat_donotdelete.txt");

$errors = array();

/**
 * Check if DB is active
 */

if (is_array($conf['mysql']) && count($conf['mysql']) > 0) {
    foreach ($conf['mysql'] as $k => $v) {
        $_hostname = $v['hostname'];
        $_db = $v['db'];
        $_username = $v['username'];
        $_password = $v['password'];
        $_table = $v['table'];

        $db = mysqli_connect($_hostname, $_username, $_password, $_db);
        if (!$db || mysqli_connect_errno()) {
            $errors[] = 'Connection error DB: ' . $_hostname . " - " . $_db;
        } else {
            if ($result = mysqli_query($db, "SELECT * FROM " . $_table . " LIMIT 10")) {
                if (mysqli_num_rows($result) > 0) {
                    mysqli_free_result($result);
                } else {
                    $errors[] = 'Cannot fetch results error: ' . $_hostname . " - " . $_db . " - " . $_table;
                }
            } else {
                $errors[] = 'Cannot fetch results error: ' . $_hostname . " - " . $_db . " - " . $_table;
            }
        }
    }
}

/**
 * Check if Memcache is active
 */

if (is_array($conf['memcache']) && count($conf['memcache']) > 0) {
    foreach ($conf['memcache'] as $k => $v) {
        $_hostname = $v['hostname'];
        $_port = $v['port'];
        if (!memcache_connect($_hostname, $_port)) {
            $errors[] = 'Memcache is not available: ' . $_hostname . " - " . $_port;
        }
    }
}

/**
 * REDIS CHECK
 */

if (is_array($conf['redis']) && count($conf['redis']) > 0) {
    foreach ($conf['redis'] as $k => $v) {
        $_hostname = $v['hostname'];
        $_port = $v['port'];

        $redis = new Redis();
        $status = $redis->connect($_hostname, $_port);
        if (!$status) {
            $errors[] = 'Redis is not available: ' . $_hostname . " - " . $_port;
        }
    }
}

/**
 * FILE CHECK
 */

if (is_array($conf['file']) && count($conf['file']) > 0) {
    foreach ($conf['file'] as $k => $v) {
        $_path = $v['path'];
        if (!file_exists($_path)) {
            $errors[] = 'File is not available: ' . $_path;
        }
    }
}


if ($errors) {
    http_response_code(500);
    print('Errors on this server<br>');
    print implode("<br/>", $errors);
    print "<br>";
    print("TIME: " . time());
} else {
    http_response_code(200);
    print 'OK' . ' 200' . "<br>";
    print("TIME: " . time());
}

exit();
