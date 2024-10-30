<?php

// Require needed Braintree library
require_once(JIGOSHOP_BRAINTREE_GATEWAY_DIR .'/lib/Braintree.php');
 
add_action('plugins_loaded', 'jigoshop_braintree_init', 11);
 
function jigoshop_braintree_init() {

    // Execute only if Jigoshop is enabled
    if (!class_exists('jigoshop_payment_gateway')) {
        return;
    }
    
    /**
     * Load some basic styles
     */
    add_action('wp_print_styles', 'add_braintree_stylesheet');
    
    function add_braintree_stylesheet() {
        $myStyleUrl  = plugins_url('braintree.css', __FILE__);
        $myStyleFile = WP_PLUGIN_DIR . '/jigoshop-gateway-braintree/braintree.css';
        
        if (file_exists($myStyleFile)) {
            wp_register_style('jigoshop_braintree_styles', $myStyleUrl);
            wp_enqueue_style('jigoshop_braintree_styles');
        }
    }
    
    
    /**
     * Braintree extends default Jigoshop Payment Gateway class
     */
    class jigoshop_braintree extends jigoshop_payment_gateway {

        public function __construct() {
            parent::__construct();

            $this->id			= 'braintree';
            $this->icon 		= plugins_url('/images/braintree-credit-cards.png', __FILE__);
            $this->has_fields 	= false;

            $options = Jigoshop_Base::get_options();

            // Define user set variables
            $this->enabled			= $options->get('jigoshop_braintree_enabled');
            $this->environment		= $options->get('jigoshop_braintree_environment');
            $this->title 			= $options->get('jigoshop_braintree_title');
            $this->description 		= $options->get('jigoshop_braintree_description');
            $this->merchant_id 		= $options->get('jigoshop_braintree_merchant_id');
            $this->public_key		= $options->get('jigoshop_braintree_public_key');
            $this->private_key		= $options->get('jigoshop_braintree_private_key');
            $this->encryption_key 	= $options->get('jigoshop_braintree_encryption_key');
            $this->store_name	 	= $options->get('jigoshop_braintree_store_name');
            $this->settle			= $options->get('jigoshop_braintree_settle');

            // Hooks
            add_action('wp_enqueue_scripts',      array($this, 'payment_scripts'));
            add_action('init',                    array($this, 'make_braintree_payment'));
            add_action('jigoshop_update_options', array($this, 'process_admin_options'));
            add_action('receipt_braintree',       array($this, 'receipt_page'));
            add_action('admin_notices',           array($this, 'ssl_check'));

            add_option('jigoshop_braintree_enabled',        'no');
            add_option('jigoshop_braintree_evironment',     'production');
            add_option('jigoshop_braintree_description',    __('Pay with a credit card. You can enter your credit card information on the next page.', 'jigoshop_braintree'));
            add_option('jigoshop_braintree_title',          __('Braintree', 'jigoshop_braintree'));
            add_option('jigoshop_braintree_merchant_id',    '');
            add_option('jigoshop_braintree_public_key',     '');
            add_option('jigoshop_braintree_private_key',    '');
            add_option('jigoshop_braintree_encryption_key', '');
            add_option('jigoshop_braintree_store_name',     __('Braintree shop', 'jigoshop_braintree'));
            add_option('jigoshop_braintree_settle',         'checkout');
        }

        public function process_admin_options()
        {
            // Admin options
        }

		protected function get_default_options() {

			$defaults = array();

			$defaults[] = array( 
				'name'		=> __('Braintree', 'jigoshop_braintree'), 
				'type'		=> 'title', 
				'desc'		=> __('Braintree handles payments made with credit card directly to your bank account.', 'jigoshop_braintree') 
			);

			$defaults[] = array(
				'name'		=> __('Enable Braintree', 'jigoshop_braintree'),
				'desc' 		=> '',
				'tip' 		=> '',
				'id' 		=> 'jigoshop_braintree_enabled',
				'std' 		=> 'no',
				'type' 		=> 'select',
				'choices'	=> array(
					'no'	=> __('No', 'jigoshop_braintree'),
					'yes'	=> __('Yes', 'jigoshop_braintree')
				)
			);    

			$defaults[] = array(
				'name'		=> __('Environment', 'jigoshop_braintree'),
				'desc' 		=> '',
				'tip' 		=> '',
				'id' 		=> 'jigoshop_braintree_environment',
				'std' 		=> 'Production',
				'type' 		=> 'select',
				'choices'	=> array(
					'production'	=> __('Production', 'jigoshop_braintree'),
					'sandbox'	=> __('Sandbox', 'jigoshop_braintree')
				)
			);
			
            $defaults[] = array(
				'name'		=> __('Method Title', 'jigoshop_braintree'),
				'desc' 		=> '',
				'tip' 		=> __('This controls the title which the user sees during checkout.', 'jigoshop_braintree'),
				'id' 		=> 'jigoshop_braintree_title',
				'std' 		=> __('Braintree','jigoshop_braintree'),
				'type' 		=> 'text'
			);                        

            $defaults[] = array(
				'name'		=> __('Description', 'jigoshop_braintree'),
				'desc' 		=> '',
				'tip' 		=> __('This controls the title which the user sees during checkout.', 'jigoshop_braintree'),
				'id' 		=> 'jigoshop_braintree_description',
				'std' 		=> __('Description','jigoshop_braintree'),
				'type' 		=> 'longtext'
			);   

            $defaults[] = array(
				'name'		=> __('Merchant ID', 'jigoshop_braintree'),
				'desc' 		=> '',
				'tip' 		=> __('Please enter your Merchant ID', 'jigoshop_braintree'),
				'id' 		=> 'jigoshop_braintree_merchant_id',
				'std' 		=> '',
				'type' 		=> 'text'
			);   

            $defaults[] = array(
				'name'		=> __('Merchant ID', 'jigoshop_braintree'),
				'desc' 		=> '',
				'tip' 		=> __('Please enter your Merchant ID', 'jigoshop_braintree'),
				'id' 		=> 'jigoshop_braintree_merchant_id',
				'std' 		=> '',
				'type' 		=> 'text'
			);   


            $defaults[] = array(
				'name'		=> __('Public key', 'jigoshop_braintree'),
				'desc' 		=> '',
				'tip' 		=> __('Please enter your Braintree public key; this is needed in order to take payment!', 'jigoshop_braintree'),
				'id' 		=> 'jigoshop_braintree_public_key',
				'std' 		=> '',
				'type' 		=> 'text'
			);   

            $defaults[] = array(
				'name'		=> __('Private key', 'jigoshop_braintree'),
				'desc' 		=> '',
				'tip' 		=> __('Please enter your Braintree private key; this is needed in order to take payment!', 'jigoshop_braintree'),
				'id' 		=> 'jigoshop_braintree_private_key',
				'std' 		=> '',
				'type' 		=> 'text'
			);   

            $defaults[] = array(
				'name'		=> __('Encryption key', 'jigoshop_braintree'),
				'desc' 		=> '',
				'tip' 		=> __('Please enter your Braintree encryption key; this is needed in order to take payment!','jigoshop_braintree'),
				'id' 		=> 'jigoshop_braintree_encryption_key',
				'std' 		=> '',
				'type' 		=> 'textarea'
			);   

            $defaults[] = array(
				'name'		=> __('Braintree name', 'jigoshop_braintree'),
				'desc' 		=> '',
				'tip' 		=> __('This controls the name of store send with the payment.','jigoshop_braintree'),
				'id' 		=> 'jigoshop_braintree_store_name',
				'std' 		=> '',
				'type' 		=> 'text'
			);   

			$defaults[] = array(
				'name'		=> __('When to Settle', 'jigoshop_braintree'),
				'desc' 		=> '',
				'tip' 		=> __('When should charges be settled?', 'jigoshop_braintree'),
				'id' 		=> 'jigoshop_braintree_settle',
				'std' 		=> 'Checkout',
				'type' 		=> 'select',
				'choices'	=> array(
					'checkout'	=> __('Checkout', 'jigoshop_braintree'),
					'complete'	=> __('Complete', 'jigoshop_braintree')
				)
			);
                        
			return $defaults;
		}
		
        /**
        * Admin Panel Options
        * - Options for bits like 'title' and availability on a country-by-country basis
        */

        /**
         * There are no payment fields for Braintree, but we want to show the description if set.
         */
        public function payment_fields() {
            if ($this->description) {
                echo wpautop(wptexturize($this->description));
            }
        }


        /**
        * Check if SSL is enabled and notify the user
        **/
        public function ssl_check() {

            if ($this->enabled == 'no') {
                return;
            }

            // Show message if enabled and FORCE SSL is disabled and WordpressHTTPS plugin is not detected
            if (Jigoshop_Base::get_options()->get_option('jigoshop_force_ssl_checkout') == 'no' && !class_exists('WordPressHTTPS')) {
?>
                <div class="error">
                    <p>
                    <?php echo sprintf(__('Braintree can not be enabled because the <a href="%s">force SSL option</a> is disabled; your checkout may not be secure! Please enable SSL and ensure your server has a valid SSL certificate.', 'jigoshop_braintree'), admin_url('admin.php?page=jigoshop_settings&tab=general')); ?>
                    </p>
                </div>
<?php
            }
        }

        /**
        * Check if this gateway is enabled and using correct currency. Only USD allowed atm.
        */
        public function is_available() {

            if ($this->enabled == 'yes') {

                if (!is_ssl()) {
                    return false;
                }
/*
                // Currency check
                if (!in_array(Jigoshop_Base::get_options()->get_option('jigoshop_currency'), array('USD'))) {
                    return false;
                }
*/
                // Required fields check
                if (!$this->merchant_id || !$this->public_key || !$this->private_key || !$this->encryption_key) {
                    return false;
                }

                return true;
            }

            return false;
        }

        /**
        * This function actually generates also the form for getting credit card information
        */
        public function generate_braintree_form($order_id) {

            $order = new jigoshop_order($order_id);
?>	
            <fieldset>
                <form action="<?php echo add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, add_query_arg('braintree', '1', get_permalink(Jigoshop_Base::get_options()->get_option('jigoshop_pay_page_id'))))); ?>" method="POST" id="braintree-payment-form">
                    <p class="form-row form-row-first braintree-card-number-row">
                    <span class="braintree-payment-errors"></span>
                        <label for="braintree-card-number"><?php _e('Credit Card number', 'jigoshop_braintree'); ?><span class="required">*</span></label>
                        <input type="text" class="input-text braintree-card-number" id="braintree-card-number" data-encrypted-name="number" />
                    </p>
                    <div class="clear"></div>
                    <p class="form-row form-row-first">
                        <label><?php _e('Expiration date', 'jigoshop_braintree'); ?><span class="required">*</span></label>
                        <select id="braintree-card-expire-month" class="jigoshop-select">
                            <option value=""><?php _e('Month', 'jigoshop_braintree'); ?></option>
<?php
                            for ($i = 1; $i <= 12; $i++) {
                                $month = date('m', mktime(0, 0, 0, $i, 1));
                                echo '<option value="' . $month . '">' . $month . '</option>';
                            }
?>
                        </select>
                        <select id="braintree-card-expire-year" class="braintree-select">
                            <option value=""><?php _e('Year', 'jigoshop_braintree'); ?></option>
<?php
                            for ($exp = date('Y'); $exp <= date('Y') + 15; $exp++) {
                                echo '<option value="' . $exp . '">' . $exp . '</option>';
                            }
?>
                        </select>
                        <input type="hidden" id="braintree_expiration" data-encrypted-name="expiration_date" />
                    </p>
                    <p class="form-row form-row-last">
                        <label><?php _e('CVC', 'jigoshop_braintree'); ?><span class="required">*</span></label>
                        <input type="password" class="input-text braintree-card-cvc" id="braintree-card-cvc" maxlength="4" style="width:4em;" data-encrypted-name="cvv" />
                    </p>
                    <input type="hidden" name="braintree-order-id" value="<?php echo $order_id; ?>"/>
                    <div class="clear"></div>
                    <input type="submit" class="submit-braintree button alt" name="submit-braintree" value="<?php _e('Submit payment', 'jigoshop_braintree'); ?>" />
                    <a class="button cancel" href="<?php echo esc_url( $order->get_cancel_order_url() ); ?>"><?php _e('Cancel order &amp; restore cart', 'jigoshop_braintree'); ?></a>
                </form>
            </fieldset>
            <script type="text/javascript" src="https://js.braintreegateway.com/v1/braintree.js"></script>
            <script type="text/javascript">
                //setup braintree encryption
                var braintree = Braintree.create('<?php echo $this->encryption_key; ?>');
                braintree.onSubmitEncryptForm('braintree-payment-form');

                //pass expiration dates in original format
                function jigoshop_updateBraintreeCardExp()
                {
                    jQuery('#braintree_expiration').val(jQuery('#braintree-card-expire-month').val() + '/' + jQuery('#braintree-card-expire-year').val());
                }
                jQuery('#braintree-card-expire-month, #braintree-card-expire-year').change(function() {
                    jigoshop_updateBraintreeCardExp();
                });
                jigoshop_updateBraintreeCardExp();						
            </script>
<?php	
        }

        /**
        * Process the payment and return the result
        */
        public function process_payment($order_id) {
            
            $order = new jigoshop_order($order_id);
            
            jigoshop_cart::empty_cart();
            
            return array(
                'result' 	=> 'success',
                'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(Jigoshop_Base::get_options()->get_option('jigoshop_pay_page_id'))))
            );
        }

        /**
         * payment_scripts function.
         *
         * Outputs scripts used for braintree payment
         */
        public function payment_scripts() {
            
            wp_localize_script( 'jigoshop_braintree', 'jigoshop_braintree_params', array('key' => $this->public_key));
            
        }

        public function receipt_page($order) {

            echo '<p>' . __('Thank you for your order, enter your credit card information below to make the payment.', ' jigoshop_braintree') . '</p>';

            jigoshop::show_messages();

            $this->generate_braintree_form($order);
        }
        
        /**
          * Setup API creds
          */
        // CHANGED: '' to 'protected'
        public static function setupAPI()
        {
            //setup api
            Braintree_Configuration::environment(Jigoshop_Base::get_options()->get_option('jigoshop_braintree_environment'));
            Braintree_Configuration::merchantId(Jigoshop_Base::get_options()->get_option('jigoshop_braintree_merchant_id'));
            Braintree_Configuration::publicKey(Jigoshop_Base::get_options()->get_option('jigoshop_braintree_public_key'));
            Braintree_Configuration::privateKey(Jigoshop_Base::get_options()->get_option('jigoshop_braintree_private_key'));
        }
	
        /**
          * Make Braintree payment if token was generated correctly
          */
        public function make_braintree_payment() {
            
            if (!isset($_GET['braintree'])) {
                return;
            }

            $order_id = isset($_POST['braintree-order-id']) ? $_POST['braintree-order-id'] : jigoshop::add_error(__('Invalid order id given.', 'jigoshop_braintree'));
            $order    = new jigoshop_order($order_id);
            
            //charge
            $braintree_amount          = $order->order_total;
            
            $braintree_number          = isset($_POST['number'])          ? intval(sanitize_text_field($_POST['number']))  : jigoshop::add_error(__('Please check your credit card details.', 'jigoshop_braintree'));
            $braintree_expiration_date = isset($_POST['expiration_date']) ? sanitize_text_field($_POST['expiration_date']) : jigoshop::add_error(__('Please check your credit card details.', 'jigoshop_braintree'));
            $braintree_cvv             = isset($_POST['cvv'])             ? intval(sanitize_text_field($_POST['cvv']))     : jigoshop::add_error(__('Please check your credit card details.', 'jigoshop_braintree'));


            self::setupAPI();

            try { 


                $response = Braintree_Transaction::sale(array(
                        'amount'     => $braintree_amount,
						'orderId'    => $order_id,
                        'creditCard' => array(
                            'number'         => $braintree_number,
                            'expirationDate' => $braintree_expiration_date,
                            'cardholderName' => trim($order->billing_first_name . ' ' . $order->billing_last_name),
                            'cvv'            => $braintree_cvv
                        ),
                    )
                );


            } catch (Exception $e) {				
                jigoshop::add_error($e->getMessage());
            }

            if (!$response->success) {
                jigoshop::add_error(__('There was an error when processing your payment.', 'jigoshop_braintree'));
                return;
            }

            //successful charge			
            $transaction_id = $response->transaction->id;
            update_post_meta($order_id, 'jigoshop_braintree_transaction_id', $transaction_id);

            if ($this->settle == 'checkout') {

                $response = Braintree_Transaction::submitForSettlement($transaction_id);

                if ($response->success) {
                    
                    update_post_meta($order_id, 'jigoshop_braintree_settled', 'yes');

                    // Add order note
                    $order->add_order_note(sprintf(__('Braintree payment completed (ID: %s).', 'jigoshop_braintree'), $transaction_id));

                    // Payment complete
                    $order->payment_complete();	

                    //redirect to thank you page
                    wp_redirect(add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink(Jigoshop_Base::get_options()->get_option('jigoshop_thanks_page_id')))));
                    exit;
                    
                } else {										
                    jigoshop::add_error(__('There was an error when settling your payment.', 'jigoshop_braintree'));
                }

            } else {
                //will settle when entering complete status
                update_post_meta($order_id, 'jigoshop_braintree_settled', 'no');

                // Add order note
                $order->add_order_note(sprintf(__('Braintree payment authorized (ID: %s, Amount: %s).', 'jigoshop_braintree'), $transaction_id, $braintree_amount));	

                //redirect to thank you page
                wp_redirect(add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink(Jigoshop_Base::get_options()->get_option('jigoshop_thanks_page_id')))));
                exit;
            }
            
        }
	} // end jigoshop_braintree
	
	/**
 	* Add the Gateway to Jigoshop
 	**/
    add_filter('jigoshop_payment_gateways', 'add_braintree_gateway');

	function add_braintree_gateway($methods) {
		$methods[] = 'jigoshop_braintree';
		return $methods;
	}
	
	/**
	* Settle unsettled orders when they enter complete status
	**/
    add_action('order_status_completed', 'jigoshop_braintree_order_status_completed');
    
	function jigoshop_braintree_order_status_completed($order_id) {		
        
		if (get_post_meta($order_id, 'jigoshop_braintree_settled', true) == 'no') {
            
			//settle it
			$transaction_id = get_post_meta($order_id, 'jigoshop_braintree_transaction_id', true);
			$order = new jigoshop_order($order_id);
			$order->get_order($order_id);
			
            $braintree_amount = (isset($_REQUEST['order_total'])) ? $_REQUEST['order_total'] : $order->order_total;
            
			if (empty($transaction_id)) {
				jigoshop::add_error(__('This order is unsettled, but I could not find the transaction id.', 'jigoshop_braintree'));
				return;
			}
			
			jigoshop_braintree::setupAPI();
						
			$response = Braintree_Transaction::submitForSettlement($transaction_id, $braintree_amount);
            
			if ($response->success) {
                
				update_post_meta($order_id, 'jigoshop_braintree_settled', 'yes');
				
				// Add order note
				$order->add_order_note(sprintf(__('Braintree payment settled (ID: %s, Amount: %s).', 'jigoshop_braintree'), $transaction_id, $braintree_amount));
					
				// Payment complete
				$order->payment_complete();			
                
			} else {										
				jigoshop::add_error(__('There was an error when settling this payment.', 'jigoshop_braintree'));
			}
		}
	}
	
	
	/**
	* Void orders when they enter cancelled status
	**/
    add_action('order_status_cancelled', 'jigoshop_braintree_order_status_cancelled');
    
	function jigoshop_braintree_order_status_cancelled($order_id) {
        
		if (get_post_meta($order_id, 'jigoshop_braintree_settled', true) == 'no') {
            
			//settle it
			$transaction_id = get_post_meta($order_id, 'jigoshop_braintree_transaction_id', true);
			$order = new jigoshop_order($order_id);
			$order->get_order($order_id);
						
			if (empty($transaction_id))	{
				jigoshop::add_error(__('This order is unsettled, but I could not find the transaction id.', 'jigoshop_braintree'));
				return;
			}
			
			jigoshop_braintree::setupAPI();
						
			$response = Braintree_Transaction::void($transaction_id);
            
			if ($response->success) {
                
				delete_post_meta($order_id, 'jigoshop_braintree_settled');
				
				// Add order note
				$order->add_order_note(sprintf(__('Braintree payment voided (ID: %s).', 'jigoshop_braintree'), $transaction_id));																	
			} else {										
				jigoshop::add_error(__('There was an error when voiding this payment.', 'jigoshop_braintree'));
			}
            
		} else {
			jigoshop::add_error(__('This order has settled. You should refund it.', 'jigoshop_braintree'));
		}
	}
	
	/**
	* Refund orders when they enter refunded status
	**/
    add_action('order_status_refunded', 'jigoshop_braintree_order_status_refunded');
    
	function jigoshop_braintree_order_status_refunded($order_id) {
        
		if (get_post_meta($order_id, 'jigoshop_braintree_settled', true) == 'yes') {
            
			//settle it
			$transaction_id = get_post_meta($order_id, 'jigoshop_braintree_transaction_id', true);
			$order = new jigoshop_order($order_id);
			$order->get_order($order_id);
						
			if (empty($transaction_id)) {
//				exit('no id');
				jigoshop::add_error(__('This order is settled, but I could not find the transaction id.', 'jigoshop_braintree'));
				return;
			}
			
			jigoshop_braintree::setupAPI();
						
			$response = Braintree_Transaction::refund($transaction_id);
            
			if ($response->success) {
                
				delete_post_meta($order_id, 'jigoshop_braintree_settled');
				
				// Add order note
				$order->add_order_note(sprintf(__('Braintree payment refunded (ID: %s).', 'jigoshop_braintree'), $transaction_id));																	
			} else {										
				//try to void it
				$response = Braintree_Transaction::void($transaction_id);
                
				if ($response->success)	{
					delete_post_meta($order_id, 'jigoshop_braintree_settled');
					
					// Add order note
					$order->add_order_note(sprintf(__('Payment had not yet settled. Braintree payment voided (ID: %s).', 'jigoshop_braintree'), $transaction_id));		
				} else {	
					jigoshop::add_error(__('There was an error when refunding this payment.', 'jigoshop_braintree'));
				}
			}
		} else {
//			exit('unsettled');
			jigoshop::add_error(__('This order is unsettled. You should cancel it.', 'jigoshop_braintree'));
		}
	}
}
if(is_admin()){
    add_filter('plugin_action_links_' . plugin_basename(dirname(JIGOSHOP_BRAINTREE_GATEWAY_DIR) . '/bootstrap.php'), function($links) {

        $links[] = '<a href="https://www.jigoshop.com/documentation/braintree-payment-gateway/" target="_blank">Documentation</a>';
        $links[] = '<a href="https://www.jigoshop.com/support/" target="_blank">Support</a>';
        $links[] = '<a href="https://wordpress.org/support/view/plugin-reviews/jigoshop#postform" target="_blank">Rate Us</a>';
        $links[] = '<a href="https://www.jigoshop.com/product-category/extensions/" target="_blank">More plugins for Jigoshop</a>';
        return $links;
    });
}