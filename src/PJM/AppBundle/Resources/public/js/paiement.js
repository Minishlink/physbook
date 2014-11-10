$(document).ready(function () {
    $('#init_paiement').click(function () {
        console.log('initialisation du paiement');

        var req = $.ajax({
            type: "POST",
            url: "https://rest-pp.s-money.fr/commerce/payments/smoney",
            data: { amount: 20 },
            cache: false
        });

        req.done(function (data) {
            $("#return_paiement").hide().html(data).show("slow", "swing");
            console.log('ok');
        });

        req.fail(function (jqXHR, textStatus,errorThrown) {
            console.log(textStatus + " " + errorThrown);
        });
    });
});
