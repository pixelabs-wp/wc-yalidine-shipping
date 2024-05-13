<?php
/**
 * Plugin Name: Yalidine Shipping Integration for WooCommerce
 * Description: Integrate Yalidine Shipping with WooCommerce for seamless shipping options.
 * Version: 1.0.0
 * Author: Ali Haider
 * Author URI: Your Website
 * Text Domain: yalidine-woocommerce-integration
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define plugin constants.
define('YALIDINE_WC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('YALIDINE_WC_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Include the main class file.
require_once YALIDINE_WC_PLUGIN_PATH . 'includes/yws_functions.php';
require_once (YALIDINE_WC_PLUGIN_PATH . 'includes/yws_register_menus.php');
// Include files for menu registration and callback functions.
require_once (YALIDINE_WC_PLUGIN_PATH . 'admin/yws_parcels.php');
require_once (YALIDINE_WC_PLUGIN_PATH . 'admin/yws_stopdesk.php');
require_once (YALIDINE_WC_PLUGIN_PATH . 'includes/ajax_handlers.php');
require_once (YALIDINE_WC_PLUGIN_PATH . 'includes/api_call.php');
require_once (YALIDINE_WC_PLUGIN_PATH . 'includes/edit_order_widget.php');
require_once (YALIDINE_WC_PLUGIN_PATH . 'includes/yws_wc_shipping_calculation.php');

// add_action('wp_loaded', 'yalidine_wc_plugin_loaded');

// function yalidine_wc_plugin_loaded()
// {
//    // Define a constant to store the current user's ID
//     define('YALIDINE_CURRENT_USER_ID', get_current_user_id()); 
// }

add_action( 'admin_enqueue_scripts', 'yalidine_wc_enqueue_styles_and_scripts');

function yalidine_wc_enqueue_styles_and_scripts()
{
    // Enqueue style.css
    wp_enqueue_style('yalidine-wc-style', YALIDINE_WC_PLUGIN_URL . 'assets/style.css');

    // Enqueue script.js (assuming it's a JavaScript file)
    wp_enqueue_script('yalidine-wc-script', YALIDINE_WC_PLUGIN_URL . 'assets/script.js', array('jquery'), null, true);
    
    // **Enqueue jQuery from a CDN (optional):**
    // Comment out the following line if you're including jQuery from another source
    wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', null, null, false); // Load jQuery in the footer

    wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js', array('jquery'), '1.10.24', true);
    wp_enqueue_style('datatables-style', 'https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css');
    
    // **Localize script for AJAX URL:**
    wp_localize_script('yalidine-wc-script', 'yalidineAjax', array(
        'ajaxUrl' => admin_url('admin-ajax.php'), // URL for your AJAX endpoint
        'nonce' => wp_create_nonce('yalidine_save_credentials') // Nonce for security
    ));

    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', array(), '4.5.2');

    wp_enqueue_script('popper-js', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js', array('jquery'), '2.9.2', true);
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), '4.5.2', true);

      // Check if the current screen is related to your plugin
      $screen = get_current_screen();
      if (strpos($screen->base, 'yalidine-parcels') !== false) {
          // Enqueue Bootstrap CSS
          wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', array(), '4.5.2');
  
          // Enqueue Popper JS (required by Bootstrap)
          wp_enqueue_script('popper-js', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js', array('jquery'), '2.9.2', true);
  
          // Enqueue Bootstrap JS
          wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), '4.5.2', true);
      }
      
}


// Activation hook.
register_activation_hook(__FILE__, 'yalidine_wc_plugin_activation');

// Plugin activation function.
function yalidine_wc_plugin_activation()
{
    // Create custom table when plugin is activated.
    yalidine_wc_create_custom_table();
}



// $linked_stopdesk_id = get_yalidine_api_credentials_by_id(8);

// echo json_encode($linked_stopdesk_id);


