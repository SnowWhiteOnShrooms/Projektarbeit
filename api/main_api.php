<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/defaults.php';

if (check_sketch() > 5) {
    header('Location: /blocked_ip');
    error('Your IP is blocked, contact the server admin.', 401);
}

if(!isset($_SESSION['username'])) {
    sketchy_ip();
    header('Location: /login');
    exit();
}

// Import MongoDB library
require_once $_SERVER['DOCUMENT_ROOT'] . '/Mongo/lib_mongo.php';

// Check if a action was requested
if (empty($_POST['action'])) {
    error('The action field has to be set.');
}

// Perform Actions
switch ($_POST['action']) {
    case 'savetime':
        $time = ['time' => $_POST['time']];
        if (isset($_POST['episode'])) {
            $time['episode'] = $_POST['episode'];
            $time['season'] = $_POST['season'];
        }
        if ($time < 10) {
            $time = 0;
        }
        $id = new MongoDB\BSON\ObjectId($_POST['id']);

        mongo_update_many('movies', ['_id'=> $id], ['$set' => [sprintf('time.%s', $_SESSION['username']) => $time]]);
        break;
    case 'logout':
        setcookie(session_name(), '', 100);
        session_unset();
        session_destroy();
        $_SESSION =[];
        echo 'Logged out.';
        exit();
    case 'get_movies':
        $search = $_POST['search'];
        if (!isset($search)) {
            $search = $_SESSION['last_search'];
        }
        $search = trim(strip_tags($search ?? ''));
        $_SESSION['last_search'] = $search;
        $filter = trim(strip_tags($_POST['filter'] ?? ''));
        $f = ['name' => ['$regex' => sprintf('.*%s.*', str_replace(' ', '.*', $search)), '$options'=>'i']];
        if ($filter === 'Movies') {
            $f['type'] = 'movie';
        } elseif ($filter === 'Series') {
            $f['type'] = 'serie';
        }
        $movies = mongo_find('movies', $f, array('sort' => ['importDate' => 1], 'typeMap' => array('array'=>'array', 'document'=>'array', 'root'=>'array')));
        $data = [];
        foreach ($movies as $movie) {
            $d = [
                '_id' => (string) $movie['_id'],
                'name' => $movie['name'],
                'thumbnail_format' => $movie['thumbnail_format']];
            $time = $movie['time'][$_SESSION['username']];
            if ($movie['type'] === 'movie') {
                $d['duration'] = $movie['duration'];
            } elseif ($movie['type'] === 'serie') {
                $seasons = array_keys($movie['data']);
                usort($seasons, 'number_val_sorting');
                $first_season = $seasons[0];
                $episodes = array_keys($movie['data'][$first_season]);
                usort($episodes, 'number_val_sorting');
                $first_episode = $episodes[0];
                $d['duration'] = $movie['data'][$time['season'] ?? $first_season][$time['episode'] ?? $first_episode]['duration'];
            }
            $d['time'] = $time['time'] ?? 0;
            $data[] = $d;
        }

        echo json_encode(['movies' => $data]);
        break;
    case 'scanForNewMovies':
        $imported_count = 0;
        $imported_count += uploadMovies();
        $imported_count += uploadSeries();
        echo $imported_count;
        break;
    default: // If no valid action is set.
        error('No such action exists.');
        break;
}

