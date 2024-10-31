<?php
/*-------------------------------------------------------------------
Plugin Name: My Restaurant Menu
Plugin URI: http://wordpress.org/plugins/my-restaurant-menu/
Description: Create and manage menu(s) for your restaurant, including a printable PDF version.
Version: 0.2.0
Author: Marco Piarulli
Text Domain: my-restaurant-menu
Domain Path: /languages
--------------------------------------------------------------------*/
define( 'MP62_MRM_VERSION', '0.2.0' );
define( 'MP62_MRM_PLUGIN_PATH', dirname( __FILE__ ) );							// /var/www/domain.tld/wp-content/plugins/plugin-name
define( 'MP62_MRM_PLUGIN_FOLDER', basename( MP62_MRM_PLUGIN_PATH ) );			// plugin-name
define( 'MP62_MRM_PLUGIN_URL', plugins_url() . '/' . MP62_MRM_PLUGIN_FOLDER );	// http://domain.tld/wp-content/plugins/plugin-name
define( 'MP62_TEXTDOMAIN', 'my-restaurant-menu' );

// include() or require() any necessary files here...
include_once( 'includes/class-mp62-mrm-main.php' );
include_once( 'includes/class-mp62-mrm-items.php' );
include_once( 'includes/class-mp62-mrm-menus.php' );
include_once( 'includes/class-mp62-mrm-utilities.php' );
include_once( 'includes/class-mp62-mrm-settings.php' );

register_activation_hook( __FILE__, array( 'mp62_mrm_Main', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'mp62_mrm_Main', 'deactivate' ) );

// Tie into WordPress Hooks and any functions that should run on load.
add_action( 'init', 'mp62_mrm_Main::initialize' );
add_action( 'init', 'mp62_mrm_Main::register_post_types' );
add_action( 'init', 'mp62_mrm_Main::register_taxonomies' );

add_action( 'admin_init', 'mp62_mrm_Main::admin_init' );
add_action( 'admin_init', 'mp62_mrm_Settings::page_init' );
add_action( 'admin_menu', 'mp62_mrm_Main::add_menu_pages' );
add_action( 'admin_enqueue_scripts', 'mp62_mrm_Main::enqueue_admin_scripts' );
add_action( 'wp_enqueue_scripts', 'mp62_mrm_Main::enqueue_frontend_scripts' );

add_action( 'edit_mp62_mrm_item_section', 'mp62_mrm_Main::save_image', 10, 1 );
add_action( 'create_mp62_mrm_item_section', 'mp62_mrm_Main::save_image', 10, 1 );

add_filter( 'manage_edit-mp62_mrm_item_section_columns', 'mp62_mrm_Main::add_taxonomy_columns' );
add_filter( 'manage_mp62_mrm_item_section_custom_column', 'mp62_mrm_Main::manage_taxonomy_column', 10, 3 );

add_action( 'edit_mp62_mrm_item_need', 'mp62_mrm_Main::save_image', 10, 1 );
add_action( 'create_mp62_mrm_item_need', 'mp62_mrm_Main::save_image', 10, 1 );

add_filter( 'manage_edit-mp62_mrm_item_need_columns', 'mp62_mrm_Main::add_taxonomy_columns' );
add_filter( 'manage_mp62_mrm_item_need_custom_column', 'mp62_mrm_Main::manage_taxonomy_column', 10, 3 );

add_action( 'save_post_mp62_mrm_item', 'mp62_mrm_Items::save_custom_fields', 1, 2 );
add_action( 'save_post_mp62_mrm_menu', 'mp62_mrm_Menus::save_custom_fields', 1, 2 );

add_filter( 'post_row_actions', 'mp62_mrm_Utilities::add_custom_action_link', 10, 2 );
add_action( 'admin_action_duplicate_post', 'mp62_mrm_Utilities::make_duplicate_post' );
add_action( 'admin_action_generate_pdf', 'mp62_mrm_Menus::generate_pdf' );

add_filter( 'pre_get_posts', 'mp62_mrm_Main::sort_items' );
add_filter( 'the_content', 'mp62_mrm_Menus::show_menu' );

add_shortcode( 'mrm', 'mp62_mrm_Menus::menu_shortcode' );

add_filter( 'mce_buttons_2', 'mp62_mrm_Utilities::mce_buttons' );
add_filter( 'tiny_mce_before_init', 'mp62_mrm_Utilities::mce_google_fonts_array' );

/*
 * filter to put Wordpress SEO metaboxes after the standard metaboxes
 */
add_filter( 'wpseo_metabox_prio', function() { return 'low'; } );
?>
