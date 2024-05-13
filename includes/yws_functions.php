<?php

/**
 * Create custom table to store Yalidine API credentials.
 */
function yalidine_wc_create_custom_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'yalidine_credentials';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        stopdesk_name VARCHAR(255) NOT NULL,
        api_id VARCHAR(255) NOT NULL,
        token_id VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);


}


function get_yalidine_stopdesk_names()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'yalidine_credentials';
    $current_user_id = get_current_user_id();
    $query = "SELECT stopdesk_name, id FROM $table_name WHERE user_id = $current_user_id";
    $results = $wpdb->get_results($query, ARRAY_A);

    return $results;
}



function get_yalidine_api_credentials()
{
    $user_id = get_current_user_id();

    $stopdesk_name_id = get_user_meta($user_id, 'selected_stopdesk', true); // Replace 'stopdesk_name' with the meta key you want to retrieve

    global $wpdb;
    $table_name = $wpdb->prefix . 'yalidine_credentials';
    $query = "SELECT api_id, token_id FROM $table_name WHERE id = $stopdesk_name_id";
    $results = $wpdb->get_results($query, ARRAY_A);

    return $results;
}


function get_yalidine_api_credentials_by_id($api_id)
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'yalidine_credentials';
    $query = "SELECT api_id, token_id FROM $table_name WHERE id = $api_id";
    $results = $wpdb->get_results($query, ARRAY_A);

    return $results;
}




// Save selected stop desk in order meta
add_action('woocommerce_process_shop_order_meta', 'save_order_stopdesk');

function save_order_stopdesk($order_id)
{
    if (!empty($_POST['order_stopdesk_select'])) {
        $selected_stopdesk_id = sanitize_text_field($_POST['order_stopdesk_select']);
        create_parcel_on_order_status_change($order_id, $selected_stopdesk_id);

    }
}




// add_filter( 'manage_edit-shop_order_columns', 'my_custom_order_columns' );
// add_action( 'manage_shop_order_posts_custom_column', 'my_custom_order_column_content', 10, 2 );

// function my_custom_order_columns( $columns ) {
//   $columns['order_item_count'] = 'Items';
//   return $columns;
// }

// function my_custom_order_column_content( $column_name, $order_id ) {
//   if ( $column_name === 'order_item_count' ) {
//     $order = wc_get_order( $order_id );
//     $items = $order->get_items();
//     $item_count = count( $items );
//     echo $item_count;
//   }
// }





add_action('wp_ajax_delete_parcel', 'delete_parcel_callback');
function delete_parcel_callback()
{
    if (isset($_POST['tracking'])) {
        $tracking = $_POST['tracking'];
        $orderID = $_POST['orderID'];

        yalidine_delete_parcels($tracking);

        delete_post_meta($orderID, "linked_stopdesk");
        delete_post_meta($orderID, "tracking_number");

        wp_die();
    }
}




add_filter('manage_woocommerce_page_wc-orders_columns', 'add_wc_order_list_custom_column');
function add_wc_order_list_custom_column($columns)
{
    $reordered_columns = array();

    // Inserting columns to a specific location
    foreach ($columns as $key => $column) {
        $reordered_columns[$key] = $column;

        if ($key === 'order_status') {
            // Inserting after "Status" column
            $reordered_columns['my-column1'] = __('Tracking Code', 'theme_domain');
        }
    }
    return $reordered_columns;
}

add_action('manage_woocommerce_page_wc-orders_custom_column', 'display_wc_order_list_custom_column_content', 10, 2);
function display_wc_order_list_custom_column_content($column, $order)
{
    switch ($column) {
        case 'my-column1':
            // Get custom order metadata
            $value = $order->get_meta('tracking_number');
            // Decode the JSON data into a PHP associative array
            $orderData = json_decode($order, true);
            // Access the 'id' from the decoded data
            $id = $orderData['id'];
            $tracking_number = get_post_meta( $id, 'tracking_number', true );

            if (!empty($tracking_number)) {
                echo $tracking_number;
            }
            // For testing (to be removed) - Empty value case
            else {
                echo '<small>(<em>Not linked to yalidine</em>)</small>';
            }
            break;


    }
}



function get_state($state_ref) {
    $states['DZ'] = [
        'DZ-01' => 'Adrar',
        'DZ-02' => 'Chlef',
        'DZ-03' => 'Laghouat',
        'DZ-04' => 'Oum El Bouaghi',
        'DZ-05' => 'Batna',
        'DZ-06' => 'Béjaïa',
        'DZ-07' => 'Biskra',
        'DZ-08' => 'Béchar',
        'DZ-09' => 'Blida',
        'DZ-10' => 'Bouira',
        'DZ-11' => 'Tamanrasset',
        'DZ-12' => 'Tébessa',
        'DZ-13' => 'Tlemcen',
        'DZ-14' => 'Tiaret',
        'DZ-15' => 'Tizi Ouzou',
        'DZ-16' => 'Alger',
        'DZ-17' => 'Djelfa',
        'DZ-18' => 'Jijel',
        'DZ-19' => 'Sétif',
        'DZ-20' => 'Saïda',
        'DZ-21' => 'Skikda',
        'DZ-22' => 'Sidi Bel Abbès',
        'DZ-23' => 'Annaba',
        'DZ-24' => 'Guelma',
        'DZ-25' => 'Constantine',
        'DZ-26' => 'Médéa',
        'DZ-27' => 'Mostaganem',
        'DZ-28' => "M'Sila",
        'DZ-29' => 'Mascara',
        'DZ-30' => 'Ouargla',
        'DZ-31' => 'Oran',
        'DZ-32' => 'El Bayadh',
        'DZ-33' => 'Illizi',
        'DZ-34' => 'Bordj Bou Arreridj',
        'DZ-35' => 'Boumerdès',
        'DZ-36' => 'El Tarf',
        'DZ-37' => 'Tindouf',
        'DZ-38' => 'Tissemsilt',
        'DZ-39' => 'El Oued',
        'DZ-40' => 'Khenchela',
        'DZ-41' => 'Souk Ahras',
        'DZ-42' => 'Tipaza',
        'DZ-43' => 'Mila',
        'DZ-44' => 'Aïn Defla',
        'DZ-45' => 'Naâma',
        'DZ-46' => 'Aïn Témouchent',
        'DZ-47' => 'Ghardaïa',
        'DZ-48' => 'Relizane',
    ];

return (isset($states['DZ'][$state_ref]) ? $states['DZ'][$state_ref] : null);
}



//  getWilayaDeliveryFees($wilaya_id) {
//     $this->last_api_url = $this->mainEndPoint . $this->endPoints['FEES'] . '/' . $wilaya_id;
//     $this->httpRequest->setUrl($this->last_api_url);
//     $response = $this->httpRequest->get();
//     $response = json_decode($response, true);
//     return $response;
// }
?>