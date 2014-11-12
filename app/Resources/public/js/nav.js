$(document).ready(function () {
    // slider
    $('ul.enable-slider').append('<div id="nav-slider" class="hidden-xs"></div>');

    initSlider();

    $('ul.enable-slider li a').hover(
        function() {
            $(this).children('img').hide(0);
            $(this).children('img.active').show(0);

            var left = $(this).parent().position().left;
            var width = $(this).parent().width()+1;
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

            $('#nav-slider').stop().animate({
                'left' : left,
                'width' : width,
                'opacity': 100
            });
        },
        function() {
            $(this).children('img').not('.active').show(0);
            $(this).children('img.active').hide(0);

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
        e.preventDefault();
        $($(this).attr('data-target')).toggle();
    });

    $('#menu').hover(function() {
        $('.navbar-brand').addClass('active');
    },
    function () {
        $('.navbar-brand').removeClass('active');
    });
});


function initSlider() {
    var left = $('ul.enable-slider li.active').position().left;
    var width = $('ul.enable-slider li.active').width()+1;

    $('#nav-slider').css({'left' : left, 'width' : width});
}
