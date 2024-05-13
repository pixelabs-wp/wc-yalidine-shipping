<?php
// $url = "https://api.yalidine.app/v1/parcels/"; // the parcel's endpoint
//  $api_id = "08467949173865045243"; // your api ID
//  $api_token = "6tDv0VDFh5MKfvcyQtO3eouLUT8Sc7w5FngPzXRrOHPyq29zWY4Jlpr2dB1jaiRJ"; // your api token

function yws_yalidine_api_call($url, $api_id, $api_token)
{

    $curl = curl_init();

    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'X-API-ID: ' . $api_id,
                'X-API-TOKEN: ' . $api_token
            ),
        )
    );

    $response_json = curl_exec($curl);
    curl_close($curl);

    $response_array = json_decode($response_json, true); // converting the json to a php array

    return $response_array;
}



function create_parcel_on_order_status_change($order_id, $selected_stopdesk_id)
{
    // Check if the order status changed to 'processing' or any other status you desire

    // Get the order object
    $order = wc_get_order($order_id);
    $country = $order->get_billing_country();
    $state = $order->get_billing_state();
    $statename = WC()->countries->get_states($country)[$state];
    // error_log( 'state:' . $statename);
    // error_log( 'Order ID:' . $order_id );
    // error_log( 'Order Data:' .  $order );



    // Get the linked stopdesk ID from order meta
    $linked_stopdesk_id = get_yalidine_api_credentials_by_id($selected_stopdesk_id);


    // Access the values using array notation
    $api_id = $linked_stopdesk_id[0]['api_id'];
    $token_id = $linked_stopdesk_id[0]['token_id'];
    // error_log( 'api_id:' .  $api_id );
    // error_log( 'token_id:' .  $token_id );


    // Iterating through order shipping items
    foreach ($order->get_items('shipping') as $item_id => $item) {
        $order_item_name = $item->get_name();
        // $order_item_type = $item->get_type();
        // $shipping_method_title = $item->get_method_title();
        // $shipping_method_id = $item->get_method_id(); // The method ID
        // $shipping_method_instance_id = $item->get_instance_id(); // The instance ID
        // $shipping_method_total = $item->get_total();
        // $shipping_method_total_tax = $item->get_total_tax();
        // $shipping_method_taxes = $item->get_taxes();
    }



    $items = '';
    foreach ($order->get_items() as $item_key => $item) {
        $items .= $item->get_name() . ' / ';
    }

    $items = trim($items, ' / ');
    $order_data = $order->get_data();
    // error_log( 'order_data:' .  $order_data );
// Get the state name based on the state code
    $commune = isset($order_data['billing']['city']) ? $order_data['billing']['city'] : $order_data['shipping']['city'];

    $center_information = retrieve_yalidine_center();

    // Decode JSON data
    $center_information_array = json_decode($center_information, true);


    // Loop through each center data
    foreach ($center_information_array['data'] as $center) {
        // Check if the state name matches
        if ($center['wilaya_name'] === $statename && $center['commune_name'] == $commune) {
            // Store the center ID and break the loop (assuming you only want the first match)
            $matched_center_id = $center['center_id'];
            break;
        }
    }

    if ($order_item_name === 'livraison à domicile') {
        $parcel =
            array( // first parcel
                "order_id" => $order_id,
                "firstname" => isset($order_data['billing']['first_name']) ? $order_data['billing']['first_name'] : $order_data['shipping']['first_name'],
                "familyname" => isset($order_data['billing']['last_name']) ? $order_data['billing']['last_name'] : $order_data['shipping']['last_name'],
                "contact_phone" => isset($order_data['billing']['phone']) ? $order_data['billing']['phone'] : $order_data['shipping']['phone'],
                "address" => isset($order_data['billing']['address_1']) ? $order_data['billing']['address_1'] : $order_data['shipping']['address_1'],
                "to_commune_name" => $commune,
                "to_wilaya_name" => $statename,
                "product_list" => $items,
                "price" => $order_data['total'],
                "freeshipping" => True,
                "has_exchange" => 0,
                "is_stopdesk" => False
            );
    }

    else if($order_item_name === 'livraison desktop'){
        $parcel =
            array( // first parcel
                "order_id" => $order_id,
                "firstname" => isset($order_data['billing']['first_name']) ? $order_data['billing']['first_name'] : $order_data['shipping']['first_name'],
                "familyname" => isset($order_data['billing']['last_name']) ? $order_data['billing']['last_name'] : $order_data['shipping']['last_name'],
                "contact_phone" => isset($order_data['billing']['phone']) ? $order_data['billing']['phone'] : $order_data['shipping']['phone'],
                "address" => isset($order_data['billing']['address_1']) ? $order_data['billing']['address_1'] : $order_data['shipping']['address_1'],
                "to_commune_name" => $commune,
                "to_wilaya_name" => $statename,
                "product_list" => $items,
                "price" => $order_data['total'],
                "freeshipping" => True,
                "has_exchange" => 0,
                "is_stopdesk" => true,
                "stopdesk_id" => $matched_center_id
            );
    }


    // Convert parcel data to JSON format
    $post_data = json_encode(array($parcel));
    // error_log( 'post_data:' .  $post_data );


    $url = "https://api.yalidine.app/v1/parcels/";

    // Set up cURL request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            "X-API-ID: " . $api_id,
            "X-API-TOKEN: " . $token_id,
            "Content-Type: application/json",
        )
    );
    // Execute cURL request
    $result = curl_exec($ch);
    var_dump($result);

    // Assuming $result contains the JSON response
    $success_json_response = json_decode($result, true);

    // Check if the JSON decoding was successful and if the response contains a success message
    if ($success_json_response !== null && isset($success_json_response[$order_id]['success']) && $success_json_response[$order_id]['success']) {
        // Extract the tracking value
        $tracking = $success_json_response[$order_id]['tracking'];

        // Update order meta with the tracking value
        update_post_meta($order_id, 'tracking_number', $tracking);
        update_post_meta($order_id, 'linked_stopdesk', $selected_stopdesk_id);

        // Display a success admin notice
        add_action('admin_notices', function () use ($tracking) {
            echo '<div class="notice notice-success is-dismissible"><p>Tracking number saved: ' . esc_html($tracking) . '</p></div>';
        });
    } else {
        // Display an error admin notice
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error is-dismissible"><p>Error: Unable to save tracking number.</p></div>';
        });
    }


    error_log(json_encode($result));


    curl_close($ch);

    // Optionally handle the API response
    // For example, log the response or perform further actions based on it
    // echo $result;
}

// }






function retrieve_yalidine_center()
{

    $url = "https://api.yalidine.app/v1/centers/"; // the wilayas endpoint
    $linked_stopdesk_id = get_yalidine_api_credentials();

    // Access the values using array notation
    $api_id = $linked_stopdesk_id[0]['api_id'];
    $token_id = $linked_stopdesk_id[0]['token_id'];

    $curl = curl_init();

    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'X-API-ID: ' . $api_id,
                'X-API-TOKEN: ' . $token_id
            ),
        )
    );

    $response_json = curl_exec($curl);
    curl_close($curl);

    $response_array = json_decode($response_json, true); // converting the json to a php array

    return $response_json;
    /* now handle the response_array like you need

        ...

    */
}




function yalidine_delete_parcels($tracking)
{
    $linked_stopdesk_id = get_yalidine_api_credentials();

    // Access the values using array notation
    $api_id = $linked_stopdesk_id[0]['api_id'];
    $token_id = $linked_stopdesk_id[0]['token_id'];
    $api_url = 'https://api.yalidine.app/v1/parcels/' . $tracking;

    $curl = curl_init();

    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => $api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => array(
                'X-API-ID: ' . $api_id,
                'X-API-TOKEN: ' . $token_id
            ),
        )
    );

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;

}



add_action('wp_ajax_edit_yalidine_parcels', 'edit_yalidine_parcels');

function edit_yalidine_parcels()
{
    $linked_stopdesk_id = get_yalidine_api_credentials();

    // Access the values using array notation
    $api_id = $linked_stopdesk_id[0]['api_id'];
    $token_id = $linked_stopdesk_id[0]['token_id'];


    $formData = $_POST['formData'];

    // Extract individual form fields
    $tracking = $formData['tracking'];
    $firstName = $formData['firstName'];
    $lastName = $formData['lastName'];
    $phone = $formData['phone'];
    $address = $formData['address'];
    $toWilayaName = $formData['toWilayaName'];
    $to_commune_name = $formData['toCommuneName'];

    $data = array( // array of parameters to edit and their new values
        // Example : changing the firstname and the freeshipping
        "firstname" => $firstName,
        "familyname" => $lastName,
        "contact_phone" => $phone,
        "address" => $address,
        "to_wilaya_name" => $toWilayaName,
        "to_commune_name" => $to_commune_name
    );

    $postdata = json_encode($data);
    $api_url = 'https://api.yalidine.app/v1/parcels/' . $tracking;

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH'); // we use the patch method
    // curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            "X-API-ID: " . $api_id,
            "X-API-TOKEN: " . $token_id,
            "Content-Type: application/json"
        )
    );

    $result = curl_exec($ch);
    curl_close($ch);

    header("Content-Type: application/json");
    echo $result;
    wp_die();

}


// Getting the shipping fee and available stopd desks
function yws_get_provinance_shipping_fees()
{

    $linked_stopdesk_id = get_yalidine_api_credentials();


    // Access the values using array notation
    $api_id = $linked_stopdesk_id[0]['api_id'];
    $token_id = $linked_stopdesk_id[0]['token_id'];
    $url = "https://api.yalidine.app/v1/deliveryfees/"; // the deliveryfees endpoint
    $curl = curl_init();

    curl_setopt_array(
        $curl,
        array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'X-API-ID:' . $api_id,
                'X-API-TOKEN:' . $token_id
            ),
        )
    );

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;



    /* now handle the response_array like you need
    
        ...
    
    */

}