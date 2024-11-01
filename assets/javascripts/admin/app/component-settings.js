MONSTER('MOIP.Components.Settings', function(Model, $, utils) {

    var errorClass = utils.addPrefix('field-error');

    Model.fn.start = function() {
        this.init();
    };

    Model.fn.init = function() {
        this.publicKey = $('[data-field="public-key"]');
        this.installments = $('[data-field="installments"]');
        this.billet = $('[data-field="billet"]');
        this.debit = $('[data-field="debit"]');
        this.billetBanking = $('[data-field="wbo-billet-banking"]');
        this.creditCard = $('[data-field="wbo-credit-card"]');
        this.bDiscount = $('[data-field="billet-discount"]');
        this.eDiscount = $('[data-field="enable-discount-field"]');
        this.eDiscountName = $('[data-field="wbo-billet-discount"]');
        this.cdOptions = $('[data-field="credit-card-option"]');
        this.eEmail = $('[data-field="send-email-field"]');
        this.eEmailFields = $('[data-field="wbo-email-field"]');

        if (this.elements.checkout) {
            this.handleElementsVisibility(this.elements.checkout.val());
        }
        this.hideInstallments($('[data-action=installments-maximum]').val());
        this.addEventListener();
        this.addDiscountFields();
        this.addEmailFields();
    };

    Model.fn.addEventListener = function() {
        this.on('keyup', 'invoice-name');
        this.on('change', 'checkout-type');
        this.on('change', 'installments-maximum');
        this.on('keyup', 'wirecard-manual-key');
        this.on('keyup', 'wirecard-manual-token');

        $('#oauth-app-btn').on('click', this._onClickOauthApp.bind(this));
        $('#mainform').on('submit', this._onSubmitForm.bind(this));
        $('#wirecard-split-app').on('click', this._onCreateSplitApp.bind(this));
    };

    Model.fn._onKeyupInvoiceName = function(event) {
        if (event.currentTarget.value.length > 13) {
            $(event.currentTarget).addClass(errorClass);
            return;
        }

        $(event.currentTarget).removeClass(errorClass);
    };

    Model.fn._onSubmitForm = function(event) {
        this.toTop = false;
        this.items = [];

        this.elements.validate.each(this._eachValidate.bind(this));

        return !~this.items.indexOf(true);
    };

    Model.fn._onCreateSplitApp = function(event) {
        this.toTop = false;
        this.items = [];

        this.elements.validate.each(this._eachValidate.bind(this));

        return !~this.items.indexOf(true);
    };

    Model.fn._onChangeCheckoutType = function(event) {
        this.handleElementsVisibility(event.currentTarget.value);
    };

    Model.fn._onChangeDiscountType = function(event) {
        this.handleDiscountsVisibility(event.currentTarget.value);
    };

    Model.fn._onChangeInstallmentsMaximum = function(event) {
        this.hideInstallments(event.currentTarget.value);
    };

    Model.fn.hideInstallments = function(max) {
        var installments = $('[data-installment]');

        installments.each(function(index, item) {
            var installment = $(item);
            if (parseInt(item.dataset.installment) > parseInt(max)) {
                installment.hide();
            } else {
                installments.show();
            }
        });
    };

    Model.fn._onClickOauthApp = function(event) {
        this.body.find('#app-overlay').slideToggle();
    };

    Model.fn._eachValidate = function(index, field) {
        var rect;
        var element = $(field),
            empty = element.isEmptyValue(),
            func = empty ? 'addClass' : 'removeClass';

        if (!element.is(':visible')) {
            return;
        }

        element[func](errorClass);

        this.items[index] = empty;

        if (!empty) {
            return;
        }

        field.placeholder = field.dataset.errorMsg;

        if (!this.toTop) {
            this.toTop = true;
            rect = field.getBoundingClientRect();
            window.scrollTo(0, (rect.top + window.scrollY) - 40);
        }
    };

    Model.fn.addDiscountFields = function() {
        var billetDiscountContainer = this.eDiscountName.closest('tr')

        if (this.eDiscount.prop('checked') === true) {
            billetDiscountContainer.show();
        } else {
            billetDiscountContainer.hide();
        }

        $('#woocommerce_woo-moip-official_field_enabled_discount').on('click', this._onClickDiscountFields.bind(this));
    }

    Model.fn._onClickDiscountFields = function() {
        var billetDiscountContainer = this.eDiscountName.closest('tr')

        if (this.eDiscount.prop('checked') === true) {
            billetDiscountContainer.show();
        } else {
            billetDiscountContainer.hide();
        }
    }

    Model.fn.addEmailFields = function() {
        var billetEmailContainer = this.eEmailFields.closest('tr')

        if (this.eEmail.prop('checked') === true) {
            billetEmailContainer.show();
        } else {
            billetEmailContainer.hide();
        }

        $('#woocommerce_woo-moip-official_send_wirecard_billet_email').on('click', this._onClickEmailFields.bind(this));
    }

    Model.fn._onClickEmailFields = function() {
        var billetEmailContainer = this.eEmailFields.closest('tr')

        if (this.eEmail.prop('checked') === true) {
            billetEmailContainer.show();
        } else {
            billetEmailContainer.hide();
        }
    }

    Model.fn.handleElementsVisibility = function(checkoutValue) {
        var publicKeyContainer = this.publicKey.closest('tr'),
            installmentsContainer = this.installments.closest('.form-table'),
            installmentsTitle = installmentsContainer.prev(),
            billetContainer = this.billet.closest('.form-table'),
            billetTitle = billetContainer.prev(),
            debitContainer = this.debit.closest('tr'),
            billetDiscountContainer = this.bDiscount.closest('tr'),
            savecdContainer = this.cdOptions.closest('tr'),
            discountContainer = this.eDiscount.closest('.form-table'),
            billetEmailContainer = this.eEmailFields.closest('tr');

        if (checkoutValue == 'default_checkout') {
            publicKeyContainer.show();
            installmentsContainer.show();
            installmentsTitle.show();
            billetContainer.show();
            billetTitle.show();
            debitContainer.hide();
            billetDiscountContainer.hide();
            savecdContainer.hide();
            discountContainer.hide();
            billetEmailContainer.hide();
        }

        if (checkoutValue == 'transparent_checkout') {
            publicKeyContainer.show();
            installmentsContainer.show();
            installmentsTitle.show();
            billetContainer.show();
            billetTitle.show();
            debitContainer.hide();
            billetDiscountContainer.show();
            savecdContainer.show();
            billetEmailContainer.show();
        }

        if (checkoutValue == 'moip_checkout') {
            publicKeyContainer.hide();
            installmentsContainer.hide();
            installmentsTitle.hide();
            billetContainer.hide();
            billetTitle.hide();
            debitContainer.show();
            billetDiscountContainer.hide();
            savecdContainer.hide();
            billetEmailContainer.hide();
        }

    };

});