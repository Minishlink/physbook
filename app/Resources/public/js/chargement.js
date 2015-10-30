$(document).ready(function () {
    chargement(false);

    $('#content').fadeIn(400);

    $('a').not(".disable-fade").not("[target='_blank']").filter("[href]").not('[href^="#"]').click(function(e) {
        if (e.ctrlKey === false && e.button === 0) {
            $('#content').fadeOut(200);

            $('ul[id^="menu-"]:visible').slideUp(100);
            $('.collapse.in:visible').toggle('hide');

            chargement(true);

            if (iOSMobile()) {
                e.preventDefault();
                window.location = e.target.href;
            }
        }
    });

    $('.collapse').on('shown.bs.collapse', function () {
        resizeComponents();
    });

    $('.modal').on('shown.bs.modal', function() {
        resizeComponents();
    });

    $('#fos_comment_thread').on('click', '.fos_comment_comment_vote', function() {
        $(this)
            .prop('disabled', true)
            .addClass('disabled')
        ;
    });

    $('#fos_comment_thread').on('fos_comment_vote_comment', function(e, data, form) {
        $('.fos_comment_comment_vote.disabled')
            .prop('disabled', false)
            .removeClass('disabled')
        ;
    });
});

function chargement(commencer) {
    var $chargement = $('#chargement');

    if (commencer) {
        $chargement.css('visibility', 'visible');
        $chargement.fadeTo(200, 1);
    } else {
        $chargement.fadeTo(200, 0, function() {
            $chargement.css('visibility', 'hidden');
        });
    }
}

function resizeSelect2() {
    $('.select2-container:visible').css('width','100%');
}

function resizeDataTables() {
    var tables = $.fn.dataTable.tables(true);
    $(tables).DataTable().columns.adjust();
    $('.dataTables_scrollHeadInner, .dataTable').css('width', '100%');
    $(tables).css('width', '100%');
}

function resizeComponents() {
    resizeDataTables();
    resizeSelect2();
}
