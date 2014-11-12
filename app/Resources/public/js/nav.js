$(document).ready(function () {
    // slider
    $('ul.enable-slider').append('<div id="nav-slider" class="hidden-xs"></div>');

    initSlider();

    $('ul.enable-slider li a').hover(
        function() {
            var left = $(this).parent().position().left;
            var width = $(this).parent().width();

            $('#nav-slider').stop().animate({
                'left' : left,
                'width' : width
            });
        },
        function() {
            var left = $('ul.enable-slider li.active').parent().position().left;
            var width = $('ul.enable-slider li.active').width();

            $('#nav-slider').stop().animate({
                'left' : left,
                'width' : width
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
});


function initSlider() {
    var left = $('ul.enable-slider li.active').parent().position().left;
    var width = $('ul.enable-slider li.active').width();

    $('#nav-slider').css({'left' : left, 'width' : width});
}
