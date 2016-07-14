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
require_once( WPWEI_ROOT_PATH. "lib/class-dvwei-import.php" );
require_once( WPWEI_ROOT_PATH. "admin-panel.php" );

function dvwei_export_products(){

	if( $_GET['dvwooei_action'] == 'export'  ){

		$export		=	new DVWEI_Export;
		$export->clean_export();

	}

	if( $_FILES['imported_file']['type'] == 'application/vnd.ms-excel' && $_FILES['imported_file']['error'] === 0 ){

		if( !function_exists('wp_handle_upload') ){
		  require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$uploadedfile			= $_FILES['imported_file'];
		$upload_overrides	= array( 'test_form' => false );
		$movefile					= wp_handle_upload( $uploadedfile, $upload_overrides );

		if( $movefile && ! isset($movefile['error']) ){

			$import		=	new DVWEI_Import( $movefile['file'] );
			$import->import();

		}else{
			add_settings_error( 'dvweei_upload_error', esc_attr( 'settings_updated' ), $movefile['error'], 'error' );
		}

	}

}

add_action( 'admin_init', 'dvwei_export_products' );
