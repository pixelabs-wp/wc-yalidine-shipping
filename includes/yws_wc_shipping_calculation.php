<?php
add_filter('woocommerce_shipping_methods', 'add_alpha_shipping');
function add_alpha_shipping($methods)
{
    $methods['YalidineShipping'] = 'Yalidine_Shipping_Method';
    return $methods;
}

class Yalidine_Shipping_Method extends WC_Shipping_Method
{
    /**
     * Constructor for your shipping class
     *
     * @access public
     * @return void
     */

    public function __construct()
    {
        $this->id = 'YalidineShipping';
        $this->method_title = __('Yalidine Shipping methods', 'YALIDINE_domain');
        $this->method_description = __('Yalidine Shipping methods for home and stop desk', 'YALIDINE_domain');

        // Availability & Countries
        $this->availability = 'including';
        $this->countries = ['DZ'];
        $this->init();
        $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
        $this->title = isset($this->settings['title']) ? $this->settings['title'] : __('Yalidine Shipping methods', 'YALIDINE_domain');
    }

    /**
     * Init your settings
     *
     * @access public
     * @return void
     */
    function init()
    {
        $this->init_form_fields();
        $this->init_settings();

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    function init_form_fields()
    {

        $this->form_fields = [
            'enabled' => [
                'title' => __('Enable', 'YALIDINE_domain'),
                'type' => 'checkbox',
                'description' => __('Enable this shipping.', 'YALIDINE_domain'),
                'default' => 'yes'
            ],

            'title' => [
                'title' => __('Title', 'YALIDINE_domain'),
                'type' => 'text',
                'description' => __('Yalidine Shipping methods for home and stop desk', 'YALIDINE_domain'),
                'default' => __('Yalidine Shipping methods', 'YALIDINE_domain')
            ],
        ];
    }

    /**
     * calculate_shipping function.
     *
     * @access public
     * @param array $package
     * @return void
     */
    public function calculate_shipping($package = array())
    {

        // Get the destination state from the package
        $destination_state = isset($package['destination']['state']) ? $package['destination']['state'] : '';
        $destination_walaya_name = get_state($destination_state);
        // Log the state value for debugging
        // error_log('Destination State: ' . $destination_walaya_name);

        // Split the state value to get Wilaya ID
        $wilaya_id = explode('-', $destination_state);

        $yws_shipping_fees_json = yws_get_provinance_shipping_fees();
        $yws_shipping_fees = json_decode($yws_shipping_fees_json, true);

        $matched_fees = [];

        foreach ($yws_shipping_fees['data'] as $wilaya_info) {
            if ($wilaya_info['wilaya_name'] === $destination_walaya_name) {
                $matched_fees = [
                    'home_fee' => $wilaya_info['home_fee'],
                    'desk_fee' => $wilaya_info['desk_fee'],
                ];
                break; // Stop iterating after finding a match
            }
        }

        if (empty($matched_fees)) {
            // Handle the case where no matching wilaya is found
            error_log("No fees found for wilaya:" . $destination_walaya_name);
        } else {

            $allKeys = array_keys($matched_fees);

            $index = 0;
            $rates = [];
            for ($index = 0 ; $index< count($matched_fees) ; $index++){
                $key_name =  $allKeys[$index];
    
                if ($key_name == "home_fee"){
                    $formatted_key_name = "livraison à domicile";
                }
                else if ($key_name == "desk_fee"){
                    $formatted_key_name = "livraison desktop";
                }

                $rates[] = array(
                    'id' =>  $key_name,
                    'label' => $formatted_key_name,
                    'cost' => $matched_fees[$key_name]
                );
            }
    
        
    
            if(!empty($rates)) {
                foreach($rates as $rate) {
                    $this->add_rate( $rate );
                }
            }
            
            // Access the extracted fees
            error_log("Home Fee: " . $matched_fees['home_fee']);
            error_log("Desk Fee: " . $matched_fees['desk_fee']);
        }

        // $this->add_rate([
        //     'id' => 'yalidine_home_shipping',
        //     'label' => __('Yalidine Home Delivery', 'YALIDINE_domain'),
        //     'cost' => $matched_fees['home_fee'],
        //     'calc_tax' => 'per_item', // Adjust tax calculation if needed
        //     'package_rates' => ['^([^\.]*)$'], // Allows for various package weights
        //   ]);

        //   $this->add_rate([
        //     'id' => 'yalidine_desk_shipping',
        //     'label' => __('Yalidine Desk Delivery', 'YALIDINE_domain'),
        //     'cost' => $matched_fees['desk_fee'],
        //     'calc_tax' => 'per_item', // Adjust tax calculation if needed
        //     'package_rates' => ['^([^\.]*)$'], // Allows for various package weights
        //   ]);

        // Display radio buttons on the checkout page (explained later)
        //   add_action('woocommerce_after_shipping_rate', [$this, 'add_shipping_method_radio_buttons']);

        // wp_send_json($yws_shipping_fee);

        // $rate = array(
        //     'label' => $destination_walaya_name,
        //     'cost' => $matched_fees['home_fee'],
        //     'calc_tax' => 'per_item'
        // );

        // // Register the rate
        // $this->add_rate($rate);

       
    // // Make a shipping rate for each result
        // foreach ($matched_fees as $option) {

        //    $key_name =  $allKeys[$index];
        //     $rates = array(
        //         'id' =>  $key_name,
        //         'label' => $key_name,
        //         'cost' => $option
        //     );

        //     $index = $index + 1;
        //     // Register the rate
        // }

        // wp_send_json($rates);
    }


}
