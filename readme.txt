=== PipraPay Gateway for WooCommerce ===
Contributors: piprapay
Donate link: https://piprapay.com
Tags: woocommerce, payment gateway, piprapay, digital products, HPOS
Requires at least: 5.2
Tested up to: 8.0
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A seamless and secure payment gateway integration for WooCommerce using PipraPay.

== Description ==

PipraPay Gateway is a secure, fast, and flexible WooCommerce payment gateway plugin that allows store owners to accept payments through PipraPay. It supports both physical and digital products, with configurable order status handling after payment.

=== Features ===
* Integration with the PipraPay API
* Customizable payment settings
* Configurable checkout logo
* Secure webhook handling
* Order payment verification
* Compatibility with WooCommerce High-Performance Order Storage (HPOS)
* Compatibility with WooCommerce block-based checkout
* Show / Hide icon option on checkout page (new)
* Direct Settings link from the Plugins page (new)
* Automatic order status handling:
  - Physical Product (Set to processing)
  - Digital Product (Set to processing)
  - Digital Product (Auto complete)

== Installation ==

1. Upload the `piprapay-gateway` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to WooCommerce → Settings → Payments and enable "PipraPay Gateway".
4. Configure your API key, logo settings, currency, and order type options.

== FAQ ==

= What is PipraPay? =
PipraPay is a secure payment gateway that supports multiple currencies and payment methods.

= How do I configure the plugin? =
Go to WooCommerce → Settings → Payments and click on "Manage" under PipraPay Gateway.

= Can I customize the checkout logo? =
Yes, administrators can set a custom logo URL or upload an image via the media library.

= Is my data secure? =
Yes, all payment data is processed securely through the PipraPay API.

= Is this plugin compatible with WooCommerce HPOS? =
Yes, this plugin is fully compatible with WooCommerce High-Performance Order Storage (HPOS).

= Does this plugin support WooCommerce block-based checkout? =
Yes, the plugin supports WooCommerce block-based checkout, ensuring a seamless experience in the modern checkout flow.

= Can I set different order status for digital products? =
Yes, you can choose between:
* Digital Product (Set to processing)
* Digital Product (Auto complete)

== External Services ==

This plugin connects to the PipraPay API to process payments securely. The following data is sent to PipraPay:

* Customer name, email, and phone number
* Order amount and currency
* API key for authentication

This data is only sent during the payment process and is necessary to complete the transaction.

For more information, please review:
* [PipraPay Terms of Service](https://piprapay.com/terms)
* [PipraPay Privacy Policy](https://piprapay.com/privacy)

== Changelog ==

= 1.0.4 =
* Added new Show / Hide icon option on checkout page
* Added new Direct Settings link from the Plugins page
* Added new order type option: Digital Product (Set to processing)
* Updated order status handling for digital products
* Minor code optimizations

= 1.0.3 =
* Initial release

== Upgrade Notice ==

Version 1.0.4 introduces enhanced digital product order handling. Ensure your digital products now have the correct status according to your selection.
