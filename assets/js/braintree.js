var form = document.querySelector('#checkout');
// braintreeSubmitButton = document.querySelector('#braintree-pay-card');
braintreeSubmitButton = document.querySelector('.btn-success');
// console.log(braintreeSubmitButton);
braintree_amount = document.querySelector('#braintree_amount').value;
braintree_token = document.querySelector('#braintree_token').value;


var paypalButton = document.querySelector('#paypal-button');


jQuery(document).ready(function () {

    braintreeSubmitButton.addEventListener('click', function (e) {
        var value = jQuery('input[name="braintree-type"]:checked').val();
        console.log(value);
        if (value == 'paypal') {
            console.log('click');
            paypalButton.click();
            e.preventDefault();
            return false;
        }
    });

    jQuery('input.braintree-type').change(function (e) {
        e.stopImmediatePropagation();
    });

    braintree.client.create({
        authorization: braintree_token
    }, function (clientErr, clientInstance) {

        // Stop if there was a problem creating the client.
        // This could happen if there is a network error or if the authorization
        // is invalid.
        if (clientErr) {
            console.error('Error creating client:', clientErr);
            return;
        }

        braintree.hostedFields.create({
            client: clientInstance,
            styles: {
                'input': {
                    'font-size': '14px'
                },
                'input.invalid': {
                    'color': 'red'
                },
                'input.valid': {
                    'color': 'green'
                }
            },
            fields: {
                number: {
                    selector: '#braintree-card-number'
                },
                expirationDate: {
                    selector: '#expiration-date'
                },
                cvv: {
                    selector: '#braintree-card-cvc'
                }
            }
        }, function (hostedFieldsErr, hostedFieldsInstance) {
            if (hostedFieldsErr) {
                console.error(hostedFieldsErr);
                return;
            }

            braintreeSubmitButton.removeAttribute('disabled');
            braintreeSubmitButton.addEventListener('click', function (event) {
                // form.addEventListener('submit', function (event) {
                event.preventDefault();
                hostedFieldsInstance.tokenize(function (tokenizeErr, payload) {
                    if (tokenizeErr) {
                        console.error(tokenizeErr);
                        return;
                    }

                    // If this was a real integration, this is where you would
                    // send the nonce to your server.
                    console.log('Got a nonce: ' + payload.nonce);
                    jQuery('input[name="payment_method_nonce"]').val(payload.nonce);
                    form.submit();

                });
            }, false);
        });

        // Create a PayPal component.
        braintree.paypal.create({
            client: clientInstance
        }, function (paypalErr, paypalInstance) {

            // Stop if there was a problem creating PayPal.
            // This could happen if there was a network error or if it's incorrectly
            // configured.
            if (paypalErr) {
                console.error('Error creating PayPal:', paypalErr);
                return;
            }

            // Enable the button.
            paypalButton.removeAttribute('disabled');

            // When the button is clicked, attempt to tokenize.
            paypalButton.addEventListener('click', function (event) {

                // Because tokenization opens a popup, this has to be called as a result of
                // customer action, like clicking a button—you cannot call this at any time.
                paypalInstance.tokenize({
                    flow: 'vault'
                }, function (tokenizeErr, payload) {

                    // Stop if there was an error.
                    if (tokenizeErr) {
                        if (tokenizeErr.type !== 'CUSTOMER') {
                            console.error('Error tokenizing:', tokenizeErr);
                        }
                        return;
                    }

                    // Tokenization succeeded!
                    paypalButton.setAttribute('disabled', true);
                    jQuery('input[name="payment_method_nonce"]').val(payload.nonce);
                    form.submit();

                });

            }, false);

        });


    });

});
