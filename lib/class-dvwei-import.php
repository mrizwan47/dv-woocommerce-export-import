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
			'truncate_before_import'	=> false,
			'replace_old_data'				=> false
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

		$this->import_taxonomies();
		die;
		$this->import_basic_products();
		die;

		// TODO keep adding new sheets

	}

	/**
	 * Import Categories, Tags and Attributes
	 */
	function import_taxonomies(){

		$categories_sheet			= $this->phpexcel_data->getSheetByName('categories');
		$categories						=	$categories_sheet->toArray(null,true,true,true);

		$cat_keys							=	array_shift($categories);

		foreach( $categories as $cat_vals ){

			$category	=	array_combine($cat_keys, $cat_vals);

			$res	=	wp_insert_term( $category['name'], 'product_cat', array(
				'description'		=> $category['description'],
				'slug'					=> $category['slug'],
				'parent'				=> $category['parent_id']
			) );

			update_term_meta( $res['term_id'], 'display_type', $category['display_type'] );

		}

	}


	/**
	 * Import Basic Products
	 */
	function import_basic_products(){

		$sheet					= $this->phpexcel_data->getSheetByName('products');
		$data						=	$sheet->toArray(null,true,true,true);
		$first					=	true;

		foreach( $data as $product ){

			if( !$first ){

				$add_p_data = array(
				  'post_title'    => wp_strip_all_tags( $product['B'] ),
				  'post_content'  => $product['G'],
				  'post_status'   => $product['AG'],
					'post_excerpt'	=> $product['F'],
					'post_type'			=> 'product',
					'comment_status'=> $product['AF']
				);

				if( $this->args['replace_old_data'] )
					$add_p_data['ID']		= $product['A'];

				print_r($product);
				//wp_insert_post( $add_p_data );

			}else{
				$first	=	false;
				print_r($product);
			}

		}

	}

}
