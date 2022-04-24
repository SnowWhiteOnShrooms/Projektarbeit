<?php
session_start();
if(!isset($_SESSION['username'])) {
    header('Location: /login');
    exit();
}
// Import MongoDB library
require_once $_SERVER['DOCUMENT_ROOT'] . '/Mongo/lib_mongo.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/defaults.php';

$id = trim(strip_tags($_GET['id']));
// $season = trim(strip_tags($_GET['s']));
// $episode = trim(strip_tags($_GET['e']));
$result = mongo_find('movies', ['_id' => new MongoDB\BSON\ObjectId($id)], array('typeMap' => array('array'=>'array', 'document'=>'array', 'root'=>'array')))[0];

$time = $result['time'][$_SESSION['username']]['time'];
$type = $result['type'];
if ($type === 'serie') {
    $seasons = array_keys($result['data']);
    usort($seasons, 'number_val_sorting');
    $season = $result['time'][$_SESSION['username']]['season'] ?? $seasons[0];
    $episodes = array_keys($result['data'][$season]);
    usort($episodes, 'number_val_sorting');
    $episode = $result['time'][$_SESSION['username']]['episode'] ?? $episodes[0];
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
    <title>
        <?php
        if($type == 'movie'){
            echo $result['name'];
        } else {
            echo sprintf('%s, %s, %s', $result['name'], $season, $episode);
        }
        ?>
    </title>
</head>
<body>
<div class="topnav">
    <div class="topleftnav">
        <a class="navitem" href="/">Home</a>
        <a class="navitem" href="/?f=series">Series</a>
        <a class="navitem"  href="/?f=movies">Movies</a>
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
<div class="moviepage">
    <?php
    echo sprintf('<video class="movieplayer" id="%s" controls><source type="video/mp4" src="', $id);
    if($type == 'movie'){
        echo '/Data/' . $id . '/movie.' . $result['movie_format'];
    } else {
        echo sprintf('/Data/%s/%s/%s.%s', $id, $season, $episode, $result['data'][$season][$episode]['format']);
    }
    ?>">
        Your browser does not support the video tag.
    </video>
    <div class="movieheader">
        <?php
        if($type == 'movie'){
            echo $result['name'];
        } else {
            echo sprintf('%s - %s - %s', $result['name'], $season, $episode);
        }
        ?>
    </div>
    <?php
    if($type == 'serie'){
        $html = '<div class="selector"><div class="seasons"><i class="fas fa-chevron-down"></i><select class="season" onchange="selectSeason()">';
        foreach ($seasons as $s) {
            $html .= sprintf('<option%s>%s</option>', ($s == $season ? ' selected': ''), $s);
        }
        $html .= '</select></div>';
        foreach ($seasons as $s) {
            $html .= sprintf('<div class="episodes" id="%s" style="display: %s;">', $s, ($s == $season ? 'block': 'none'));
            $ep = array_keys($result['data'][$s]);
            usort($ep, 'number_val_sorting');
            foreach ($ep as $e) {
                $html .= sprintf('<div onclick="selectEpisode(this);" class="episode%s">%s</div>', (($s == $season && $e == $episode) ? ' active_episode': ''), $e);
            }
            $html .= '</div>';
        }
        $html .= '</div></div>';
        echo $html;
    }
    ?>
</div>
</body>
    <script src="/JavaScript/watch.js"></script>
    <?php
        $html = sprintf('<script>document.getElementsByClassName("movieplayer")[0].currentTime = %s;', $time ?? 0);
        if($type === 'serie'){
            $html .= sprintf('var episode = "%s";', $episode);
            $html .= sprintf('var season = "%s";', $season);
            $html .= 'serie = true;';
            $html .= 'roundUp = 60;';
        }
        $html .=  '</script>';
        echo $html;
    ?>
</html>