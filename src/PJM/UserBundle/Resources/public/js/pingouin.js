$(document).ready(function () {
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
});
