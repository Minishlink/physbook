$(document).ready(function () {
    $('#password').focus(function () {
        $('#pingouin-ouvert').toggle(0, function () {
            $('#pingouin-ferme').toggle(0);
        });
    });

    $('#password').focusout(function () {
        $('#pingouin-ferme').toggle(0, function () {
            $('#pingouin-ouvert').toggle(0);
        });
    });
});
