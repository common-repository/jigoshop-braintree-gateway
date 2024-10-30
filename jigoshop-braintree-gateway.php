<?php
/*
 * Plugin name: Jigoshop Braintree Gateway
 * Description: This plugin provides a Braintree payment gateway option to Jigoshop.
 * Author: Jigo Ltd
 * Version: 3.1.2
 * Plugin URI: https://wordpress.org/plugins/jigoshop-braintree-gateway/
 * Author URI: http://jigoshop.com
 */

// Define plugin name
define('JIGOSHOP_BRAINTREE_GATEWAY_NAME', 'Jigoshop Braintree Gateway');

add_action('plugins_loaded', function () {
	load_plugin_textdomain('jigoshop_braintree_gateway', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	if (class_exists('\Jigoshop\Core')) {
		// Define plugin directory for inclusions
		define('JIGOSHOP_BRAINTREE_GATEWAY_DIR', dirname(__FILE__));
		// Define plugin URL for assets
		define('JIGOSHOP_BRAINTREE_GATEWAY_URL', plugins_url('', __FILE__));
		//Check version.
		if (\Jigoshop\addRequiredVersionNotice(JIGOSHOP_BRAINTREE_GATEWAY_NAME, '2.1.6')) {
			return;
		}
		require_once(JIGOSHOP_BRAINTREE_GATEWAY_DIR . '/src/Jigoshop/Extension/BrainTree/Common.php');

		//Init components.
		if (is_admin()) {
			require_once(JIGOSHOP_BRAINTREE_GATEWAY_DIR . '/src/Jigoshop/Extension/BrainTree/Admin.php');
		}
	} elseif (class_exists('jigoshop')) {
		// Define plugin directory for inclusions
		define('JIGOSHOP_BRAINTREE_GATEWAY_DIR', dirname(__FILE__) . '/Jigoshop1x');
		// Define plugin URL for assets
		define('JIGOSHOP_BRAINTREE_GATEWAY_URL', plugins_url('', __FILE__) . '/Jigoshop1x');
		//Check version.
		if (jigoshop_add_required_version_notice(JIGOSHOP_BRAINTREE_GATEWAY_NAME, '1.17')) {
			return;
		}
		//Init components.
		require_once(JIGOSHOP_BRAINTREE_GATEWAY_DIR . '/braintree.php');
	} else {
		add_action('admin_notices', function () {
			echo '<div class="error"><p>';
			printf(__('%s requires Jigoshop plugin to be active. Code for plugin %s was not loaded.', 'jigoshop_braintree_gateway'),
				JIGOSHOP_BRAINTREE_GATEWAY_NAME, JIGOSHOP_BRAINTREE_GATEWAY_NAME);
			echo '</p></div>';
		});
	}
});
