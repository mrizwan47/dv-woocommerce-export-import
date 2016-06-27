<?php
/**
 * DV Woocommerce Export Import
 *
 * @package     DVWoocommerceExportImport
 * @author      Designsvalley Team
 * @copyright   2016 Designsvalley
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: DV Woocommerce Export Import
 * Plugin URI:  http://designsvalley.com/
 * Description: Export/Import woocommerce data with ease.
 * Version:     1.0
 * Author:      Designsvalley Team
 * Author URI:  http://designsvalley.com/
 * Text Domain: dv-woocommerce-export-import
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

define( 'PLUGIN_NAME', "DV Woocommerce Export Import" );
define( 'PLUGIN_FILE', __FILE__ );
define( 'PLUGIN_VERSION', "1.0" );
define( 'PLUGIN_ROOT_PATH', plugin_dir_path( __FILE__ ) );
define( 'PLUGIN_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'WPML_TD', 'dv-woocommerce-export-import' );

require_once( PLUGIN_ROOT_PATH. "admin-panel.php" );
