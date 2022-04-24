function get_movies (search=null) {
    let filter = $('.active').html();

    let data = {
        action: 'get_movies',
        filter: filter
    }
    if (search !== null) {
        data['search'] = search;
    }
    $.ajax({
        url: '/api/main_api.php',
        method: 'POST',
        data: data,
        success: function(data) {
            if (window.location.href.includes('/watch')) {
                window.location.href = '/';
            } else {
                $('div.movietable').html(generate_movietable(data));
            }
        },
        error: function(data) {
            alert('Error while running get_movies:\n\n' + data.responseText);
        }
    });
}

function upload_new_movies(button) {
    $(button).html('Uploading <i class="fas fa-sync fa-spin"></i>');
    $.ajax({
        url: '/api/main_api.php',
        method: 'POST',
        data: {
            action: 'scanForNewMovies'
        },
        success: function(data) {
            $(button).html('Upload');
            alert('Uploaded ' + data + '.');
            get_movies ()
        },
        error: function(data) {
	    $(button).html('Upload');
            alert('Error while running get_movies:\n\n' + data.responseText);
        }
    });
}

function search () {
    let val = $('input.search').val();
    get_movies(val)
}

function logout () {
    $.ajax({
        url: '/api/main_api.php',
        method: 'POST',
        data: {
            action: 'logout'
        },
        success: function(data) {
            window.location.href = '/login';
        },
        error: function(data) {
            alert('Error while running logging out:\n\n' + data.responseText);
        }
    });
}

function clearSearch () {
    $('input.search').val('');
    search()
}

document.addEventListener('keyup', function (event) {
    if (event.keyCode === 13) {
        search();
    }
});
