$(document).ready(function () {
    $('a').click(function() {
        $('#content').fadeOut(200);

        $('#chargement').css('visibility', 'visible');
        $('#chargement').fadeTo(200, 1);
    });
});

$(window).load(function() {
    $('#chargement').fadeTo(200, 0, function() {
        $('#chargement').css('visibility', 'hidden');
    });

    $('#content').fadeIn(200);
});
