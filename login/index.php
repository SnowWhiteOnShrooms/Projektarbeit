<?php
// Import MongoDB library
require_once $_SERVER['DOCUMENT_ROOT'] . '/Mongo/lib_mongo.php';

$results = mongo_find('users');
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
    <div class="title">
        Who's watching?
    </div>

    <div class="users">
        <?php
            foreach ($results as $result) {
                $html = '<div class="profile">';
                $html .= sprintf('<img onclick="selectUser(this)" class="profile_pic" src="/profile_pictures/%s.png">', $result['username']);
                $html .= sprintf('<span class="username">%s</span>', $result['username']);
                $html .= '</div>';
                echo $html;
            }
        ?>
    </div>
</body>
<script>
    function selectUser (element) {
        let username = $(element).siblings('.username').html();
        let url = '/login/userlogin.php?username=' + username;
        window.location.href = encodeURI(url);
    }

</script>
</html>