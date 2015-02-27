$(document).ready(function () {
    $('a.show_user').webuiPopover({
        title: '',
        type: 'async',
        content: function(data) { return data; },
        closeable: true,
        animation: 'pop',
        async: {
            before: function(that, xhr) { that.setContent('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Chargement...'); },
        }
    });
});
