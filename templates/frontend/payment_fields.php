<div class="braintree-optionss">
    <div class="braintree-options-list">
        <input type="radio" name="braintree-type" class="braintree-type" value="card" />
        <fieldset class="braintree-details">

            <p class="form-row form-row-first braintree-card-number-row">
                <span class="braintree-payment-errors"></span>
                <label for="braintree-card-number"><?php _e('Credit Card number', 'jigoshop_braintree'); ?>
                    <span class="required">*</span>
                </label>
            <div id="braintree-card-number" class="braintree-field"></div>
            </p>
            <div class="clear"></div>
            <p class="form-row form-row-last">
                <label><?php _e('CVC', 'jigoshop_braintree'); ?>
                    <span class="required">*</span>
                </label>
            <div class="input-text braintree-card-cvc braintree-field" id="braintree-card-cvc"></div>
            </p>

            <div class="clear"></div>
            <p class="form-row form-row-first">
                <label><?php _e('Expiration date', 'jigoshop_braintree'); ?>
                    <span class="required">*</span>
                </label>
            </p>
            <div id="expiration-date" class="braintree-field">

            <input type="hidden" name="action" value="purchase">

        </fieldset>

    </div>
    <div class="clear"></div>
    <hr style="background-color: grey; height: 2px;">
    <div class="braintree-option braintree-option_paypal">

        <div class="clear"></div>
        <input type="radio" name="braintree-type" class="braintree-type pull-left" value="paypal"
        style="margin-right: 20px;"/>

        <fieldset class="braintree-details pull-left" id="braintree-paypal">
            <script src="https://www.paypalobjects.com/api/button.js?"
                    data-merchant="braintree"
                    data-id="paypal-button"
                    data-button="checkout"
                    data-color="gold"
                    data-size="medium"
                    data-shape="pill"
                    data-button_type="button"
                    data-button_disabled="false">

            </script>
            <input type="hidden" name="payment_method_nonce">
            <input type="hidden" name="amount" id="braintree_amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="channel" id="braintree_channel" value="JigoLtd_SP">
            <input type="hidden" name="braintree_token" id="braintree_token" value="<?php echo $token; ?>">
        </fieldset>
        <span class="pull-right" style="font-size: 11px; margin-top: 10px;">
            Please accept the Terms and Conditions before proceeding to payment via PayPal
        </span>
    </div>
    <div class="clear"></div>
</div>


<!--<script type="text/javascript" src="https://js.braintreegateway.com/v1/braintree.js"></script>-->
<script src="https://js.braintreegateway.com/web/3.12.0/js/client.min.js"></script>
<script src="https://js.braintreegateway.com/web/3.12.0/js/hosted-fields.min.js"></script>
<script src="https://js.braintreegateway.com/web/3.12.0/js/paypal.min.js"></script>