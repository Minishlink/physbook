$(document).ready(function () {
    popover_show_user(true);

    $('a.show_usersHM').webuiPopover({
        closeable: true,
        animation: 'fade',
    });

    // pour initialiser les popover dont les éléments associés ne sont pas dans le DOM au départ
    $('#fos_comment_thread').on('fos_comment_new_comment fos_comment_load_thread', function(e, data) {
        popover_show_user(false);
    });
    $('.usersHM').on('shown.webui.popover', function(e, data) {
        popover_show_user(false);
    });
});

function popover_show_user(first) {
    $('a.show_user').webuiPopover({
        title: '',
        type: 'async',
        content: function(data) { return data; },
        closeable: true,
        animation: 'pop',
        multi: !first,
        async: {
            before: function(that, xhr) { that.setContent('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Chargement...'); },
        }
    });
}
