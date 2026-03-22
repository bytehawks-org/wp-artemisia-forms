<?php
/**
 * Plugin Name: Artemisia Forms (By ByteHawks)
 * Plugin URI:  https://github.com/bytehawks/artemisia-forms
 * Description: A robust, high-performance, OOP-based WordPress form builder and management system.
 * Version:     1.0.0
 * Author:      ByteHawks
 * License:     GPLv2 or later
 * Text Domain: artemisia-forms
 *
 * @package ByteHawks\ArtemisiaForms
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'ARTEMISIA_FORMS_VERSION', '1.0.0' );
define( 'ARTEMISIA_FORMS_FILE', __FILE__ );
define( 'ARTEMISIA_FORMS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ARTEMISIA_FORMS_URL', plugin_dir_url( __FILE__ ) );

// Require the Composer autoloader or use fallback
if ( file_exists( ARTEMISIA_FORMS_PATH . 'vendor/autoload.php' ) ) {
    require_once ARTEMISIA_FORMS_PATH . 'vendor/autoload.php';
} else {
    // Fallback PSR-4 autoloader for ByteHawks\ArtemisiaForms
    spl_autoload_register( function( $class ) {
        $prefix = 'ByteHawks\\ArtemisiaForms\\';
        $base_dir = ARTEMISIA_FORMS_PATH . 'includes/';
        $len = strlen( $prefix );
        if ( strncmp( $prefix, $class, $len ) !== 0 ) {
            return;
        }
        $relative_class = substr( $class, $len );
        $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
        if ( file_exists( $file ) ) {
            require $file;
        }
    } );
}

/**
 * Initialize the core plugin after plugins are loaded.
 */
function run_artemisia_forms() {
    // Register REST API endpoints
    add_action( 'rest_api_init', function() {
        $forms_controller = new \ByteHawks\ArtemisiaForms\Api\FormsController();
        $forms_controller->register_routes();
    } );

    // Register Admin Dashboard Menu
    if ( is_admin() ) {
        $admin_menu = new \ByteHawks\ArtemisiaForms\Admin\AdminMenu();
        $admin_menu->init();
    }

    // Register Frontend Shortcode
    $shortcode = new \ByteHawks\ArtemisiaForms\Frontend\Shortcode();
    $shortcode->init();
}
add_action( 'plugins_loaded', 'run_artemisia_forms' );

/**
 * Plugin activation hook.
 */
function activate_artemisia_forms() {
    \ByteHawks\ArtemisiaForms\Core\Activator::activate();
}
register_activation_hook( __FILE__, 'activate_artemisia_forms' );

/**
 * Plugin deactivation hook.
 */
function deactivate_artemisia_forms() {
    \ByteHawks\ArtemisiaForms\Core\Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_artemisia_forms' );
