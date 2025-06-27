# PipraPay Gateway for WooCommerce

![License](https://img.shields.io/badge/license-GPLv2%20or%20later-blue.svg)
![WooCommerce](https://img.shields.io/badge/WooCommerce-3.0%2B-orange.svg)
![WordPress](https://img.shields.io/badge/WordPress-6.7%2B-blue.svg)
![HPOS Compatible](https://img.shields.io/badge/HPOS-Compatible-success.svg)

A seamless and secure payment gateway integration for WooCommerce using PipraPay.

## Features

- Integration with the PipraPay API
- Customizable payment settings
- Configurable checkout logo
- Secure webhook handling
- Order payment verification
- Compatibility with WooCommerce High-Performance Order Storage (HPOS)
- Compatibility with WooCommerce block-based checkout

## Installation

1. Upload the `piprapay-gateway` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **WooCommerce → Settings → Payments** and enable "PipraPay Gateway"
4. Configure your API key, logo settings, and other options

## FAQ

### What is PipraPay?
PipraPay is a secure payment gateway that supports multiple currencies and payment methods.

### How do I configure the plugin?
Go to **WooCommerce → Settings → Payments** and click on "Manage" under PipraPay Gateway.

### Can I customize the checkout logo?
Yes, administrators can set a custom logo URL or upload an image via the media library.

### Is my data secure?
Yes, all payment data is processed securely through the PipraPay API.

### Is this plugin compatible with WooCommerce HPOS?
Yes, this plugin is fully compatible with WooCommerce High-Performance Order Storage (HPOS).

### Does this plugin support WooCommerce block-based checkout?
Yes, the plugin supports WooCommerce block-based checkout, ensuring a seamless experience in the modern checkout flow.

## External Services

This plugin connects to the PipraPay API to process payments securely. The following data is sent to PipraPay:

- Customer name, email, and phone number
- Order amount and currency
- API key for authentication

This data is sent only during the payment process and is necessary to complete the transaction.

For more information, please review:
- [PipraPay Terms of Service](https://piprapay.com/terms)
- [PipraPay Privacy Policy](https://piprapay.com/privacy)

## Changelog

### 1.0.0
- Initial release
