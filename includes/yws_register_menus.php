<?php
/**
 * Add Yalidine menu and submenus in WordPress admin.
 */
// function yalidine_wc_add_admin_menus() {
//     add_menu_page(
//         'Yalidine',
//         'Yalidine',
//         'manage_options',
//         'yalidine-parcels', // Linking main menu to Parcels submenu.
//         'yalidine_wc_parcels_page',
//         'dashicons-location',
//         80
//     );

//     add_submenu_page(
//         'yalidine-parcels',
//         'Stopdesk',
//         'Stopdesk',
//         'manage_options',
//         'yalidine-stopdesk',
//         'yalidine_wc_stopdesk_page'
//     );
// }

add_action( 'admin_menu', 'yalidine_wc_add_admin_menus' );


/**
* Add YALIDINE menu
*/
function yalidine_wc_add_admin_menus() {
    add_menu_page('YALIDINE', 'YaWoo Manager', 'manage_options', 'woo-yalidine', 'yalidine_woocommerce_welcome');
    add_submenu_page( 'woo-yalidine', 'yalidine-parcels', 'Display Packages', 'manage_options', 'yalidine-parcels', 'yalidine_wc_parcels_page' );
    add_submenu_page( 'woo-yalidine', 'yalidine-stop-desks', 'Stopdesk', 'manage_options', 'yalidine-stop-desks', 'yalidine_wc_stopdesk_page' );
    remove_submenu_page('woo-yalidine','woo-yalidine');
}