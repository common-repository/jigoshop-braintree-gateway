<?php
/**
 * Jigoshop Braintree Payments Gateway
 * By Stranger Studios (jason@strangerstudios.com)
 * 
 * Uninstall - removes all Stripe options from DB when user deletes the plugin via WordPress backend.
 **/
 
if ( !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}
	global $wpdb;
	$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'jigoshop_braintree_%'");		
?>