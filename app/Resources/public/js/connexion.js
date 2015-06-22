$(document).ready(function () {
    // pingouin
    $('#password').focus(function () {
        $('#pingouin-ouvert').hide(0, function () {
            $('#pingouin-ferme').show(0);
        });
    });

    $('#password').focusout(function () {
        $('#pingouin-ferme').hide(0, function () {
            $('#pingouin-ouvert').show(0);
        });
    });

    // indicateur de chargement
    $('form').on('submit', function(e) {
        var $this = $(this);
        var button = $(this).find('button');

        if(!button.hasClass('disabled')) {
            button.addClass('disabled');

            $('#logo').html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>');
        }
    });
});
