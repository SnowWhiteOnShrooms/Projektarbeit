<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/defaults.php';

if (check_sketch() > 5) {
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QNA</title>

    <link rel="stylesheet" href="/StyleSheets/Computer.css">
    <link rel="stylesheet" href="/Font/css/all.css">
</head>
<body>

<a class="backarrow" href="/index.php"><i class="fas fa-arrow-left"></i> </a>

<h2>English</h2>

<h3>How do I find out my IP address?</h3>

<p class="questionText">
    First you connect your server to mouse, keyboard, screen and internet.
    <br>
    <br>Then log in and open the terminal.
    <br>
    <br>In the terminal type "ip addr" and press enter.
    <br>
    <br>Then you search from the displayed code where "inet 192..." is written. Everything from "192" to the first "/" is your IP address.

<h3>How do I create a new user?</h3>

<p class="questionText">
    <br>Go to your browser and enter your IP address from your server and add /adduser after the IP address.
    <br>
    <br>The first user you create is the admin. Only as admin you can add a new user from then on.
    <br>
    <br> First you enter your username, then your password, then you confirm your password and finally you choose a profile picture. Make sure that your profile picture is a "png" file and is square.
</p>

<h3>How do I delete a user?</h3>

<p class="questionText">
    If you have macOS or Linux, open the terminal first. If you have Windows, open Command Prompt.
    <br>
    <br>It is important that when you copy the commands from here, you copy them without the quotes.
    <br>
    <br>First you type "ssh pi@" and after the "@" you write the IP address of your server.
    <br>
    <br>Now execute the command with Enter.
    <br>
    <br>Next you enter your password and press enter again.
    <br>
    <br>Now type "mongo" and press Enter.
    <br>
    <br>After you have executed the command "mongo" you have to type "use streamingserver".
    <br>
    <br>After that you have to type "db.users.remove({"username": "TEST"})" and at TEST you have to enter the name of the user you want to remove.
    <br>
    <br>Now write "exit" and press Enter. Repeat this step once more.
    <br>
    <br>As last step you have to delete the profile picture of the removed user in Scripts/profile_pictures.
</p>

<h3>How do I upload a movie or a series?</h3>

<p class="questionText">
    If you want to upload a movie, you have to create a folder with the name of the movie.
    <br>
    <br>In this folder you put the movie. Make sure that it is not more than one movie file. Make sure that the movie is in mp4, mpeg4 or mkv format. Otherwise you will not be able to upload the movie. If the movie is in the wrong format, try downloading the movie in the correct format or convert the movie to the correct format.
    <br>
    <br>If you have named the folder like the movie and the movie is in the right format, you have to make a title picture matching the movie in the folder.
    <br>
    <br>You can find the title image on the Internet. Make sure that the title image is in landscape format and not in portrait format. Make sure it is in jpg, jpeg or png format. Otherwise find a title image in the right format or convert it.
    <br>
    <br>Now you can drag the folder into scripts/Data/Upload/  Movies. Now go to the homepage of the website and press Upload.
    <br>
    <br>If you want to upload a series, you have to create a folder with the name of the series.
    <br>
    <br>In the folder you make another folder and name it "Season 1". If the series has more than one season, make as many folders as it has seasons and name them accordingly.
    <br>
    <br>In the Season 1 folder you put all episodes from Season 1. The episodes you then also rename "in Episode 1,2,3" etc. Make sure that the episodes are in one of the following formats: mp4, mpeg4 or mkv. If the episode is in the wrong format, try to download the episode in the correct format or convert the episode to the correct format.
    <br>
    <br>Once you have followed all these steps, you still need a title page.
    <br>
    <br>You can find the cover image on the Internet. Make sure that the cover image is in landscape format and not in portrait format. Make sure it is in jpg, jpeg or png format. Otherwise find a cover image in the right format or convert it.
    <br>
    <br>Now you can drag the folder with your series into scripts/Data/Upload/  Series. Now go to the homepage of the website and press Upload.
</p>

<h3>How do I delete a movie or series?</h3>

<p class="questionText">
    If you have MacOS or Linux, first open the terminal. If you have Windows, open Command Prompt.
    <br>
    <br>It is important that when you copy the commands from here, you copy them without the quotes.
    <br>
    <br>First you type "ssh pi@" and after the "@" you write the IP address of your server.
    <br>
    <br>Now execute the command with Enter.
    <br>
    <br>Next you enter your password and press enter again.
    <br>
    <br>Now type "mongo" and press Enter.
    <br>
    <br>After you have executed the command "mongo" you have to type "use streamingserver".
    <br>
    <br>After that you have to type "db.movies.remove({"name" : "TEST"})" and at TEST you have to enter the name of the movie or series you want to remove.
    <br>
    <br>Now write "exit" and press Enter. Repeat this step once more.
    <br>
    <br>As a last step you have to delete the folder of the removed movie or series in Scripts/Data.
</p>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<h2>German</h2>

<h3>Wie finde ich meine IP-Adresse heraus?</h3>

<p class="questionText">
    Als erstes schliesst du deinen Server an Maus, Tastatur, Bildschirm und Internet an.
    <br>
    <br>Dann logst du dich ein und öffnest das Terminal.
    <br>
    <br>Im Terminal gibst du ein "ip addr" und drückst Enter.
    <br>
    <br>Dann suchst du von dem angezeigtem Code wo "inet 192..." steht.
    Alles was von "192" bis zu dem ersten "/" ist, ist deine IP-Adresse.
</p>

<h3>Wie erstelle ich einen neuen Benutzer?</h3>

<p class="questionText">
    Gehe in deinen Browser und gebe deine IP-Adresse von deinem Server ein und f&#252;ge hinter der IP-Adresse noch /adduser hinzu.
    <br>
    <br>Der erste User, den du erstellst, ist der Admin.
    Nur als Admin kann man ab dann einen neuen Nutzer hinzufügen.
    <br>
    <br>Als Erstes gibst du dann deinen Benutzernamen an, dann dein Passwort, dann best&#228;tigst du dein Passwort und zuletzt wählst du noch ein Profilbild aus.
    Achte darauf, dass dein Profilbild eine "png" Datei ist und quadratisch ist.
</p>

<h3>Wie lösche ich einen Benutzer?</h3>

<p class="questionText">
    Wenn du macOS oder Linux hast, öffnest du als Erstes das Terminal. Hast du Windows, öffnest du Command Prompt.
    <br>
    <br> Wichtig ist, dass wenn du die Befehle von hier kopierst, dass du sie ohne die Anführungsstriche kopierst.
    <br>
    <br>Als Erstes gibst du "ssh pi@" ein und nach dem "@" schreibst du die IP-Adresse von deinem Server hin.
    <br>
    <br>Nun führst du den Befehl mit Enter aus.
    <br>
    <br>Als Nächstes gibst du dein Passwort ein und drückst wieder Enter.
    <br>
    <br>Nun gibst du "mongo" ein und drückst Enter.
    <br>
    <br>Hast du den Befehl "mongo" ausgeführt, gibst du "use streamingserver" ein.
    <br>
    <br>Hast du das gemacht musst du nun "db.users.remove({"username": "TEST"})" und bei TEST fügst du den Namen des Benutzers, den du löschen willst ein.
    <br>
    <br>Nun schreibst du "exit" hin und drückst Enter. Diesen Schritt wiederholst du noch einmal.
    <br>
    <br>Als letzten Schritt musst du noch in Scripts/ profile_pictures das Profilbild des entfernten Benutzers löschen.
</p>

<h3>Wie lade ich einen Film oder eine Serie hoch?</h3>

<p class="questionText">
    Willst du einen Film hochladen, musst du einen Ordner mit dem Namen des Filmes erstellen.
    <br>
    <br>In den Ordner machst du dann den Film rein.
    Achte darauf, dass es nicht mehr als eine Filmdatei ist.
    Achte darauf, dass der Film im Format mp4, mpeg4 oder mkv ist.
    Ansonsten kannst du den Film leider nicht hochladen.
    Wenn der Film im falschen Format ist, probiere den Film im richtigen Format runterzuladen oder konvertiere den Film ins richtige Format.
    <br>
    <br>Hast du den Ordner benannt wie den Film und der Film im richtigen Format ist, musst du noch ein Titelbild passend zum Film in den Ordner machen.
    <br>
    <br>Das Titelbild findest du im Internet.
    Achte darauf, dass das Titelbild im Querformat ist und nicht in Hochformat.
    Achte darauf, dass es im Format jpg, jpeg oder png ist.
    Sonst finde ein Titelbild im richtigen Format oder konvertiere es um.
    <br>
    <br>Nun kannst du den Ordner bei scripts/Data/Upload/Movies hereinziehen.
    Nun gehst du auf die Homepage der Website und drückst Upload.
    <br>
    <br>
    <br>
    <br>
    <br>Willst du eine Serie hochladen, musst du einen Ordner mit dem Namen der Serie erstellen.
    <br>
    <br>In den Ordner machst du wieder einen Ordner und nennst ihn "Season 1".
    Hat die Serie mehr als eine Season machst du so viele Ordner wie es Seasons hat und benennst sie entsprechend.
    <br>
    <br>In den Season 1 Ordner machst du dann alle Episoden von der Season 1 rein.
    Die Episoden nennst du dann auch um "in Episode 1,2,3" usw.
    Achte darauf, dass das die Episoden einer der folgenden Formate hat: mp4, mpeg4 oder mkv.
    Wenn die Episode im falschen Format ist, probiere die Episode im richtigen Format runterzuladen oder konvertiere die Episode ins richtige Format.
    <br>
    <br>Hast du alle diese Schritte befolgt, brauchst du noch ein Titelblatt.
    <br>
    <br>Das Titelbild findest du im Internet.
    Achte darauf, dass das Titelbild im Querformat ist und nicht in Hochformat.
    Achte darauf, dass es im Format jpg, jpeg oder png ist.
    Sonst finde ein Titelbild im richtigen Format oder konvertiere es um.
    <br>
    <br>Nun kannst du den Ordner mit deiner Serie bei scripts/Data/Upload/Series hereinziehen.
    Nun gehst du auf die Homepage der Website und drückst Upload.
</p>

<h3>Wie lösche ich einen Film oder Serie?</h3>

<p class="questionText">
    Wenn du MacOS oder Linux hast, öffnest du als Erstes das Terminal. Hast du Windows öffnest du Command Prompt.
    <br>
    <br> Wichtig ist, dass wenn du die Befehle von hier kopierst, dass du sie ohne die Anführungsstriche kopierst.
    <br>
    <br>Als Erstes gibst du "ssh pi@" ein und nach dem "@" schreibst du die IP-Adresse von deinem Server hin.
    <br>
    <br>Nun führst du den Befehl mit Enter aus.
    <br>
    <br>Als Nächstes gibst du dein Passwort ein und drückst wieder Enter.
    <br>
    <br>Nun gibst du "mongo" ein und drückst Enter.
    <br>
    <br>Hast du den Befehl "mongo" ausgeführt, gibst du "use streamingserver" ein.
    <br>
    <br>Hast du das gemacht, musst du nun "db.movies.remove({"name" : "TEST"})" und bei TEST fügst du den Namen des Films oder der Serie, die du löschen willst ein.
    <br>
    <br>Nun schreibst du "exit" hin und drückst Enter. Diesen Schritt wiederholst du noch einmal.
    <br>
    <br>Als letzten Schritt musst du noch in Scripts/ Data den Ordner des entfernten Filmes oder Serie löschen.
</body>
</html>