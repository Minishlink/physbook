$(document).load(function () {
    // s'il y a création effective de l'évènement
    $("#creation").on("success", '.ajax-form form', function() {
        // on cache le formulaire
        $('#creation').collapse('hide');

        // on rafraichit les évènements
        timeline_refresh();
    });

    function timelineRefresh() {
        chargement(true);
        $('#timeline').fadeOut();

        $.ajax({
            url: Routing.generate,
            type: 'get',
            dataType: 'json',
            success: function(json) {
                if (json.success) {
                    showEvent();
                }

                $this.parent().html(json.formView);
                $('#'+ $this.attr('name')).find('button').parent().before(json.flashBagView)
                //$('#flashBag').html(json.flashBagView);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Erreur : ' + errorThrown);
            },
            complete: function() {
                chargement(false);
            }
        });
    }

    function hideEvent() {

    }

    function showEvent() {

    }

    // on
});
