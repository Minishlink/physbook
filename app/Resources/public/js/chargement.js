$(document).ready(function () {
    $('#chargement').fadeTo(200, 0, function() {
        $('#chargement').css('visibility', 'hidden');
    });

    $('#content').fadeIn(400);

    $('a').not(".disable-fade").not("[target='_blank']").filter("[href]").click(function(e) {
        if (e.ctrlKey === false && e.button === 0) {
            $('#content').fadeOut(200);

            $('ul[id^="menu-"]:visible').slideUp(100);
            $('.collapse.in:visible').toggle('hide');

            $('#chargement').css('visibility', 'visible');
            $('#chargement').fadeTo(200, 1);
        }
    });

    $('.collapse').on('shown.bs.collapse', function () {
        var tables = $.fn.dataTable.tables(true);
        $(tables).DataTable().columns.adjust();
    });

    $('.modal').on('shown.bs.modal', function() {
        var tables = $.fn.dataTable.tables(true);
        $(tables).DataTable().columns.adjust();
    });
});
