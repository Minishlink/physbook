$(document).ready(function () {
    $('#content').on('submit', '.ajax-form form', function(e) {
        e.preventDefault();
        var $this = $(this);
        var button = $(this).find('button');

        if(!button.hasClass('disabled')) {
            button.addClass('disabled');
            chargement(true);

            var formData = new FormData($this[0]);



            $.ajax({
                url: $this.attr('action'),
                type: $this.attr('method'),
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(json) {
                    if (json.redirectURL) {
                        window.location.assign(json.redirectURL);
                    }

                    if (json.success) {
                        $this.trigger('reset');
                        $this.trigger('success');
                    }

                    $this.parent().html(json.formView);
                    $('#'+ $this.attr('name')).find('button').parent().before(json.flashBagView)
                    //$('#flashBag').html(json.flashBagView);
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
