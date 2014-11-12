$(document).ready(function () {
    // slider
    $('ul.enable-slider').append('<div id="nav-slider" class="hidden-collapsed"></div>');

    initSlider();

    $('ul.enable-slider li a').hover(
        function() {
            // lorsque la souris survole le lien, on affiche l'icône correspondante en rouge
            $(this).children('img').hide(0);
            $(this).children('img.active').show(0);

            // on colore le slider en fonction du lien visé
            var target = $(this).attr('data-target');
            switch(target) {
                case '#menu-vie':
                    $('#nav-slider').css('background-color', '#b63938');
                    break;
                case '#menu-assos':
                    $('#nav-slider').css('background-color', '#febf00');
                    break;
                case '#menu-consos':
                    $('#nav-slider').css('background-color', '#eb661d');
                    break;
                case '#menu-thuyss':
                    $('#nav-slider').css('background-color', '#6a8fda');
                    break;
                case '#menu-tutos':
                    $('#nav-slider').css('background-color', '#804faa');
                    break;
            }

            // on place le slider coloré à l'endroit de la souris
            var left = $(this).parent().position().left;
            var width = $(this).parent().width()+1;
            $('#nav-slider').stop().animate({
                'left' : left,
                'width' : width,
                'opacity': 100
            });
        },
        function() {
            // lorsque la souris quitte le lien, on affiche l'icône correspondante en noir
            $(this).children('img').not('.active').show(0);
            $(this).children('img.active').hide(0);

            // on remet le slider en place
            var left = $('ul.enable-slider li.active').position().left;
            var width = $('ul.enable-slider li.active').width()+1;

            $('#nav-slider').stop().animate({
                'left' : left,
                'width' : width,
                'opacity': 0
            });
        }
    );
    $(window).resize(function() {
        initSlider();
    });
    $(window).load(function() {
        initSlider();
    });

    // sous-menu
    $('a.disable-fade').click(function(e) {
        // le lien ne pointe plus vers #
        e.preventDefault();

        // on cache tous les sous-menus et on affiche celui qui est cliqué
        var cible = $(this).attr('data-target');
        // si un sous-menu est visible
        if($('ul[id^="menu-"]').is(":visible")) {
            // on cache tous les sous-menus visibles autre que la cible
            $('ul[id^="menu-"]:visible').not(cible).slideUp(200, function() {
                // on affiche la cible
                $(cible).slideDown();
            });
        } else {
            // si aucun sous-menu n'est visible on affiche directement la cible
            $(cible).slideDown();
        }
    });

    $('.navbar-toggle').click(function () {
        $('ul[id^="menu-"]:visible').slideUp(100);
    });

    $('#menu').hover(function() {
        // on colore le logo phy'sbook en rouge
        $('.navbar-brand').addClass('active');
    },
    function () {
        // on décolore le logo phy'sbook
        $('.navbar-brand').removeClass('active');

        // on cache le sous-menu
        $('ul[id^="menu-"]').slideUp();
    });
});


function initSlider() {
    var left = $('ul.enable-slider li.active').position().left;
    var width = $('ul.enable-slider li.active').width()+1;

    $('#nav-slider').css({'left' : left, 'width' : width});
}
