function watch(element) {
    let id = $(element).attr('id');
    window.location.href= '/watch?id=' + id;
}

function generate_movietable (json){
    let data =[];
    try{
        data = JSON.parse(json);
    }
    catch(err){
        alert("Error while generating movietable: \n\n" + json.responseText);
        return '<span>No Results</span>';
    }

    data = data["movies"];
    let html = '';
    let len = data.length;

    if (len < 1) {
        return '<span>No Results</span>';
    }

    for (let i = 0; i < len; i++) {
        let id = data[i]['_id'];
        let name = data[i]['name'];
        html += '<div class="movie" id="' + id + '" onclick="watch(this);">';
        html += '<div class="movieposter" style="background-image: url(\'/Data/' + id + '/thumbnail.' + data[i]['thumbnail_format'] + '\');"></div>';
        html += '<div class="progressBar"><div class="bar" style="width: '
        html += '' + Math.floor((data[i]['time']/data[i]['duration']) * 100);
        html += '%;"></div></div>'
        html += '<div class="movietitle">' + name + '</div></div>';
    }
    return html;
}

get_movies();
