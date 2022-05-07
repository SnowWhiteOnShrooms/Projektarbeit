<?php
session_start();
$config = (require $_SERVER['DOCUMENT_ROOT'] . '/api/config.php');

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/defaults.php';

if (check_sketch() > 5) {
    header('Location: /blocked_ip');
    error('Your IP is blocked, contact the server admin.', 401);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/Mongo/lib_mongo.php';

$password = $_POST['password'];
$username = $_POST['username'];

$result = mongo_find('users', ['username' => $username]) ?? [];

if (count($result) < 1) {
    sketchy_ip();
    error('Username not found!', 401);
}

$result = $result[0];

$password = hash_hmac("sha256", $password, $config['pepper']);
$password_db = $result['password'];

if (password_verify($password, $password_db)) {
    $_SESSION['username'] = $username;
    $_SESSION['admin'] = $result['admin'] ?? false;

    $ip = get_ip_address();

    $result = mongo_find('connected_ips', ['ip_address' => $ip], array('typeMap' => array('array'=>'array', 'document'=>'array', 'root'=>'array')));

    if (count($result) > 0) {
        mongo_update_one('connected_ips', ['ip_address' => $ip], ['$set' => ['sketch_level' => 0]]);
    } else {
        mongo_insert_one('connected_ips', ['ip_address' => $ip, 'sketch_level' => 0]);
    }

    echo 'Logged in.';
} else {
    sketchy_ip();
    error('Password do not match!', 401);
}
