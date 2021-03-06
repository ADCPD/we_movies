$(document).ready( function () {
    //--------------------------------------------------------------------------------//
    //---------------------------------- JSON REQUESTER ------------------------------//
    //--------------------------------------------------------------------------------//
    const getJSON = async url => {
        const response = await fetch(url);
        if(!response.ok) // check if response worked (no 404 errors etc...)
            throw new Error(response.statusText);

        //const movieData = await response.json(); // get JSON from the response
        // get JSON from the response
        const movieData = response.json();
        // returns a promise, which resolves to this data value
        return movieData;
    }

    //--------------------------------------------------------------------------------//
    //---------------------------------- SEARCH BAR ------------------------------//
    //--------------------------------------------------------------------------------//
    var requestPath = location.origin + '/names/movies';
    $("select").select2({
        theme: "bootstrap-5",
        containerCssClass: "select2--large", // For Select2 v4.0
        selectionCssClass: "select2--large", // For Select2 v4.1
        dropdownCssClass: "select2--large",
        ajax: {
            url: requestPath,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term // search term
                };
            },
            processResults: function(data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                var resData = [];
                data.forEach(function(value) {
                    if (value.title.indexOf(params.term) != -1)
                        resData.push(value)
                })
                return {
                    results: $.map(resData, function(item) {
                        return {
                            id: item.Id,
                            text: item.title,
                        }
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 1
    });

    //--------------------------------------------------------------------------------//
    //---------------------------------- COMMUNS VALUES ------------------------------//
    //--------------------------------------------------------------------------------//
    var jsonPath = location.origin + '/movies';
    var backgoundImagePath = "https://image.tmdb.org/t/p/w500/";

    //--------------------------------------------------------------------------------//
    //---------------------------------- GET TDM LIST MOVIES -------------------------//
    //--------------------------------------------------------------------------------//
    getJSON(jsonPath).then(moviesData => {
        var movies = moviesData.results;
        $.each(movies, function( key, movie ) {
            generateContent(movie)
        });
    });

    //--------------------------------------------------------------------------------//
    //----------------------------- FILTER MOVIES BY GENDER --------------------------//
    //--------------------------------------------------------------------------------//
    $("input[type=radio]").click(function () {
        // Init movies gender result
        document.getElementById("movies_list").innerHTML = "";

        var genreId = $(this).val();
        $("#movies-list-section").remove();
        //document.getElementById('movies-list-section').remove();

        getJSON(jsonPath).then(data => {
            var filtredData = filter(data.results, function(movie) {
                return movie.genre_ids.includes(parseInt(genreId)) ;
            });

            $(filtredData).each(function(index, item) {
                if(item.genre_ids.includes(parseInt(genreId))) {
                    generateContent(item)
                } else {
                    document.getElementById("movies_list").remove();
                }
            })

        }).catch(error => {
            console.error(error);
        });
    });

    //--------------------------------------------------------------------------------//
    //----------------------------- CUSTOM FUNCTION ----------------------------------//
    //--------------------------------------------------------------------------------//
    function filter(array, test) {
        var passed = [];
        for (var i = 0; i < array.length; i++) {
            if(test(array[i]))
                passed.push(array[i]);
        }
        return passed;
    }

    function generateContent(item) {
        var backgoundImage = backgoundImagePath + item.backdrop_path;
        var modalPath = location.origin + '/movie/' + item.id ;
        document.getElementById("movies_list").innerHTML += '<div id="movies-list-section" class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">' +
            '<div class="col p-4 d-flex flex-column position-static">' +
            '<h3 class="mb-0" data-filtered="' + item.id + '">' + item.title + '</h3>' +
            '<div class="mb-1 text-muted">Vote range  :  ' + item.vote_average + '</div>' +
            '<p class="card-text mb-auto">' + item.overview + '</p>' +
            '<a href="' + modalPath + '" ' +
            'role="button" id="modal_stream_movie_' + item.id + '" ' +
            'class="btn btn-primary modal-trigger" data-filtered="' + item.id + '"' +
            ' data-bs-toggle="modal" data-bs-target="#movieModal" >See more ...</a>' +
            '</div>' +
            '<div class="col-auto d-none d-lg-block">' +
            '<img src="' + backgoundImage + '" alt="" class="bd-placeholder-img" width="200" height="250"  role="img"/> ' +
            '</div>' +
            '</div>';


            generateModal(modalPath, item.id);
    }

    function generateModal(path, id) {
        $('.modal-trigger').click(function () {
            var embedSelector = $("iframe#modal_stream_movie");
            var contentSelector = document.getElementById("modal_stream_movie_description");

            $.get(path, function(data){
                embedSelector.removeAttr('src');
                contentSelector.innerHTML = "";

                if (id === data.id) {
                    embedSelector.attr('src', data.movie_streaming_path);
                    contentSelector.innerHTML += '<p>' +
                        '<strong> Original title : </strong> ' + data.original_title + '(' + data.original_language + ')' +
                        '</br>' +
                        '<strong> Title : </strong> ' + data.title +
                        '</br>' +
                        '<strong> Release Date : </strong> ' + data.release_date +
                        '</br></hr>' +
                        data.overview +
                        '</p>';
                } else {
                    embedSelector.removeAttr('src');
                    contentSelector.innerHTML =  "Sorry no data found!!"
                }
            });
        });
    }
});
