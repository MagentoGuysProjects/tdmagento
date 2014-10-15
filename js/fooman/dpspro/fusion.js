Event.observe(window, 'load', function() {
    payment.save = payment.save.wrap(function(originalSaveMethod) {
        payment.originalSaveMethod = originalSaveMethod;
        //this form element is always set in payment object this.form or payment.form no need to bind to specific
        var opsValidator = new Validation(payment.form);
        if (!opsValidator.validate()) {
            return;
        }
        if ('foomandpsprofusion' == payment.currentMethod) {
            $('foomandpsprofusion_cc_owner').disable();
            $('foomandpsprofusion_cc_number').disable();
            $('foomandpsprofusion_expiration').disable();
            $('foomandpsprofusion_expiration_yr').disable();
            $('foomandpsprofusion_cc_cid').disable();
            $('foomandpsprofusion_cc_type').disable();

            originalSaveMethod();
            return;
        }
        originalSaveMethod();
    });

});
