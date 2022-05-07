<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/defaults.php';

if (check_sketch() > 5) {
    header('Location: /blocked_ip.php');
    error('Your IP is blocked, contact the server admin.', 301);
}

// Import MongoDB library
require_once $_SERVER['DOCUMENT_ROOT'] . '/Mongo/lib_mongo.php';

$username = urldecode($_GET['username']);
?>
<!DOCTYPE html>
<html>
<head>
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';
    ?>
    <link rel="stylesheet" href="/Font/css/all.css">
    <script src="/JavaScript/jquery.js"></script>
    <title>Login</title>
</head>
<body>
<a class="backarrow" href="/login">
    <i class="fas fa-arrow-left"></i>
</a>
<div class="titleuser">
    <?php
        echo $username;
    ?>
</div>

<div class="users">
    <?php
    $html = '<div class="profile">';
    $html .= sprintf('<img class="profile_pic" src="/profile_pictures/%s.png">', $username);
    $html .= '<input class="loginPassword" type="password" placeholder="Password">';
    $html .= '<button data-username="' . $username . '" class="loginButton" onclick="login()">Login</button>';
    $html .= '</div>';
    echo $html;
    ?>

</div>
</body>
    <script>
        document.addEventListener('keyup', function (event) {
            if (event.keyCode === 13) {
                login();
            }
        })

        function login () {
            let username = $('.loginButton').attr('data-username');
            let password = $('.loginPassword').val();
            $.ajax({
                url: '/api/login.php',
                method: 'POST',
                data: {
                    username: username,
                    password: password
                },
                success: function(data) {
                    window.location.href = '/';
                },
                error: function(data) {
                    if (data.status === 402) {
                        window.location.href = '/blocked_ip.php';
                        return;
                    }
                    let passwordelement = $('.loginPassword');
                    passwordelement.val('');
                    passwordelement.toggleClass('error');
                    setTimeout(function () {
                        passwordelement.toggleClass('error');
                    }, 1000);
                }
            });
        }
    </script>
</html>