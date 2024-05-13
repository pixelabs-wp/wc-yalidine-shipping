<?php


/**
 * Callback function for Stopdesk submenu page.
 */
function yalidine_wc_stopdesk_page()
{
    ?>

    <div class="yalidine-stopdesk-page-body">
        <!-- HTML markup for Parcels submenu page -->
        <h2>Stopdesk</h2>

        <form id="yalidine-form">
            <label for="stopdesk_name">Stopdesk Name:</label>

            <input type="text" name="stopdesk_name" id="stopdesk_name" required><br>

            <label for="api_id">API ID:</label>
            <input type="text" name="api_id" id="api_id" required><br>

            <label for="token_id">Token ID:</label>
            <input type="text" name="token_id" id="token_id" required><br>

            <input type="hidden" name="user_id" id="user_id" value="<?php echo get_current_user_id(); ?>">

            <input type="submit" name="submit" value="Submit">
        </form>

        <!-- JavaScript for AJAX submission -->


        <div class="container_api_credentials">
            <table id="credentials-table" class="display">
                <thead>
                    <tr>
                        <th>Stopdesk Name</th>
                        <th>API ID</th>
                        <th>Token ID</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

    </div>


    <div id="edit-popup" style="display: none;">
        <form id="edit-form">
            <input type="hidden" name="user_id" id="edit-user-id">
            <label for="edit-stopdesk-name">Stopdesk Name:</label>
            <input type="text" name="stopdesk_name" id="edit-stopdesk-name">
            <label for="edit-api-id">API ID:</label>
            <input type="text" name="api_id" id="edit-api-id">
            <label for="edit-token-id">Token ID:</label>
            <input type="text" name="token_id" id="edit-token-id">
            <input type="submit" value="Update">
        </form>
    </div>

    <?php

    // $url = "https://api.yalidine.app/v1/parcels/"; // the parcel's endpoint
//  $api_id = "27399863520687645105"; // your api ID
//  $api_token = "JZbamv0IKP1hTkt4ysoMWBndwVdU2X1YEr9T5kLhKxA9pzCxNUSfLyGuDzRX46Bi"; // your api token


    // $curl = curl_init();

    // curl_setopt_array($curl, array(
//     CURLOPT_URL => $url,
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_ENCODING => '',
//     CURLOPT_MAXREDIRS => 10,
//     CURLOPT_TIMEOUT => 0,
//     CURLOPT_FOLLOWLOCATION => true,
//     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//     CURLOPT_CUSTOMREQUEST => 'GET',
//     CURLOPT_HTTPHEADER => array(
//         'X-API-ID: '. $api_id,
//         'X-API-TOKEN: '. $api_token
//     ),
// ));

    // $response = curl_exec($curl);

    // echo json_encode($response);
// curl_close($curl);

}

