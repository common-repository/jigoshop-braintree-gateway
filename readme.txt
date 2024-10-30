=== Jigoshop Braintree Gateway ===
Contributors: jigoshop
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7F5FZ5NGJ3XTL
Tags: ecommerce, wordpress ecommerce, gateway, shop, shopping, cart, checkout, paypal, reports, tax, paypal, jigowatt, online, sell, sales, configurable, variable, downloadable, external, affiliate, download, virtual, physical, payment, pro, payments, braintree
Requires at least: 4.0
Tested up to: 4.8.2
Requires PHP: 5.6
Stable tag: 3.1.2

Braintree Payments Gateway Integration for Jigoshop.

== Description ==

Braintree SDK is a new client-side SDK that enables you to accept several payment types on web or mobile. With Braintree Direct, merchants get seamless access to new payment methods with the flip of a switch, and customers have the option to pay how they want to pay. Braintree Direct simplifies all payment method details down to a token so that you shouldn’t need to worry about which method users choose to pay you.
Braintree Direct is available for merchants in the United States, Canada, Australia, Europe, Singapore, Hong Kong, Malaysia and New Zealand. In legal terms, you have to be domiciled in a supported country.
To be domiciled in one of the supported countries, your business must operate out of a US, Canadian, Australian, Europe, Singapore, Hong Kong, Malaysia, New Zealand-based office. You must also have a bank account with a US, European, Australian, Canadian, Singapore, Hong Kong, Malaysia or New Zealand-chartered bank. The location of your customers has no effect on where you are domiciled.
Merchants in the US can use Braintree to accept PayPal, and most credit and debit cards, including Visa, Mastercard, American Express, Discover, JCB, and Diner’s Club.
Braintree supports more than 130 local currencies in 44 countries.
= Fees and pricing =
Cards and digital wallets: 2.9% + $.30 per transaction.
* An additional 1% fee applies to transactions presented outside of your home currency
* A flat $15 fee is assessed for chargebacks
* Fees are returned for fully-refunded transactions
* Discounted rate of 2.2% + $.30 for eligible nonprofits (3.25% + $.30 for Amex transactions)
* A $0.15 fee per AMEX transaction submitted by Braintree to American Express. Merchant may be subject to additional fees assessed by American Express.
PayPal
* Your rates are predetermined by PayPal, but generally 2.9% + $.30 per transaction; you can contact PayPal directly or visit their pricing page for more details.
* 3.9% transaction fee + fixed fee based on currency for international sales (no foreign exchange fees and no cross-border fees on European card transactions)
* Discounted rate for eligible nonprofits

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin folder to the '/wp-content/plugins/' directory
1. Activate plugin through the 'Plugins' menu in WordPress
1. Go to 'Jigoshop/Manage Licenses' to enter license key for your plugin
1. Navigate to Jigoshop > Settings > Payment to configure the Braintree gateway settings.
= Usage =
After enabling the plugin you have to configure it in Jigoshop => Settings => Payment Gateways.
Configuration
1.Enter Braintree merchant id (supplied to you by Braintree Payments)
1.Enter Braintree public key (supplied to you by Braintree Payments)
1.Enter Braintree private key (supplied to you by Braintree Payments)
1.You are good to go!
Optional configuration options
* Method title - gateway name visible in checkout.
* Description - description shown to user in checkout (above credit card fields). 


== Changelog ==

= 3.1.2 =
    * Added paypal bn code
= 3.1.1 =
    * Updated Links
= 3.0.3 =
    * Redeveloped for JS2
= 3.0.2 =
    * Minor fix
= 3.0.1 =
    * Fix action hook
= 3.0 =
    * Plugin Redeveloped to Jigoshop 2.0 compatible
= 2.1.4 =
    * Changed API Version
= 2.1.3 =
    * Small issue fix
= 2.1.2  =
    * Added: actions links
= 2.1.1 =
    * Fix payment bug
= 2.1 =
    * Made compatibility to display Admin options with Jigoshop 1.9.x
= 2.0 =
    * First Release 
