# Projektarbeit
## Table of Contents

1. [About The Project](#about-the-project)
    - [Built With](#built-with)
    - [Code that we used](#code-that-we-used)
2. [Setup](#setup)
    - [General](#general)
    - [Installs](#installs)
    - [Firewall Setup](#firewall-setup)
    - [MongoDB Setup](#mongodb-setup)
3. [Questions and Answers](#questions-and-answers)
    - [How do I create a new user?](#how-do-i-create-a-new-user)
    - [How do I delete a user?](#how-do-i-delete-a-user)
    - [How do I upload a movie or a series?](#how-do-i-upload-a-movie-or-a-series)
      - [Uploading Movies](#uploading-movies)
      - [Uploading Series](#uploading-series)
    - [How do I delete a movie or a series?](#how-do-i-delete-a-movie-or-a-series)

## About The Project
This is the Final Project of my friend and me for Secondary School. 
We built our own Streaming Server. 

### Built With

* [MongoDB](https://www.mongodb.com)
* [PHP](https://www.php.net)
* [Javascript](https://www.javascript.com)
* [CSS](https://developer.mozilla.org/en-US/docs/Glossary/CSS)
* [HTML](https://developer.mozilla.org/en-US/docs/Glossary/HTML)

### Code that we used
* [getID3](https://github.com/JamesHeinrich/getID3)
* [MongoDB Library](https://github.com/IQisMySenpai)
* [FontAwesome](https://fontawesome.com/)
* [jQuery](https://jquery.com/)

## Setup
We are using *Ubuntu Server 22.04 LTS*

After setting up the user we run following:

### General
Change the pepper in the /api/config.php

Update Package List
```
sudo apt update
```

Upgrade Packages
```
sudo apt upgrade
```

Also Download FontAwesome and add it in a Folder called `Font` to the root of the Project

### Installs
Openssh Server
```
sudo apt install openssh-server
```

Apache2 Server
```
sudo apt install apache2
```

PHP 8.1
```
sudo apt-get install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.1 libapache2-mod-php8.1 php8.1-common php8.1-cli php8.1-mongodb php-pear php8.1-dev
```

[MongoDB Install](https://www.mongodb.com/docs/manual/tutorial/install-mongodb-on-ubuntu/)
```
wget -qO - https://www.mongodb.org/static/pgp/server-5.0.asc | sudo apt-key add -
echo "deb [ arch=amd64,arm64 ] https://repo.mongodb.org/apt/ubuntu focal/mongodb-org/5.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-5.0.list
sudo apt-get update
sudo apt-get install -y mongodb-org
sudo pecl install mongodb
```

Add `extension=mongodb.so` to your php.ini

Start and enable apache2 and mongodb
```
sudo systemctl start apache2
sudo systemctl start mongod
sudo systemctl enable apache2
sudo systemctl enable mongod
```

### Firewall Setup

```
sudo ufw enable
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22
sudo ufw allow 80
sudo ufw allow 443
```

### MongoDB Setup
Start the mongo shell:
```
mongo
```
Create these 2 users
```
use admin
db.createUser({user:"admin",pwd:"SuperSecretPassword", roles:["userAdminAnyDatabase", "dbAdminAnyDatabase", "readWriteAnyDatabase"]})
db.createUser({user:"phpUserName", pwd:"SuperSecretPassword2", roles:[{role:"readWrite", db:"streamingserver"}]})
```

Change the location of the DB in the lib_mongodb.php file to 
```
"mongodb://phpUserName:SuperSecretPassword2@127.0.0.1:27017"
```

## Questions and Answers
### How do I create a new user?
Go to your browser and enter your IP address from your server and add `/adduser` after the IP address. 
The first user you create is the admin. Only as admin you can add a new user from then on.
First you enter your username, then your password, then you confirm your password and finally you choose a 
profile picture. Make sure that your profile picture is a .png file and is square.


### How do I delete a user?

SSH into the server.

Open the mongoshell with the command 
```
mongo
```
Select the database
```
use streamingserver
```
Remove the User with
```
db.users.remove({"username": "UsernameYouWantToRemove"})
```
As last step you have to delete the profile picture of the removed user in `/profile_pictures`.


### How do I upload a movie or a series?
#### Uploading Movies
If you want to upload a movie, you have to create a folder with the name of the movie.
In this folder you put the movie. Make sure that it is not more than one movie file. Make sure that the movie is in mp4, mpeg4 or mkv format. Otherwise you will not be able to upload the movie. If the movie is in the wrong format, try downloading the movie in the correct format or convert the movie to the correct format.
If you have named the folder like the movie and the movie is in the right format, you have to make a title picture matching the movie in the folder.
You can find the title image on the Internet. Make sure that the title image is in landscape format and not in portrait format. Make sure it is in jpg, jpeg or png format. Otherwise find a title image in the right format or convert it.
Now you can drag the folder into `/Data/Upload/`  Movies. Now go to the homepage of the website and press Upload.

#### Uploading Series
If you want to upload a series, you have to create a folder with the name of the series.
In the folder you make another folder and name it "Season 1". If the series has more than one season, make as many folders as it has seasons and name them accordingly.
In the Season 1 folder you put all episodes from Season 1. The episodes you then also rename "in Episode 1,2,3" etc. Make sure that the episodes are in one of the following formats: mp4, mpeg4 or mkv. If the episode is in the wrong format, try to download the episode in the correct format or convert the episode to the correct format.
Once you have followed all these steps, you still need a title page.
You can find the cover image on the Internet. Make sure that the cover image is in landscape format and not in portrait format. Make sure it is in jpg, jpeg or png format. Otherwise find a cover image in the right format or convert it.
Now you can drag the folder with your series into `/Data/Upload/`  Series. Now go to the homepage of the website and press Upload.


### How do I delete a movie or a series?

SSH into the server.

Open the mongoshell with the command
```
mongo
```
Select the database
```
use streamingserver
```
Remove the Movie/Serie with
```
db.movies.remove({"name" : "Movie/SerieName"})
```
As a last step you have to delete the folder of the removed movie or series in `scripts/Data`.
