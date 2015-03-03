$(document).ready(function () {
    $('#chargement').fadeTo(200, 0, function() {
        $('#chargement').css('visibility', 'hidden');
    });

    $('#content').fadeIn(400);

    $('a').not(".disable-fade").not("[target='_blank']").filter("[href]").not('[href^="#"]').click(function(e) {
        if (e.ctrlKey === false && e.button === 0) {
            $('#content').fadeOut(200);

            $('ul[id^="menu-"]:visible').slideUp(100);
            $('.collapse.in:visible').toggle('hide');

            $('#chargement').css('visibility', 'visible');
            $('#chargement').fadeTo(200, 1);
        }
    });

    $('.collapse').on('shown.bs.collapse', function () {
        resizeDataTables();
    });

    $('.modal').on('shown.bs.modal', function() {
        resizeDataTables();
    });

    $('#fos_comment_thread').on('click', '.fos_comment_comment_vote', function() {
        $(this)
            .prop('disabled', true)
            .addClass('disable')
            .fadeTo(400, 0.1)
        ;
    });

    $('#fos_comment_thread').on('fos_comment_vote_comment', function(e, data, form) {
        $('.fos_comment_comment_vote.disable').fadeTo(400, 1, function() {
            $(this)
                .css("opacity", "")
                .prop('disabled', false)
                .removeClass('disable')
            ;
        });

    });
});

function resizeDataTables() {
    var tables = $.fn.dataTable.tables(true);
    $(tables).DataTable().columns.adjust();
    $('.dataTables_scrollHeadInner, .dataTable').css('width', '100%');
    $(tables).css('width', '100%');
}
