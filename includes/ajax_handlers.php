<?php

add_action('wp_ajax_save_yalidine_credentials', 'save_yalidine_credentials_callback');
add_action('wp_ajax_nopriv_save_yalidine_credentials', 'save_yalidine_credentials_callback'); // Allow non-logged-in

function save_yalidine_credentials_callback()
{
    // Check nonce (if needed).

    // Retrieve and sanitize form data.
    $stopdesk_name = sanitize_text_field($_POST['stopdesk_name']);
    $api_id = sanitize_text_field($_POST['api_id']);
    $token_id = sanitize_text_field($_POST['token_id']);
    $user_id = sanitize_text_field($_POST['user_id']);

    // Insert data into database.
    global $wpdb;
    $table_name = $wpdb->prefix . 'yalidine_credentials';

    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'stopdesk_name' => $stopdesk_name,
            'api_id' => $api_id,
            'token_id' => $token_id,
        )
    );

    // Return response.
    wp_send_json_success('Data saved successfully!');
}



add_action('wp_ajax_get_yalidine_credentials', 'get_yalidine_credentials_callback');

function get_yalidine_credentials_callback()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'yalidine_credentials';
    $current_user_id = get_current_user_id();
    $query = "SELECT user_id, stopdesk_name, api_id, token_id FROM $table_name WHERE user_id = $current_user_id";
    $results = $wpdb->get_results($query, ARRAY_A);

    wp_send_json($results);
}




add_action('wp_ajax_delete_yalidine_credentials', 'delete_yalidine_credentials_callback');
add_action('wp_ajax_nopriv_delete_yalidine_credentials', 'delete_yalidine_credentials_callback'); // Allow non-logged-in

function delete_yalidine_credentials_callback()
{
    $api_id_todelete = $_POST['user_id'];

    // Assuming $wpdb is already defined as WordPress database object
    global $wpdb;

    // Table name
    $table_name = $wpdb->prefix . 'yalidine_credentials';
    // SQL DELETE query
    $delete_query = $wpdb->prepare(
        "DELETE FROM $table_name WHERE api_id = %s",
        $api_id_todelete
    );

    // Execute the query
    $result = $wpdb->query($delete_query);

    // Check if deletion was successful
    if ($result === false) {
        // Handle error
        wp_send_json_error("Error deleting row: " . $wpdb->last_error);
    } else {
        // Check if any rows were affected
        if ($result > 0) {
            // Send success response
            wp_send_json_success("Row(s) deleted successfully.");
        } else {
            // Send response indicating no rows were found
            wp_send_json_error("No rows matching the API ID were found.");
        }
    }


}






// Handle AJAX request for editing credentials
add_action('wp_ajax_edit_yalidine_credentials', 'edit_yalidine_credentials_callback');

function edit_yalidine_credentials_callback()
{

    global $wpdb;

    // Table name
    $table_name = $wpdb->prefix . 'yalidine_credentials';

    // Retrieve and sanitize form data
    $stopdesk_name = sanitize_text_field($_POST['stopdesk_name']);
    $api_id = sanitize_text_field($_POST['api_id']);
    $token_id = sanitize_text_field($_POST['token_id']);

    $query = "SELECT id FROM $table_name WHERE api_id = $api_id";
    $api_id_to_update = $wpdb->get_results($query, ARRAY_A);
    $idValue = $api_id_to_update[0]['id'];

    // echo json_encode($idValue);
    // SQL UPDATE query
    $update_query = $wpdb->prepare(
        "UPDATE $table_name SET stopdesk_name = %s, api_id = %s, token_id = %s WHERE id = %s",
        $stopdesk_name,
        $api_id,
        $token_id,
        $idValue
    );

    // Execute the query
    $result = $wpdb->query($update_query);

     // Check if the update was successful
     if ($result == 1) {
        // Send a success response
        wp_send_json_success("Credentials updated successfully!");
    } else {
        // Send an error response
        wp_send_json_error("Error updating credentials: " . $wpdb->last_error);
    }

}



