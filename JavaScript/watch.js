var video_player = $('video.movieplayer');
var id = video_player.attr('id');

var time = 0;
var last_time = 0;

var serie = false;

var roundUp = 360;

video_player.on('timeupdate', function () {
    last_time = time;
    time = Math.floor(this.currentTime);
    if (last_time < time) {
        if (time < 10) {
            savetime(0);
        }

        if (time % 15 === 0) {

            if (this.duration - time < roundUp) {
                savetime(0);
            } else {
                savetime(time);
            }
        }
    }
});

function savetime (saveTime, success = null) {
    let data = {
        action: 'savetime',
        time: saveTime,
        id: id
    }
    if(serie) {
        data['episode'] = episode;
        data['season'] = season;
    }
    $.ajax({
        url: '/api/main_api.php',
        method: 'POST',
        data: data,
        success: success
    });
}

function selectSeason () {
    let s = $( "option:selected" ).html();
    $('episodes').css('display', 'none');
    $('#' + s).css('display', 'block');
}

function selectEpisode(element) {
    season = $( "option:selected" ).html();
    episode = $(element).text();
    savetime(0, function () {
        window.location.reload();
    });
}