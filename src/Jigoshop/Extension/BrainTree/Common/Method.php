<?php

namespace Jigoshop\Extension\BrainTree\Common;

use Braintree\Exception\Authentication;
use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Order;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Order as OrderE;
use Jigoshop\Exception;
use Jigoshop\Helper\Api;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Integration;
use Jigoshop\Integration\Helper\Render;
use Jigoshop\Payment\Method as MethodInterface;
use Jigoshop\Payment\Method3;
use Jigoshop\Service\CartService;
use Jigoshop\Service\OrderService;
use Jigoshop\Service\OrderServiceInterface;


class Method implements Method3
{
    const ID = "braintree";
    private $options;
    private $cart;
    private $settings;
    private $orderService;
    private $messages;

    public function __construct(
        Options $options,
        CartService $cart,
        OrderServiceInterface $orderService,
        Messages $messages
    ) {
        $this->options = $options;
        $this->settings = $this->__getOptions();
        $this->cart = $cart;
        $this->orderService = $orderService;
        $this->messages = $messages;


        Styles::add('jigoshop_braintree', JIGOSHOP_BRAINTREE_GATEWAY_URL . '/assets/css/style.css');
        add_action('wp_enqueue_scripts', array($this, 'paymentScripts'));


//        add_filter('jigoshop\pay\render', array($this, 'renderPay'), 10, 2);
    }

    public static function onCompleted($order)
    {

        if (get_post_meta($order->getId(), 'jigoshop_braintree_settled', true) == 'no') {

            //settle it
            $transaction_id = get_post_meta($order->getId(), 'jigoshop_braintree_transaction_id', true);


            $braintree_amount = (isset($_REQUEST['order_total'])) ? $_REQUEST['order_total'] : $order->getTotal();

            if (empty($transaction_id)) {
                Integration::getMessages()->addError(__('This order is unsettled, but I could not find the transaction id.',
                    'jigoshop_braintree'));
                return;
            }

            self::setupAPI();

            $response = \Braintree_Transaction::submitForSettlement($transaction_id, $braintree_amount);

            if ($response->success) {

                update_post_meta($order->getId(), 'jigoshop_braintree_settled', 'yes');

                // Add order note
                Integration::getOrderService()->addNote($order,
                    sprintf(__('Braintree payment settled (ID: %s, Amount: %s).', 'jigoshop_braintree'),
                        $transaction_id, $braintree_amount));


                // Payment complete
                $order->setStatus(\Jigoshop\Helper\Order::getStatusAfterCompletePayment($order),
                    sprintf(__('Braintree payment completed (ID: %s).', 'jigoshop_braintree'),
                        $transaction_id));
                Integration::getOrderService()->save($order);

            } else {
                Integration::getMessages()->addError(__('There was an error when settling this payment.',
                    'jigoshop_braintree'));
            }
        }
    }

    public static function onCancelled($order)
    {
        if (get_post_meta($order->getId(), 'jigoshop_braintree_settled', true) == 'no') {

            //settle it
            $transaction_id = get_post_meta($order->getId(), 'jigoshop_braintree_transaction_id', true);


            if (empty($transaction_id)) {
                Integration::getMessages()->addError(__('This order is unsettled, but I could not find the transaction id.',
                    'jigoshop_braintree'));
                return;
            }

            self::setupAPI();

            $response = \Braintree_Transaction::void($transaction_id);

            if ($response->success) {

                delete_post_meta($order->getId(), 'jigoshop_braintree_settled');

                // Add order note
                Integration::getOrderService()->addNote($order,
                    sprintf(__('Braintree payment voided (ID: %s).', 'jigoshop_braintree'), $transaction_id));
            } else {
                Integration::getMessages()->addError(__('There was an error when voiding this payment.',
                    'jigoshop_braintree'));
            }

        } else {
            Integration::getMessages()->addError(__('This order has settled. You should refund it.',
                'jigoshop_braintree'));
        }

    }

    /**
     * @return string ID of payment method.
     */
    public function getId()
    {
        return self::ID;
    }

    /**
     * @return string Human readable name of method.
     */


    private function getDefaultOptions()
    {
        return $this->options->get('payment.' . self::ID, array(
            'enabled' => false,
            'title' => '',
            'description' => '',
            'store_name' => '',
            'api_key' => '',
            'merchant_id' => '',
            'public_key' => '',
            'private_key' => '',
            'settle' => false,
            'test_mode' => false,
            'environment' => false,
	        'adminOnly' => false,
        ));


    }

    private function __getOptions()
    {
        return array_merge($this->getDefaultOptions(), $this->options->get($this->getId(), array()));
    }

    public function getName()
    {
        return is_admin() ? $this->getLogo() . ' ' . __('Braintree', 'jigoshop_braintree') : $this->settings['title'];
    }

    private function getLogo()
    {
        return '<img src="' . JIGOSHOP_BRAINTREE_GATEWAY_URL . '/assets/images/braintree-credit-cards.png' . '" alt="Logo" class="payment-logo" />';
    }

    /**
     * @return bool Whether current method is enabled and able to work.
     */
    public function isEnabled()
    {
        return ($this->settings['merchant_id'] && $this->settings['private_key'] && $this->settings['public_key']
            && $this->settings['enabled']);
    }

    /**
     * @return array List of options to display on Payment settings page.
     */
    public function getOptions()
    {
        return array(
            array(
                'name' => sprintf('[%s][enabled]', self::ID),
                'title' => __('Enable Braintree', 'jigoshop_braintree'),
                'id' => 'jigoshop_braintree_enabled',
                'type' => 'checkbox',
                'classes' => array('switch-medium'),
                'checked' => $this->settings['enabled'],
            ),
            array(
                'title' => __('Method Title', 'jigoshop_braintree'),
                'tip' => __('This controls the title which the user sees during checkout.', 'jigoshop_braintree'),
                'id' => 'jigoshop_braintree_title',
                'name' => '[' . self::ID . '][title]',
                'type' => 'text',
                'value' => $this->settings['title'],
            ),
            array(
                'title' => __('Description', 'jigoshop_braintree'),
                'tip' => __('This controls the description which the user sees during checkout.',
                    'jigoshop_braintree'),
                'id' => 'jigoshop_braintree_description',
                'name' => '[' . self::ID . '][description]',
                'type' => 'text',
                'value' => $this->settings['description']
            ),
            array(
                'title' => __('Merchant ID', 'jigoshop_braintree'),
                'tip' => __('Please enter your Merchant ID', 'jigoshop_braintree'),
                'id' => 'jigoshop_braintree_merchant_id',
                'name' => '[' . self::ID . '][merchant_id]',
                'type' => 'text',
                'value' => $this->settings['merchant_id'],
            ),
            array(
                'title' => __('Public key', 'jigoshop_braintree'),
                'tip' => __('Please enter your Braintree public key; this is needed in order to take payment!',
                    'jigoshop_braintree'),
                'id' => 'jigoshop_braintree_public_key',
                'name' => '[' . self::ID . '][public_key]',
                'type' => 'text',
                'value' => $this->settings['public_key'],
            ),
            array(
                'title' => __('Private key', 'jigoshop_braintree'),
                'tip' => __('Please enter your Braintree private key; this is needed in order to take payment!',
                    'jigoshop_braintree'),
                'id' => 'jigoshop_braintree_private_key',
                'name' => '[' . self::ID . '][private_key]',
                'type' => 'text',
                'value' => $this->settings['private_key'],

            ),

            array(
                'title' => __('Braintree name', 'jigoshop_braintree'),
                'tip' => __('This controls the name of the store sent with the payment.', 'jigoshop_braintree'),
                'id' => 'jigoshop_braintree_store_name',
                'name' => '[' . self::ID . '][store_name]',
                'type' => 'text',
                'value' => $this->settings['store_name'],

            ),
            array(
                'title' => __('Braintree Sandbox mode', 'jigoshop_braintree'),
                'id' => 'jigoshop_braintree_environment',
                'tip' => __('Choose the environment (ON => Sandbox, OFF => Production)', 'jigoshop_braintree'),
                'name' => '[' . self::ID . '][environment]',
                'type' => 'checkbox',
                'classes' => array('switch-medium'),
                'checked' => $this->settings['environment']
            ),
	        array(
		        'name' => sprintf('[%s][adminOnly]', self::ID),
		        'title' => __('Enable Only for Admin', 'jigoshop_braintree'),
		        'type' => 'checkbox',
		        'description' => __('Enable this if you would like to test it only for Site Admin', 'jigoshop_braintree'),
		        'checked' => $this->settings['adminOnly'],
		        'classes' => array('switch-medium'),
	        ),
        );
    }

    /**
     * Validates and returns properly sanitized options.
     *
     * @param $settings array Input options.
     *
     * @return array Sanitized result.
     */
    public function validateOptions($settings)
    {
        $settings['enabled'] = $settings['enabled'] == 'on';
        $settings['test_mode'] = $settings['test_mode'] == 'on';
        $settings['environment'] = $settings['environment'] == 'on';
	    $settings['adminOnly'] = $settings['adminOnly'] == 'on';
        $settings['title'] = trim(htmlspecialchars(strip_tags($settings['title'])));
        $settings['store_name'] = trim(htmlspecialchars(strip_tags($settings['store_name'])));
        $settings['description'] = trim(htmlspecialchars(strip_tags($settings['description'],
            '<p><a><strong><em><b><i>')));

        $settings['merchant_id'] = trim(strip_tags($settings['merchant_id']));
        $settings['public_key'] = trim(strip_tags($settings['public_key']));
        $settings['private_key'] = trim(strip_tags($settings['private_key']));

        return $settings;
    }

    public function renderPay($content, $order)
    {


    }

    public function paymentScripts()
    {

        Scripts::localize('jigoshop_braintree', 'jigoshop_braintree_params',
            array('key' => $this->settings['public_key']));

        Scripts::add('jigoshop_braintree', JIGOSHOP_BRAINTREE_GATEWAY_URL . '/assets/js/braintree.js', ['jquery'],
            ['in_footer' => true]);

    }

    /**
     * Renders method fields and data in Checkout page.
     */
    public function render()
    {
        self::setupAPI();
        try {
            $token = \Braintree_ClientToken::generate();
        } catch (Authentication $exception) {
            echo 'Braintree is not available because of wrong merchant wrong authentication credentials. Please contact website admin';
            return;
        }
        $cart = Integration::getCart();
        $amount = $cart->getTotal();
        echo Render::get('braintree', 'frontend/payment_fields', array(
            'token' => $token,
            'amount' => $amount
        ));
    }

    /**
     * @param Order $order Order to process payment for.
     *
     * @return string URL to redirect to.
     * @throws Exception On any payment error.
     */
    public function process($order)
    {
        return $this->makeBraintreePayment($order);
    }


    public static function setupAPI()
    {
        require_once(JIGOSHOP_BRAINTREE_GATEWAY_DIR . '/lib/Braintree.php');

        //setup api

        $options = \Jigoshop\Helper\Options::getOptions('payment.' . self::ID);
        \Braintree_Configuration::environment($options['environment'] ? "sandbox" : "production");
        \Braintree_Configuration::merchantId($options['merchant_id']);
        \Braintree_Configuration::publicKey($options['public_key']);
        \Braintree_Configuration::privateKey($options['private_key']);
    }

    /**
     * @param Order $order
     * @return mixed
     */
    public function makeBraintreePayment($order)
    {
        require_once(JIGOSHOP_BRAINTREE_GATEWAY_DIR . '/lib/Braintree.php');

        if (!isset($_POST['payment_method_nonce'])) {
            $this->messages->addError(__('Wrong payment method nonce', 'jigoshop_braintree'));
        }
        $payment_method_nonce = $_POST['payment_method_nonce'];

        $braintree_amount = $order->getTotal();
        self::setupAPI();

        try {

            /**@var Order $order */
            $billing = $order->getCustomer()->getBillingAddress();

            $response = \Braintree_Transaction::sale(array(
                    'amount' => $braintree_amount,
                    'orderId' => $order->getId(),
                    "paymentMethodNonce" => $payment_method_nonce,
                )
            );
        } catch (Exception $e) {
            $this->messages->addError($e->getMessage());
        }

        if (!$response->success) {
            $this->messages->addError(__('There was an error when processing your payment.', 'jigoshop_braintree'));
            return;
        }

        //successful charge
        $transaction_id = $response->transaction->id;
        update_post_meta($order->getId(), 'jigoshop_braintree_transaction_id', $transaction_id);


        $response = \Braintree_Transaction::submitForSettlement($transaction_id);

        if ($response->success) {

            update_post_meta($order->getId(), 'jigoshop_braintree_settled', 'yes');

            // Add order note
            $order->setStatus(\Jigoshop\Helper\Order::getStatusAfterCompletePayment($order),
                sprintf(__('Braintree payment completed (ID: %s).', 'jigoshop_braintree'),
                    $transaction_id));
            $this->orderService->save($order);

            //redirect to thank you page
            wp_redirect(\Jigoshop\Helper\Order::getThankYouLink($order));
            exit;

        } else {

            $this->messages->addError(__('There was an error when settling your payment.', 'jigoshop_braintree'));
        }

    }

/* Whenever method was enabled by the user.
*
* @return boolean Method enable state.
*/
	public function isActive()
	{
		if (isset($this->settings['enabled'])) {
			return $this->settings['enabled'];
		}
	}
	
	/**
	 * Set method enable state.
	 *
	 * @param boolean $state Method enable state.
	 *
	 * @return array Method current settings (after enable state change).
	 */
	public function setActive($state)
	{
		$this->settings['enabled'] = $state;
		
		return $this->settings;
	}
	
	/**
	 * Whenever method was configured by the user (all required data was filled for current scenario).
	 *
	 * @return boolean Method config state.
	 */
	public function isConfigured()
	{
		
		if (isset($this->settings['merchant_id']) && $this->settings['merchant_id']
			&& isset($this->settings['public_key']) && $this->settings['public_key']
			&& isset($this->settings['private_key']) && $this->settings['private_key']
		) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Whenever method has some sort of test mode.
	 *
	 * @return boolean Method test mode presence.
	 */
	public function hasTestMode()
	{
		return true;
	}
	
	/**
	 * Whenever method test mode was enabled by the user.
	 *
	 * @return boolean Method test mode state.
	 */
	public function isTestModeEnabled()
	{
		if (isset($this->settings['environment'])) {
			return $this->settings['environment'];
		}
	}
	
	/**
	 * Set Method test mode state.
	 *
	 * @param boolean $state Method test mode state.
	 *
	 * @return array Method current settings (after test mode state change).
	 */
	public function setTestMode($state)
	{
		$this->settings['environment'] = $state;
		
		return $this->settings;
	}
	
	/**
	 * Whenever method requires SSL to be enabled to function properly.
	 *
	 * @return boolean Method SSL requirment.
	 */
	public function isSSLRequired()
	{
		if (true == $this->settings['environment']) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Whenever method is set to enabled for admin only.
	 *
	 * @return boolean Method admin only state.
	 */
	public function isAdminOnly()
	{
		if (true == $this->settings['adminOnly']) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Sets admin only state for the method and returns complete method options.
	 *
	 * @param boolean $state Method admin only state.
	 *
	 * @return array Complete method options after change was applied.
	 */
	public function setAdminOnly($state)
	{
		$this->settings['adminOnly'] = $state;
		
		return $this->settings;
	}
}