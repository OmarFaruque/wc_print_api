<?php

/**
 * Plugin Name: WC Print API
 * Version: 1.0.0
 * Description: Get automatically product from print.com and store that product to wc database with price, category and all necesseary information as well. Cron job is a assential part of this process.
 * Author: Omar Faruque
 * Author URI: https://www.linkedin.com/in/omarfaruque2020/
 * Requires at least: 4.4.0
 * Tested up to: 6.1.0
 * Text Domain: wc-print
 */

 
define('PRINT_TOKEN', 'wcprint');
define('PRINT_FILE', __FILE__);
define('PRINT_PLUGIN_NAME', 'WC Print API');
define('PRINT_INIT_TIMESTAMP', gmdate( 'U' ) );
define('PRINT_PATH', realpath(plugin_dir_path(__FILE__)));
define('PRINT_API_URL', 'https://api.print.com/');


// All independent functions.
require_once(PRINT_PATH . DIRECTORY_SEPARATOR . 'inc/func/print-autoloader.php');


// Load and set up the Autoloader
$print_autoloader = new PRINT_Autoloader( dirname( __FILE__ ) );
$print_autoloader->register();

//Class 
new PRINT_Controller();

add_action('wp_head', function () {
    




});
