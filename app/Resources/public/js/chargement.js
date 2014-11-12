$(document).ready(function () {
    $('#chargement').fadeTo(200, 0, function() {
        $('#chargement').css('visibility', 'hidden');
    });

    $('#content').fadeIn(200);

    $('a').not(".disable-fade").click(function() {
        $('#content').fadeOut(200);

        $('#chargement').css('visibility', 'visible');
        $('#chargement').fadeTo(200, 1);
    });
});
