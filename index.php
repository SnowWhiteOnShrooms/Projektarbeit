<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/defaults.php';

if (check_sketch() > 5) {
    header('Location: /blocked_ip.php');
    error('Your IP is blocked, contact the server admin.', 401);
}

if(!isset($_SESSION['username'])) {
    sketchy_ip();
    header('Location: /login');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/head.php';
    ?>
    <link rel="stylesheet" href="/Font/css/all.css">
    <script src="/JavaScript/jquery.js"></script>
    <script src="/JavaScript/main.js"></script>
    <title>Homepage</title>
</head>
<body>
    <div class="topnav">
        <div class="topleftnav">
            <?php
                $type = $_GET['f'] ?? 'home';
                echo '<a class="navitem' . (($type !== 'series' && $type !== 'movies') ? ' active' : '') . '"  href="/">Home</a>';
                echo '<a class="navitem' . ($type === 'series' ? ' active' : '') . '" href="/?f=series">Series</a>';
                echo '<a class="navitem' . ($type === 'movies' ? ' active' : '') . '" href="/?f=movies">Movies</a>';
            ?>
        </div>
        <div class="topmiddlenav">
            <div class="searchbar">
                <i class="fas fa-times" onclick="clearSearch()"></i>
                <?php
                echo sprintf('<input type="text" placeholder="Search..." name="search" class="search" value="%s">', $_SESSION['last_search']);
                ?>
                <button class="searchbutton" onclick="search();"><i class="fa fa-search"></i></button>
            </div>
        </div>
        <div class="toprightnav">
            <button class="navitem" onclick="upload_new_movies(this)">Upload</button>
            <button class="navitem" onclick="logout()">Logout</button>
            <?php
                echo sprintf('<img class="logged_in_user" src="/profile_pictures/%s.png">', $_SESSION['username']);
            ?>
        </div>
    </div>
    <div class="movietable">
        <span>No Results</span>
    </div>
</body>

<script src="/JavaScript/home.js"></script>
</html>
