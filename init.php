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

define( 'WPWEI_NAME', "DV Woocommerce Export Import" );
define( 'WPWEI_FILE', __FILE__ );
define( 'WPWEI_VERSION', "1.0" );
define( 'WPWEI_ROOT_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPWEI_ROOT_URL', plugin_dir_url( __FILE__ ) );
define( 'WPWEI_TD', 'dv-woocommerce-export-import' );

if( !class_exists('PHPExcel') )
	require_once( WPWEI_ROOT_PATH. "phpexcel/PHPExcel.php" );

require_once( WPWEI_ROOT_PATH. "lib/class-dvwei-export.php" );
require_once( WPWEI_ROOT_PATH. "admin-panel.php" );

function dvwei_export_products(){

	if( $_GET['dvwooei_action'] == 'export'  ){

		$export		=	new DVWEI_Export;
		$export->clean_export();

	}

}
add_action( 'admin_init', 'dvwei_export_products' );
