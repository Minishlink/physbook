$(document).ready(function () {
    $('#content').on('submit', '.physHMcontainer form', function(e) {
        e.preventDefault();
        var $this = $(this);
        var button = $(this).find('input');

        if(!$this.hasClass('disabled') && !button.hasClass('disabled')) {
            button.addClass('disabled');

            $.ajax({
                url: $this.attr('action'),
                type: $this.attr('method'),
                data: $this.serialize(),
                dataType: 'json',
                success: function(json) {
                    if(json.success) {
                        $this
                            .parent('.physHMcontainer')
                            .find('.counter')
                            .html(function(i, val) {
                            return +val+1;
                        });
                    } else {
                        console.warn(json.reason);
                    }
                },
                complete: function() {
                    button.removeClass('disabled');
                }
            });
        }
    });
});
