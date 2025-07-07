<?php
/*
 * Plugin Name: PipraPay Gateway
 * Plugin URI: https://piprapay.com
 * Description: A seamless and secure payment gateway integration for WooCommerce using PipraPay.
 * Author: PipraPay
 * Version: 1.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.4
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: piprapay
 * Contributors: piprapay
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Declare compatibility with HPOS and Blocks
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

// Initialize the gateway class
add_action('plugins_loaded', 'piprapay_init_gateway_class', 0);

function piprapay_init_gateway_class()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return; // Exit if WooCommerce isn’t active
    }

    class WC_PIPRAPAY_Gateway extends WC_Payment_Gateway
    {
        private static $instance = null;

        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            $this->id = 'piprapay';
            $this->has_fields = false;
            $this->method_title = __('PipraPay', 'piprapay-gateway');
            $this->method_description = __('Secure and fast payments via PipraPay.', 'piprapay-gateway');
            $this->supports = ['products'];

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title') ?: __('PipraPay', 'piprapay-gateway');
            $this->description = $this->get_option('description') ?: __('Pay securely via PipraPay.', 'piprapay-gateway');
            $this->enabled = $this->get_option('enabled');
            
            $this->icon = $this->get_option('logo_url');
            if (empty($this->icon)) {
                $this->icon = plugins_url('assets/icon.png', __FILE__);
            }
            
            $this->apikey = sanitize_text_field($this->get_option('apikey'));
            $this->baseUrl = sanitize_text_field($this->get_option('baseUrl'));
            $this->order_type = sanitize_text_field($this->get_option('order_type'));
            $this->currency = sanitize_text_field($this->get_option('currency'));
            
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('woocommerce_api_' . strtolower($this->id), [$this, 'handle_webhook']);
        }

        public function init_form_fields()
        {
            $this->form_fields = [
                'enabled' => [
                    'title' => __('Enable/Disable', 'piprapay-gateway'),
                    'type' => 'checkbox',
                    'label' => __('Enable PipraPay Gateway', 'piprapay-gateway'),
                    'default' => 'no',
                ],
                'title' => [
                    'title' => __('Title', 'piprapay-gateway'),
                    'type' => 'text',
                    'description' => __('The title displayed at checkout.', 'piprapay-gateway'),
                    'default' => __('PipraPay', 'piprapay-gateway'),
                    'desc_tip' => true,
                ],
                'description' => [
                    'title' => __('Description', 'piprapay-gateway'),
                    'type' => 'textarea',
                    'description' => __('The description displayed at checkout.', 'piprapay-gateway'),
                    'default' => __('Pay securely via PipraPay.', 'piprapay-gateway'),
                    'desc_tip' => true,
                ],
                'logo_url' => [
                    'title' => __('Logo URL', 'piprapay-gateway'),
                    'type' => 'text',
                    'description' => __('Enter the URL of the logo to display at checkout.', 'piprapay-gateway'),
                    'default' => '',
                    'desc_tip' => true,
                ],
                'apikey' => [
                    'title' => __('API Key', 'piprapay-gateway'),
                    'type' => 'password',
                    'description' => __('Your PipraPay API key.', 'piprapay-gateway'),
                    'default' => '',
                    'desc_tip' => true,
                ],
                'baseUrl' => [
                    'title' => __('Base URL', 'piprapay-gateway'),
                    'type' => 'text',
                    'description' => __('Your PipraPay Base URL.', 'piprapay-gateway'),
                    'default' => '',
                    'desc_tip' => true,
                ],
                'order_type' => [
                    'title'       => __('Order Type', 'piprapay-gateway'),
                    'type'        => 'select',
                    'description' => __('Choose how the order should be handled after payment.', 'piprapay-gateway'),
                    'desc_tip'    => true,
                    'default'     => 'physical',
                    'options'     => [
                        'physical' => __('Physical Product (Set to processing)', 'piprapay-gateway'),
                        'digital'  => __('Digital Product (Auto complete)', 'piprapay-gateway'),
                    ],
                ],
                'currency' => [
                    'title' => __('Default Currency', 'piprapay-gateway'),
                    'type' => 'text',
                    'description' => __('The currency you want to show on gateway.', 'piprapay-gateway'),
                    'default' => 'USD',
                    'desc_tip' => true,
                ],
            ];
        }

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);
            if (!$order) {
                wc_add_notice(__('Order not found.', 'piprapay-gateway'), 'error');
                return ['result' => 'fail'];
            }
        
            $data = [
                'full_name'    => sanitize_text_field(trim(($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: 'Jhon')),
                'email_mobile' => sanitize_email($order->get_billing_email() ?: 'jhon@gmail.com'),
                'amount'       => (string) $order->get_total(),
                'metadata'     => ['invoiceid' => (string) $order->get_id()],
                'redirect_url' => $this->get_return_url($order),
                'cancel_url'   => wc_get_checkout_url(),
                'webhook_url'  => WC()->api_request_url(strtolower($this->id)),
                'return_type'  => 'POST',
                'currency'     => $this->currency,
            ];
        
            $args = [
                'body'    => json_encode($data),
                'headers' => [
                    'Content-Type'           => 'application/json',
                    'Accept'                 => 'application/json',
                    'mh-piprapay-api-key'    => $this->apikey,
                ],
                'timeout' => 45,
            ];
        
            $response = wp_remote_post($this->baseUrl . '/create-charge', $args);
        
            if (is_wp_error($response)) {
                wc_add_notice(__('Payment error: ', 'piprapay-gateway') . $response->get_error_message(), 'error');
                return ['result' => 'fail'];
            }
        
            $result = json_decode(wp_remote_retrieve_body($response), true);
        
            if (isset($result['pp_url'])) {
                return [
                    'result'   => 'success',
                    'redirect' => esc_url($result['pp_url']),
                ];
            }
        
            $message = !empty($result['message']) ? esc_html($result['message']) : __('Unknown error.', 'piprapay-gateway');
            
            wc_add_notice(
                sprintf(
                    __('Payment error: Unable to create payment link. %s', 'piprapay-gateway'),
                    $message
                ),
                'error'
            );
            return ['result' => 'fail'];
        }
        
        public function handle_webhook()
        {
            $raw = file_get_contents("php://input");

            $payload = json_decode($raw, true);

            $headers = getallheaders();

            $received_api_key = '';
            
            if (isset($headers['mh-piprapay-api-key'])) {
                $received_api_key = $headers['mh-piprapay-api-key'];
            } elseif (isset($headers['Mh-Piprapay-Api-Key'])) {
                $received_api_key = $headers['Mh-Piprapay-Api-Key'];
            } elseif (isset($_SERVER['HTTP_MH_PIPRAPAY_API_KEY'])) {
                $received_api_key = $_SERVER['HTTP_MH_PIPRAPAY_API_KEY']; // fallback if needed
            }
            
            if ($received_api_key !== $this->apikey) {
                status_header(401);
                wp_send_json_error(['message' => 'Unauthorized request.']);
                exit;
            }
        
            if (empty($payload['metadata']['invoiceid']) || empty($payload['pp_id'])) {
                status_header(400);
                wp_send_json_error(['message' => 'Missing required data.']);
                exit;
            }
        
            $order_id = isset($payload['metadata']['invoiceid']) ? absint($payload['metadata']['invoiceid']) : 0;

            $pp_id = isset($payload['pp_id']) ? sanitize_text_field($payload['pp_id']) : '';

            $order = wc_get_order($order_id);
            
            if (!$order) {
                status_header(404);
                wp_send_json_error(['message' => 'Order not found.']);
                exit;
            }
        
            $verification = $this->verify_payment($payload['pp_id']);

            if ($verification['status'] === 'completed') {
                $order->payment_complete();
                $order->add_order_note(__('Payment verified via PipraPay.', 'piprapay-gateway'));
            } else {
                $order->update_status('failed', __('Payment verification failed via PipraPay.', 'piprapay-gateway'));
                wp_send_json_error(['message' => 'Verification failed.']);
                exit;
            }
        
            status_header(200);
            wp_send_json_success(['message' => 'Success']);
            exit;
        }


        private function verify_payment($pp_id)
        {
            $data = json_encode(['pp_id' => $pp_id]);
        
            $args = [
                'body'    => $data,
                'headers' => [
                    'Content-Type'           => 'application/json',
                    'Accept'                 => 'application/json',
                    'mh-piprapay-api-key'    => $this->apikey,
                ],
                'timeout' => 45,
            ];
        
            $url = $this->baseUrl . '/verify-payments';
            $response = wp_remote_post($url, $args);
        
            if (is_wp_error($response)) {
                return ['status' => 'error', 'message' => $response->get_error_message()];
            }
        
            $result = json_decode(wp_remote_retrieve_body($response), true);
        
            // Adjust this condition based on actual API success flag if different
            return $result;
        }
    }

    // Define server-side integration for blocks
    class WC_PIPRAPAY_Blocks extends \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType
    {
        protected $name = 'piprapay';

        public function initialize()
        {
            $this->settings = get_option('woocommerce_piprapay_settings', []);
        }

        public function is_active()
        {
            return !empty($this->settings['enabled']) && 'yes' === $this->settings['enabled'];
        }

        public function get_payment_method_script_handles()
        {
            wp_register_script(
                'piprapay-blocks',
                plugins_url('assets/js/piprapay-blocks.js', __FILE__),
                ['wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-i18n'],
                filemtime(plugin_dir_path(__FILE__) . 'assets/js/piprapay-blocks.js'),
                true
            );
            return ['piprapay-blocks'];
        }

        public function get_payment_method_data()
        {
            $gateway = WC_PIPRAPAY_Gateway::get_instance();
            return [
                'title' => $gateway->title,
                'description' => $gateway->description,
                'icon' => $gateway->icon,
                'supports' => ['products'],
            ];
        }
    }

    // Register payment method for WooCommerce Blocks
    add_action('woocommerce_blocks_payment_method_type_registration', function ($registry) {
        $registry->register(new WC_PIPRAPAY_Blocks());
    });

    // Add filter to include payment gateway
    add_filter('woocommerce_payment_gateways', function ($gateways) {
        $gateways[] = 'WC_PIPRAPAY_Gateway';
        return $gateways;
    });
}
