<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/defaults.php';

if (check_sketch() > 5) {
    header('Location: /blocked_ip.php');
    error('Your IP is blocked, contact the server admin.', 401);
}

// Import MongoDB library
require_once $_SERVER['DOCUMENT_ROOT'] . '/Mongo/lib_mongo.php';

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
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/StyleSheets/Computer.css">
    <link rel="stylesheet" href="/Font/css/all.css">
    <title>Login</title>
    <script src="/JavaScript/jquery.js"></script>
    <script>
        document.addEventListener('keyup', function (event) {
            if (event.keyCode === 13) {
                if(!illegal_password()) {
                    $('#submit').click();
                }
            }
        })

        function illegal_password () {
            let password = $('#password').val();
            let verify = $('#verify').val();
            if (password !== verify) {
                return true;
            }

            if (password === '') {
                return true;
            }

            return false;
        }

        function checkpassword(){
            $('#submit').prop("disabled", illegal_password());
        }
    </script>
</head>
<body>
<div class="title">
    Create User
</div>
<div class="createuser">
    <form action="/api/createuser.php" method="post" enctype="multipart/form-data">
        <input type="text" id="username" name="username" class="createuserinput" placeholder="Username" required>
        <input type="password" id="password" name="password" class="createuserinput" placeholder="Password" onchange="checkpassword()" required>
        <input type="password" id="verify" name="verify" class="createuserinput" placeholder="Verify password" onchange="checkpassword()" required>
        <input type="file" id="image" name="image" accept="image/png" class="createuserinput" required>
        <input type="submit" id="submit" class="createuserinput" value="Create User" disabled>
    </form>
</div>
</body>
</html>