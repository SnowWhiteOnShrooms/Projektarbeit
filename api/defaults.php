<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Mongo/lib_mongo.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/getid3/getid3.php';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

function error($message, $code = 400) // Error handling code
{
    http_response_code($code);
    echo json_encode(array('status' => $code == 500 ? 'server error' : 'error', 'message' => $message), JSON_PRETTY_PRINT);
    exit(1);
}

function get_ip_address() {
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

function check_sketch() {
    $ip = get_ip_address();

    $result = mongo_find('connected_ips', ['ip_address' => $ip], array('typeMap' => array('array'=>'array', 'document'=>'array', 'root'=>'array')));

    if (count($result) > 0) {
        return $result[0]['sketch_level'];
    } else {
        return 0;
    }
}

function sketchy_ip() {
    $ip = get_ip_address();
    $result = mongo_find('connected_ips', ['ip_address' => $ip], array('typeMap' => array('array'=>'array', 'document'=>'array', 'root'=>'array')));

    if (count($result) > 0) {
        mongo_update_one('connected_ips', ['ip_address' => $ip], ['$set' => ['sketch_level' => ($result[0]['sketch_level'] + 1)]]);
    } else {
        mongo_insert_one('connected_ips', ['ip_address' => $ip, 'sketch_level' => 1]);
    }
}

function number_val_sorting($value_A, $value_B) {
    $val_A = intval(preg_replace('/\D/', '', $value_A));
    $val_B = intval(preg_replace('/\D/', '', $value_B));
    if ($val_A == $val_B) {
        return strcmp($value_A, $value_B) > 0;
    } else {
        return $val_A > $val_B;
    }
}

function uploadMovies () {
    ini_set('max_execution_time', 300);
    $path = $_SERVER['DOCUMENT_ROOT']; // Path to upload folder
    $imported_count = 0;

    if ($handle = opendir($path . '/Data/Upload/Movies')) { // Open folder
        while (false !== ($file = readdir($handle))) { // For all files that are in the import folder
            if ('.' === $file) continue; // Skip files that are just named "."
            if ('..' === $file) continue; // Skip files that are just named ".."

            $temp = explode('.', $file); // Split file name by "." into array
            if (count($temp) > 1) {
                $format =  $temp[count($temp) - 1]; // Get file extension
            } else {
                $format = '';
            }
            unset($temp); // Remove temporary storage

            if ('DS_Store' === $format) { // If file extension is DS_Store
                unlink(sprintf('%s/Data/Upload/Movies/%s', $path, $file)); // Delete file from import folder
                continue; // Go to next file
            }

            chown(sprintf('%s/Data/Upload/Movies/%s', $path, $file), 'www-data'); // Make PHP owner of file

            if ($format === '') {
                if ($movies = opendir($path . sprintf('/Data/Upload/Movies/%s', $file))) {
                    $thumbnail_name = '';
                    $thumbnail_format = '';
                    $movie_name = '';
                    $movie_format = '';

                    while (false !== ($movie = readdir($movies))) { // loop through folder
                        // Safty Checks like before...
                        if ('.' === $movie) continue;
                        if ('..' === $movie) continue;

                        $temp = explode('.', $movie); // Split file name by "." into array
                        if (count($temp) > 1) {
                            $cur_format = $temp[count($temp) - 1]; // Get file extension
                        } else {
                            $cur_format = '';
                        }
                        unset($temp); // Remove temporary storage

                        if ('DS_Store' === $cur_format) {
                            unlink(sprintf('%s/Data/Upload/Movies/%s/%s', $path, $file, $movie));
                            continue;
                        }

                        switch (strtolower($cur_format)) {
                            case 'jpg':
                            case 'jpeg':
                            case 'png':
                                $thumbnail_name = $movie;
                                $thumbnail_format = $cur_format;
                                break;
                            case 'mp4':
                            case 'mpeg4':
                            case 'mkv':
                                $movie_name = $movie;
                                $movie_format = $cur_format;
                                break;
                        }
                    }
                    $getID3 = new getID3();
                    $m = $getID3->analyze(sprintf('%s/Data/Upload/Movies/%s/%s', $path, $file, $movie_name));

                    $id = (string) mongo_insert_one('movies', [
                        'type' => 'movie',
                        'importDate' => time(),
                        'name' => $file,
                        'movie_format' => $movie_format,
                        'thumbnail_format' => $thumbnail_format,
                        'duration' => ceil($m['playtime_seconds'])
                    ]); // Create entry

                    try {
                        mkdir(sprintf('%s/Data/%s', $path, $id)); // Make folder for all data;
                    } catch(Exception $e) {
                        error(sprintf('Error Creating Directory: %s', $e), 405);
                    }

                    try {
                        copy(sprintf('%s/Data/Upload/Movies/%s/%s', $path, $file, $thumbnail_name), sprintf('%s/Data/%s/thumbnail.%s', $path, $id, $thumbnail_format)); // Copy into new folder.
                    } catch(Exception $e) {
                        error(sprintf('Error Copying Thumbnail: %s', $e), 405);
                    }

                    try {
                        unlink(sprintf('%s/Data/Upload/Movies/%s/%s', $path, $file, $thumbnail_name)); // Delete in upload folder
                    } catch(Exception $e) {
                        error(sprintf('Error Deleting Thumbnail in Upload: %s', $e), 405);
                    }

                    try {
                        copy(sprintf('%s/Data/Upload/Movies/%s/%s', $path, $file, $movie_name), sprintf('%s/Data/%s/movie.%s', $path, $id, $movie_format)); // Copy into new folder.
                    } catch(Exception $e) {
                        error(sprintf('Error Copying Movie: %s', $e), 405);
                    }

                    try {
                        unlink(sprintf('%s/Data/Upload/Movies/%s/%s', $path, $file, $movie_name)); // Delete in upload folder
                    } catch(Exception $e) {
                        error(sprintf('Error Deleting Movie in Upload: %s', $e), 405);
                    }

                    try {
                        closedir($movies); // Close Folder
                    } catch(Exception $e) {
                        error(sprintf('Error Closing Movie Folder in Upload: %s', $e), 405);
                    }

                    try {
                        rmdir(sprintf('%s/Data/Upload/Movies/%s', $path, $file)); // Remove folder in import folder;
                    } catch(Exception $e) {
                        error(sprintf('Error Deleting Movie Folder in Upload: %s', $e), 405);
                    }

                    $imported_count++;
                }
            }
        }
        closedir($handle);
    }
    return $imported_count;
}

function uploadSeries () {
    ini_set('max_execution_time', 300);
    $path = $_SERVER['DOCUMENT_ROOT']; // Path to upload folder
    $imported_count = 0;

    if ($series = opendir($path . '/Data/Upload/Series')) { // Open folder
        while (false !== ($serie = readdir($series))) { // For all files that are in the import folder
            if ('.' === $serie) continue; // Skip files that are just named "."
            if ('..' === $serie) continue; // Skip files that are just named ".."

            $temp = explode('.', $serie); // Split file name by "." into array
            if (count($temp) > 1) {
                $serie_format =  $temp[count($temp) - 1]; // Get file extension
            } else {
                $serie_format = '';
            }
            unset($temp); // Remove temporary storage

            if ('DS_Store' === $serie_format) { // If file extension is DS_Store
                unlink(sprintf('%s/Data/Upload/Series/%s', $path, $serie)); // Delete file from import folder
                continue; // Go to next file
            }

            chown(sprintf('%s/Data/Upload/Series/%s', $path, $serie), 'www-data'); // Make PHP owner of file

            if ($serie_format === '') {
                if ($seasons = opendir($path . sprintf('/Data/Upload/Series/%s', $serie))) {
                    $id = (string) mongo_insert_one('movies', [
                        'type' => 'serie',
                        'importDate' => time(),
                        'name' => $serie
                    ]);
                    mkdir(sprintf('%s/Data/%s', $path, $id));

                    $thumbnail_name = '';
                    $thumbnail_format = '';
                    $data = [];

                    while (false !== ($season = readdir($seasons))) { // loop through folder
                        // Safty Checks like before...
                        if ('.' === $season) continue;
                        if ('..' === $season) continue;

                        $temp = explode('.', $season); // Split file name by "." into array
                        if (count($temp) > 1) {
                            $season_format = $temp[count($temp) - 1]; // Get file extension
                        } else {
                            $season_format = '';
                        }
                        unset($temp); // Remove temporary storage

                        if ('DS_Store' === $season_format) {
                            unlink(sprintf('%s/Data/Upload/Series/%s/%s', $path, $serie, $season));
                            continue;
                        }

                        switch (strtolower($season_format)) {
                            case 'jpg':
                            case 'jpeg':
                            case 'png':
                                $thumbnail_name = $season;
                                $thumbnail_format = $season_format;
                                copy(sprintf('%s/Data/Upload/Series/%s/%s', $path, $serie, $thumbnail_name), sprintf('%s/Data/%s/thumbnail.%s', $path, $id, $thumbnail_format)); // Copy into new folder.
                                unlink(sprintf('%s/Data/Upload/Series/%s/%s', $path, $serie, $thumbnail_name)); // Delete in upload folder
                                break;
                            case '':
                                mkdir(sprintf('%s/Data/%s/%s', $path, $id, $season));
                                $data[$season] = [];
                                if ($episodes = opendir($path . sprintf('/Data/Upload/Series/%s/%s', $serie, $season))) {
                                    while (false !== ($episode = readdir($episodes))) { // loop through folder
                                        // Safty Checks like before...
                                        if ('.' === $episode) continue;
                                        if ('..' === $episode) continue;

                                        $temp = explode('.', $episode); // Split file name by "." into array
                                        if (count($temp) > 1) {
                                            $episode_format = $temp[count($temp) - 1]; // Get file extension
                                        } else {
                                            $episode_format = '';
                                        }
                                        unset($temp); // Remove temporary storage

                                        if ('DS_Store' === $episode_format) {
                                            unlink(sprintf('%s/Data/Upload/Series/%s/%s/%s', $path, $serie, $season, $episode));
                                            continue;
                                        }

                                        switch (strtolower($episode_format)) {
                                            case 'mp4':
                                            case 'mpeg4':
                                            case 'mkv':
                                                $getID3 = new getID3();
                                                $e = $getID3->analyze(sprintf('%s/Data/Upload/Series/%s/%s/%s', $path, $serie, $season, $episode));
                                                $episode_name = str_replace('.' . $episode_format, '', $episode);
                                                $data[$season][$episode_name] = ['format' => $episode_format, 'duration' => ceil($e['playtime_seconds'])];
                                                copy(sprintf('%s/Data/Upload/Series/%s/%s/%s', $path, $serie, $season, $episode), sprintf('%s/Data/%s/%s/%s', $path, $id, $season, $episode)); // Copy into new folder.
                                                unlink(sprintf('%s/Data/Upload/Series/%s/%s/%s', $path, $serie, $season, $episode)); // Delete in upload folder
                                                break;
                                        }
                                    }
                                    closedir($episodes);
                                    rmdir(sprintf('%s/Data/Upload/Series/%s/%s', $path, $serie, $season));
                                }
                                break;
                        }
                    }
                    closedir($seasons);
                    $imported_count++;
                    mongo_update_many('movies', ['_id' => new MongoDB\BSON\ObjectId($id)], ['$set' => ['thumbnail_format' => $thumbnail_format, 'data' => $data]]);
                    rmdir(sprintf('%s/Data/Upload/Series/%s', $path, $serie));
                }
            }
        }

        closedir($series);
    }
    return $imported_count;
}
