<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/defaults.php';

if (check_sketch() > 5) {
    header('Location: /blocked_ip.php');
    error('Your IP is blocked, contact the server admin.', 401);
}

// Import MongoDB library
require_once $_SERVER['DOCUMENT_ROOT'] . '/Mongo/lib_mongo.php';
$config = (require $_SERVER['DOCUMENT_ROOT'] . '/api/config.php');

$result = mongo_find('users') ?? [];

if (count($result) > 0) {
    if(!isset($_SESSION['username'])) {
        sketchy_ip();
        header('Location: /login');
        exit();
    }

    if (!($_SESSION['admin'] ?? false)) {
        error('You are not allowed to do this', 401);
    }
}

$uploadStatus= $_FILES['image']['error'];

switch ($uploadStatus){
    case UPLOAD_ERR_OK:
        $username = trim(strip_tags($_POST['username'] ?? ''));
        $password = $_POST['password'];
	
        $result = mongo_find('users', ['username' => $username]) ?? [];

        if (count($result) > 0) {
            error('Username already exists', 400);
        }

        $path_parts = pathinfo($_FILES["image"]["name"]);
        $extension = $path_parts['extension'];

        if($extension !== 'png') {
            error('This is not a png image');
        }

        copy($_FILES["image"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . '/profile_pictures/' . $username . '.png');

        $password = hash_hmac('sha256', $password, $config['pepper']);
        $password = password_hash($password, PASSWORD_ARGON2ID);

        $result = mongo_find('users') ?? [];
        $admin = (count($result) < 1);
        mongo_insert_one('users', ['username' => $username, 'password' => $password, 'admin' => $admin]);

        header('Location: /');
        exit();
    default:
        error('Error Uploading File');
        break;
}
