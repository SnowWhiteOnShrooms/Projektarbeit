<?php
session_start();
$config = (require $_SERVER['DOCUMENT_ROOT'] . '/api/config.php');

require_once $_SERVER['DOCUMENT_ROOT'] . '/Mongo/lib_mongo.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/defaults.php';

$password = $_POST['password'];
$username = $_POST['username'];

$result = mongo_find('users', ['username' => $username]) ?? [];

if (count($result) < 1) {
    error('Username not found!', 401);
}

$result = $result[0];

$password = hash_hmac("sha256", $password, $config['pepper']);
$password_db = $result['password'];

if (password_verify($password, $password_db)) {
    $_SESSION['username'] = $username;
    $_SESSION['admin'] = $result['admin'] ?? false;
    echo 'Logged in.';
}
else {
    error('Password do not match!', 401);
}

