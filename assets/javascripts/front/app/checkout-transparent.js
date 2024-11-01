jQuery(document).on('click', '#tabMoipCreditCard', function(event, tabName, payMethod) {
    var tabName = 'moip-payment-method-credit-card',
        payMethod = 'payCreditCard',
        i, tabcontent, tablinks;

    tabcontent = document.getElementsByClassName("tabcontent");

    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    tablinks = document.getElementsByClassName("tablinks");

    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    document.getElementById(tabName).style.display = "block";
    event.currentTarget.className += " active";

    jQuery('body').trigger('moip_checkout_payment_method', [event, payMethod]);

});

jQuery(document).on('click', '#tabMoipBillet', function(event, tabName, payMethod) {
    var tabName = 'moip-payment-method-billet',
        payMethod = 'payBoleto',
        i, tabcontent, tablinks;

    tabcontent = document.getElementsByClassName("tabcontent");

    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    tablinks = document.getElementsByClassName("tablinks");

    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    document.getElementById(tabName).style.display = "block";
    event.currentTarget.className += " active";

    jQuery('body').trigger('moip_checkout_payment_method', [event, payMethod]);

});

(function($) {
    var radios = $('input:radio[id=payment_method_woo-moip-official]'),
        tabBillet = $('#tabMoipBillet'),
        tabCrediCard = $('#tabMoipCreditCard'),
        radiosPayment = $('input:radio[name=payment_method]');

    radiosPayment.change(function() {
        if (this.value != 'woo-moip-official') {
            $('#moip-payment-method-field').val('');
        }
    });

    radios.change(function() {
        $('#moip-payment-method-field').val('');

        if (this.value == 'woo-moip-official' && tabCrediCard.hasClass('active')) {
            $('#moip-payment-method-field').val('payCreditCard');
        }

        if (this.value == 'woo-moip-official' && tabBillet.hasClass('active')) {
            $('#moip-payment-method-field').val('payBoleto');
        }
    });

    if (radios.is(':checked') == false) {
        $('#moip-payment-method-field').val('');
    }

    if (radios.is(':checked') == true) {

        if (tabBillet.hasClass('active')) {
            $('#moip-payment-method-field').val('payBoleto');
        }

        if (tabCrediCard.hasClass('active')) {
            $('#moip-payment-method-field').val('payCreditCard');
        }
    }
})(jQuery);

(function($) {
    'use strict';

    $(function() {
        $(document.body).on('change', 'input[name="payment_method"]', function() {
            $('body').trigger('update_checkout');
        });
    });
}(jQuery));

(function($) {
    'use strict';

    var orderTotal = document.getElementsByClassName('order-total');

    if (!orderTotal || orderTotal.length === 0) {
        return;
    }

    $('body').on('updated_checkout', function() {

        if (typeof orderTotal[0].firstElementChild !== 'undefined') {
            var titleTotal = orderTotal[0].firstElementChild;
        }

        var tabBilletID = document.getElementById('tabMoipBillet'),
            tabCrediCardID = document.getElementById('tabMoipCreditCard'),
            taxTotal = document.getElementsByClassName('fee')[0],
            radioMoip = $('input:radio[id=payment_method_woo-moip-official]');

        if (radioMoip.is(':checked')) {
            titleTotal.innerHTML = 'Total no Boleto';
        }

        if (!radioMoip.is(':checked')) {
            titleTotal.innerHTML = 'Total';
        }

        if (!tabBilletID) {
            titleTotal.innerHTML = 'Total';
        }

        if (!tabCrediCardID) {
            titleTotal.innerHTML = 'Total';
        }

        if (!taxTotal) {
            titleTotal.innerHTML = 'Total';
        }

        $(document.body).on('change', 'input[name="payment_method"]', function() {
            if (this.value == 'woo-moip-official' && tabBilletID) {
                $('body').trigger('update_checkout');
                titleTotal.innerHTML = 'Total no Boleto';
            }
        });

    });
}(jQuery));

(function($) {
    'use strict';

    $('body').on('updated_checkout', function() {
        $('[data-element="cpf-holder"]').mask('000.000.000-00', { reverse: true });
        $('[data-element="birth-holder"]').mask('00/00/0000', { placeholder: 'DD/MM/YYYY' });

        var phoneNumber = function(val) {
                return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
            },
            options = {
                onKeyPress: function(val, e, field, options) {
                    field.mask(phoneNumber.apply({}, arguments), options);
                }
            };
        $('#phone-holder').mask(phoneNumber, options);
    });

}(jQuery));
