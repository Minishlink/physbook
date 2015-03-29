$(document).ready(function () {
    $('#content').on('submit', '.ajax-form form', function(e) {
        e.preventDefault();
        var $this = $(this);

        if(!$this.hasClass('disabled')) {
            var button = $(this).find('button');

            button.addClass('disabled');
            chargement(true);

            $.ajax({
                url: $this.attr('action'),
                type: $this.attr('method'),
                data: $this.serialize(),
                dataType: 'json',
                success: function(json) {
                    if (json.success) {
                        $this.trigger('reset');
                    }

                    $this.parent().html(json.formView);
                    $('#flashBag').html(json.flashBagView);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Erreur : ' + errorThrown);
                },
                complete: function() {
                    button.removeClass('disabled');
                    chargement(false);
                }
            });
        }
    });
});
