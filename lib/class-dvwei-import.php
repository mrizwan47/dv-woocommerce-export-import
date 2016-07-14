<?php
/**
 * DV Woocommerce Import Class
 *
 * Class to handle import of woocommerce data
 *
 * @author      Rizwan <rizwan@designsvalley.com>
 * @package     DVWoocommerceExportImport
 * @copyright   2016 Designsvalley
 * @link        http://designsvalley.com/
 * @since       Version 1.0
 * @TODO				some commenting and make class more extensible
 */

class DVWEI_Import{

	/**
	 * Args for Import
	 * @access private
	 */
	private $args;


	/**
	 * PHPExcel data
	 */
	private $phpexcel_data;


	/**
	 * Error
	 */
	var $error;


	/**
	 * __construct to import data
	 */
	function __construct( $import_file, $args=array() ){

		// Confirm the existance of file
		if( ! file_exists($import_file) ){
			$this->error	=	"Excel file could not be found.";
			return false;
		}

		$args = wp_parse_args( $args, array(
			'truncate_before_import'	=> false
		));

		/**
     * Filter the default args import
     *
     * @since 1.0
     *
     * @param array $args
     */
		$args				=	apply_filters( 'dvwei_import_args', $args );
		$this->args	=	$args;

		// Initialize
		$this->init( $import_file );

	}

	/**
	 * Initialization
	 * @access private
	 */
	private function init( $import_file ){

		// Truncate old data
		if( $this->args['truncate_before_import'] ){
			// TODO
		}

		$this->phpexcel_data = PHPExcel_IOFactory::load( $import_file );

	}

	/**
	 * Importing...
	 */
	public function import(){

		$this->import_basic_products();

		// TODO keep adding new sheets

	}

	/**
	 * Import Basic Products
	 */
	function import_basic_products(){

		$sheet					= $this->phpexcel_data->getSheetByName('products');
		$data						=	$sheet->toArray(null,true,true,true);

		//array_flip(current());

		foreach( $data as $product ){



		}

	}

}
