$(document).ready(function () {
    $('a').click(function() {
        $('#content').fadeOut(200, function() {
            $('#chargement').fadeIn(200);
        });
    });
});

$(window).load(function() {
    $('#chargement').fadeOut(200, function() {
        $('#content').fadeIn(200);
    });
});
