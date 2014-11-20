$(document).ready(function () {
    $('#chargement').fadeTo(200, 0, function() {
        $('#chargement').css('visibility', 'hidden');
    });

    $('#content').fadeIn(200);

    $('a').not(".disable-fade").click(function(e) {
        if (e.ctrlKey === false && e.button === 0) {
            $('#content').fadeOut(200);

            $('ul[id^="menu-"]:visible').slideUp(100);
            $('.collapse.in:visible').toggle('hide');

            $('#chargement').css('visibility', 'visible');
            $('#chargement').fadeTo(200, 1);
        }
    });
});
