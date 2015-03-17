$(document).ready(function () {
    // slider
    $('ul.enable-slider').append('<div id="nav-slider" class="hidden-collapsed"></div>');

    initSlider();
    // timer pour l'affichage du sous-menu
    nav_timer = false;

    $('ul.enable-slider > li > a').hover(
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
            var target = getSliderTargetPos();

            $('#nav-slider').stop().animate({
                'left' : target[0],
                'width' : target[1],
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
    $('#liste-menu > ul > li > a.disable-fade').click(function(e) {
        // le lien ne pointe plus vers #
        e.preventDefault();

        // on cache tous les sous-menus et on affiche celui qui est cliqué
        var cible = $(this).attr('data-target');

        // si un sous-menu est visible
        if($('ul[id^="menu-"]').is(".afficher")) {
            console.log('test');
            // on cache tous les sous-menus visibles autres que la cible
            $('ul[id^="menu-"].afficher').not(cible).removeClass('afficher');

            // si un sous-menu est en passe d'être remonté (l'utilisateur a enlevé sa souris du menu)
            if(nav_timer) {
                clearTimeout(nav_timer);
            }
        }

        // on affiche la cible
        $(cible).addClass('afficher');
    });

    // si on clique sur le bouton navbar-toggle on cache les menus visibles
    $('.navbar-toggle, .navbar-header').click(function () {
        $('ul[id^="menu-"].afficher').removeClass('afficher');
    });

    /*
     * Coloration du logo en fonction de si on passe la souris sur le menu et on cache les sous-menus quand on part
     */
    $('#menu').hover(function() {
        // on colore le logo phy'sbook en rouge
        $('.navbar-brand').addClass('active');
    },
    function () {
        // on décolore le logo phy'sbook
        $('.navbar-brand').removeClass('active');

        // on cache le sous-menu au bout de 1s si on est pas revenu sur le menu
        if(nav_timer) {
            clearTimeout(nav_timer);
        }

        nav_timer = setTimeout(function(){
            if (!$('#menu').is(':hover')) {
                $('ul[id^="menu-"]').removeClass('afficher');
            }
        }, 1000);
        ;
    });

    /*
     * Les liens avec ancres sont atteints de façon progressive
     */
    $("a[href^='#']").on('click', function(e) {
        e.preventDefault();

        var hash = this.hash;
        if (hash != "") {
            var el = $(this.hash);
            var elOffset = el.offset().top;
            var elHeight = el.height();
            var windowHeight = $(window).height();
            var offset;

            console.log(elHeight);
            console.log(windowHeight);

            if (elHeight < windowHeight) {
                offset = elOffset - ((windowHeight / 2) - (elHeight / 2));
            }
            else {
                offset = elOffset;
            }

            $('html, body').animate({
                scrollTop: offset
            }, 300);
        }
    });
});


function initSlider() {
    var target = getSliderTargetPos();

    $('#nav-slider').css({
        'left' : target[0],
        'width' : target[1],
    });
}

function getSliderTargetPos() {
    if($('ul.enable-slider > li.active').length) {
        var left = $('ul.enable-slider > li.active').position().left;
        var width = $('ul.enable-slider > li.active').width()+1;
    } else {
        var left = $('ul.enable-slider > li.default').position().left;
        var width = $('ul.enable-slider > li.default').width()+1;
    }

    return [left, width];
}
