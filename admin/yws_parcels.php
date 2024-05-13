<?php
/**
 * Callback function for Parcels submenu page.
 */
function yalidine_wc_parcels_page()
{
    // Add code for Parcels submenu page.
    ?>

    <h1>Parcels</h1>
    <?php
    $stopdesk_name = get_yalidine_stopdesk_names();

    $user_id = get_current_user_id();

    if (isset($_POST['stopdesk_name'])) {
        $selected_stopdesk_id = sanitize_key($_POST['stopdesk_name']);
        // Get the current user ID

        // Update user meta with the selected StopDesk ID
        update_user_meta($user_id, 'selected_stopdesk', $selected_stopdesk_id);
    }


    $stopdesk_name_id = get_user_meta($user_id, 'selected_stopdesk', true); // Replace 'stopdesk_name' with the meta key you want to retrieve

    ?>

    <div class="yws-parcels-page-body">
        <form action="" method="POST" id="stopdesk_selection">
            <h5>Select StopDesk: </h5>

            <select name="stopdesk_name" id="stopdesk_name_select">

                <option>Select Stopdesk</option>


                <?php foreach ($stopdesk_name as $name):
                    ?>

                    <?php
                    if ($stopdesk_name_id == $name['id']) { ?>
                        <option value="<?php echo esc_attr($name['id']); ?>" selected>
                            <?php echo esc_html($name['stopdesk_name']); ?>
                        </option>

                    <?php } else {
                        ?>
                        <option value="<?php echo esc_attr($name['id']); ?>">
                            <?php echo esc_html($name['stopdesk_name']); ?>
                        </option>
                        <?php
                    }

                endforeach; ?>
            </select>
            <input type="submit">
        </form>


        <div class="container_parcel_details">
            <?php

            $api_credentials = get_yalidine_api_credentials();

            if ($api_credentials) {
                ?>
                <table id="parcel-details-table" class="display">
                    <thead>
                        <tr>
                            <th>Tracking</th>
                            <th>Name</th>
                            <th>Contact Phone</th>
                            <th>Address</th>
                            <th>From Wilaya</th>
                            <th>To Wilaya</th>
                            <th>To Commune</th>
                            <th>Product List</th>
                            <th>Price</th>
                            <th>Last Status</th>
                            <th>Date Last Status</th>
                            <th>Current Center</th>
                            <th>Payment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php


                        $url = "https://api.yalidine.app/v1/parcels/"; // the parcel's endpoint
                        $api_id = $api_credentials[0]['api_id'];
                        $api_token = $api_credentials[0]['token_id'];


                        $parcel_data = yws_yalidine_api_call($url, $api_id, $api_token);

                        foreach ($parcel_data['data'] as $shipment):
                            $name = $shipment['firstname'] . ' ' . $shipment['familyname'];
                            ?>
                            <tr>
                                <td><?php echo esc_html($shipment['tracking']); ?></td>
                                <td><?php echo esc_html($name); ?></td>
                                <td><?php echo esc_html($shipment['contact_phone']); ?></td>
                                <td><?php echo esc_html($shipment['address']); ?></td>
                                <td><?php echo esc_html($shipment['from_wilaya_name']); ?></td>
                                <td><?php echo esc_html($shipment['to_wilaya_name']); ?></td>
                                <td><?php echo esc_html($shipment['to_commune_name']); ?></td>

                                <td><?php echo esc_html($shipment['product_list']); ?></td>
                                <td><?php echo esc_html($shipment['price']); ?></td>
                                <td><?php echo esc_html($shipment['last_status']); ?></td>
                                <td><?php echo esc_html($shipment['date_last_status']); ?></td>
                                <td><?php echo esc_html($shipment['current_center_name']); ?></td>
                                <td><?php echo esc_html($shipment['payment_status']); ?></td>
                                <td><a href="<?php echo esc_url($shipment['label']); ?>" target="_blank"> <span
                                            class="dashicons dashicons-printer"></span></a> <a><span
                                            class="view_parcel dashicons dashicons-visibility"></span></a>

                                    <?php
                                    $last_status_without_spaces = str_replace(' ', '', $shipment['last_status']);
                                    $last_status_space_escaped = esc_html($last_status_without_spaces);
                                    // echo $last_status_space_escaped;
                                    if ($last_status_space_escaped == 'Enpréparation') {
                                        ?>
                                        <a id="edit_parcel"><span class="edit_parcel dashicons dashicons-edit"></span></a>
                                        <a id="delete_parcel" style="color: red;"
                                            data-id="<?php echo esc_html($shipment['order_id']); ?>"
                                            data-tracking="<?php echo esc_attr($shipment['tracking']); ?>">
                                            <span class="delete_parcel dashicons dashicons-trash"></span>
                                        </a>
                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach;
                        ?>


                    </tbody>
                </table>
                <!-- Bootstrap View Modal -->
                <div class="modal fade" id="shipment-details-modal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Shipment Details</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Shipment details will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bootstrap edit Modal -->
                <div class="modal fade" id="shipment-edit-details-modal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Edit Shipment Details</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Edit form -->
                                <form id="edit-shipment-form " style="  display: flex;
                                                                        flex-direction: row;
                                                                        flex-wrap: wrap;">
                                    <!-- Add input fields for editing shipment details -->
                                    <div class="form-group ">
                                        <label for="edit-tracking">Tracking</label>
                                        <input type="text" class="form-control" id="edit-tracking" readonly>
                                    </div>

                                    <!-- Add more input fields for other shipment details -->
                                    <!-- For example: -->
                                    <div class="form-group">
                                        <label for="edit-first-name">First Name</label>
                                        <input type="text" class="form-control" id="edit-first-name">
                                    </div>


                                    <div class="form-group">
                                        <label for="edit-last-name">Last Name</label>
                                        <input type="text" class="form-control" id="edit-last-name">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-phone">Phone</label>
                                        <input type="number" class="form-control" id="edit-phone">
                                    </div>

                                    <div class="form-group">
                                        <label for="edit-address">Address</label>
                                        <input type="text" class="form-control" id="edit-address">
                                    </div>

                                    <div class="form-group">
                                        <label for="edit-to-walaya-name">Walaya Name</label>
                                        <input type="text" class="form-control" id="edit-to-walaya-name">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-to-commune-name">Commune Name</label>
                                        <input type="text" class="form-control" id="edit-to-commune-name">
                                    </div>

                                    <!-- Repeat for other fields as needed -->
                                    <!-- Add a submit button -->
                                    <div class="form-group w-100"> <button id="edit_shipping_details_submit" type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>



                <?php
            } else { ?>
                <h1 style="color: red;">Select Stopdesk to see data</h1>
                <?php
            }
            ?>


        </div>

    </div>

    

    <?php

    // $delivery_address_data = yws_get_provinance_shipping_fees();
    // json_decode($delivery_address_data);
    // echo $delivery_address_data;

//     $center = retrieve_yalidine_center();
// echo $center;


// $order_id = 24; // For example

// // An instance of 
// $order = wc_get_order($order_id);

// // Iterating through order shipping items
// foreach( $order->get_items( 'shipping' ) as $item_id => $item ){
//     $order_item_name             = $item->get_name();
//     $order_item_type             = $item->get_type();
//     $shipping_method_title       = $item->get_method_title();
//     $shipping_method_id          = $item->get_method_id(); // The method ID
//     $shipping_method_instance_id = $item->get_instance_id(); // The instance ID
//     $shipping_method_total       = $item->get_total();
//     $shipping_method_total_tax   = $item->get_total_tax();
//     $shipping_method_taxes       = $item->get_taxes();
// }

// echo $order_item_name;
}
