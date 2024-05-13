<?php
add_action( 'add_meta_boxes', 'register_custom_order_postbox' );

function register_custom_order_postbox() {
  add_meta_box(
    'custom_order_postbox', // Meta box ID
    __('Custom Order Information', 'yalidine-woocommerce-integration'), // Title of the meta box
    'display_custom_order_postbox', // Callback function to display content
    'woocommerce_page_wc-orders', // Screen to display on (WooCommerce order edit page)
    'side', // Context
    'default' // Priority
  );
}

function display_custom_order_postbox($order) {
  global $wpdb;

  // Get current user ID
  $current_user_id = get_current_user_id();

  // Retrieve stopdesk names added by the current user
  $table_name = $wpdb->prefix . 'yalidine_credentials';
  $stopdesk_query = "SELECT stopdesk_name, id FROM $table_name WHERE user_id = %d";
  $prepared_sql = $wpdb->prepare( $stopdesk_query, $current_user_id );
  $results = $wpdb->get_results( $prepared_sql, ARRAY_A );
 
  // Display widget heading
  ?>
  <div class="stopdesk-widget">
      <h3><?php echo __('Link to Stopdesk', 'yalidine-woocommerce-integration'); ?></h3>
  
      <!-- Display select field with stopdesk names as options -->
      <select id="stopdesk-select" name="order_stopdesk_select">
          <option value="" ><?php echo __('Select Stopdesk', 'yalidine-woocommerce-integration'); ?></option>
          <?php foreach ($results as $stopdesk_name) : ?>
              <?php
              $selected = '';
              // Get the post meta for linked_stopdesk
              $linked_stopdesk = get_post_meta($order->get_id(), 'linked_stopdesk', true);
              // Check if the current stopdesk ID matches the linked_stopdesk meta
              if ($linked_stopdesk == $stopdesk_name['id']) {
                  $selected = 'selected';
              }
              ?>
              <option value="<?php echo esc_attr($stopdesk_name['id']); ?>" <?php echo $selected; ?>><?php echo esc_html($stopdesk_name['stopdesk_name']); ?></option>
          <?php endforeach; ?>
      </select>
  </div>
  <?php
  
}


