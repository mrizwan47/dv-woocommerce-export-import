<?php
/**
 * DV Woocommerce Export/Import Class
 *
 * Class to handle export and import of woocommerce data
 *
 * @author      Rizwan <rizwan@designsvalley.com>
 * @package     DVWoocommerceExportImport
 * @copyright   2016 Designsvalley
 * @link        http://designsvalley.com/
 * @since       Version 1.0
 * @TODO				some commenting and make class more extensible
 */

class DVWEI{

	/**
	 * WP Query object
	 */
	private $WPQ_Object;

	/**
	 * PHPExcel object
	 */
	private $dvphpexcel;

	/**
	 * __construct to import or export data
	 *
	*/
	function __construct( $args=array() ){

		$args = wp_parse_args( $args, array(
			'post_type'				=> 'product',
			'posts_per_page'	=> -1
		));

		$args		=	apply_filters( 'dvwei_query_args', $args );

		$this->WPQ_Object		=	new WP_Query( $args );
		$this->dvphpexcel		=	new PHPExcel();

	}

	function clean_export(){

		$this->prepare_basic_products();
		$this->prepare_category_data();
		$this->prepare_tags_data();

		// TODO Keep adding additional *prepare* functions for more sheets
		$this->save_excel();

	}
	/**
	 * Prepare Basic Product Data
	 */
	function prepare_basic_products(){

		$data	=	array();

		// Headers
		$data[]		=	array(
			'product_id',
			'product_name',
			'sku',
			'category_ids',
			'product_tags',
			'short_description',
			'long_description',
			'featured_image',
			'gallary_images',
			'product_type',
			'virtual',
			'downloadable',
			'regular_price',
			'sales_price',
			'manage_stock',
			'stock_qty',
			'allow_backorders',
			'stock_status',
			'sold_individually',
			'weight',
			'weight_unit',
			'length',
			'width',
			'height',
			'dimensions_unit',
			'shipping_class',
			'up_sells',
			'cross_sells',
			'group_of',
			'purchase_note',
			'menu_order',
			'enable_rewiews',
			'status',
			'visibility'
		);

		while ( $this->WPQ_Object->have_posts() ) : $this->WPQ_Object->the_post();

			global $product;

			$categories				=	implode(', ', wp_get_post_terms( $product->id, 'product_cat', array( 'fields' => 'ids' ) ));
			$tags							=	implode(', ', wp_get_post_terms( $product->id, 'product_tag', array( 'fields' => 'ids' ) ));
			$featured_image		=	wp_get_attachment_url(get_post_thumbnail_id($product->id));
			$gallary_images		=	array();
			$weight_unit			=	get_option('woocommerce_weight_unit');
			$dimensions_unit	=	get_option('woocommerce_dimension_unit');
			$purchase_note		=	get_post_meta( $product->id, '_purchase_note', true );
			$gallary_ids			=	$product->get_gallery_attachment_ids();

			if( $product->product_type == 'simple' || $product->product_type == 'external'  ){
				$group_of	=	$product->post_parent;
			}else{
				$group_of	=	0;
			}

			foreach( $gallary_ids as $attachment_id ){
				$gallary_images[] = wp_get_attachment_url( $attachment_id );
			}

			$data[]		=	array(
				'product_id'				=>	$product->id,
				'product_name'			=>	$product->post->post_title,
				'sku'								=>	$product->sku,
				'category_ids'			=>	$categories,
				'product_tags'			=>	$tags,
				'short_description'	=>	$product->post->post_excerpt,
				'long_description'	=>	$product->post->post_content,
				'featured_image'		=>	$featured_image,
				'gallary_images'		=>	implode(', ', $gallary_images),
				'product_type'			=>	$product->product_type,
				'virtual'						=>	$product->virtual,
				'downloadable'			=>	$product->downloadable,
				'regular_price'			=>	$product->regular_price,
				'sales_price'				=>	$product->sale_price,
				'manage_stock'			=>	$product->manage_stock,
				'stock_qty'					=>	$product->get_stock_quantity(),
				'allow_backorders'	=>	$product->backorders,
				'stock_status'			=>	$product->stock_status,
				'sold_individually'	=>	$product->sold_individually,
				'weight'						=>	$product->weight,
				'weight_unit'				=>	$weight_unit,
				'length'						=>	$product->length,
				'width'							=>	$product->width,
				'height'						=>	$product->height,
				'dimensions_unit'		=>	$dimensions_unit,
				'shipping_class'		=>	$product->shipping_class,
				'up_sells'					=>	$product->get_upsells(),
				'cross_sells'				=>	$product->get_cross_sells(),
				'group_of'					=>	$group_of,
				'purchase_note'			=>	$purchase_note,
				'menu_order'				=>	$product->post->menu_order,
				'enable_rewiews'		=>	$product->post->comment_status,
				'status'						=>	$product->post->post_status,
				'visibility'				=>	$product->visibility
			);

		endwhile;

		$this->dvphpexcel->setActiveSheetIndex(0);
		$this->dvphpexcel->getActiveSheet()->setTitle("products");
		$this->dvphpexcel->getActiveSheet()->freezePane('A2');
		$this->dvphpexcel->getActiveSheet()->fromArray($data, null, 'A1');

	}

	/**
	 * Preparing category data
	 */
	function prepare_category_data(){

		$categories = get_terms( array(
		    'taxonomy'		=> 'product_cat',
		    'hide_empty'	=> false,
		));

		$data	=	array();

		// Headers
		$data[]		=	array(
			'category_id',
			'name',
			'slug',
			'parent_id',
			'description',
			'display_type',
			'thumbnail'
		);

		foreach( $categories as $cat ){

			$data[]		=	array(
				'category_id'		=> $cat->term_id,
				'name'					=> $cat->name,
				'slug'					=> $cat->slug,
				'parent_id'			=> $cat->parent,
				'description'		=> $cat->description,
				'display_type'	=> get_term_meta('display_type', $cat->term_id, true),
				'thumbnail'			=> wp_get_attachment_url( get_term_meta('thumbnail_id', $cat->term_id, true) )
			);

		}

		$this->dvphpexcel->createSheet(1);
		$this->dvphpexcel->setActiveSheetIndex(1);
		$this->dvphpexcel->getActiveSheet()->setTitle("categories");
		$this->dvphpexcel->getActiveSheet()->freezePane('A2');
		$this->dvphpexcel->getActiveSheet()->fromArray($data, null, 'A1');

	}

	/**
	 * Preparing tags data
	 */
	function prepare_tags_data(){

		$tags = get_terms( array(
		    'taxonomy'		=> 'product_tag',
		    'hide_empty'	=> false,
		));

		$data	=	array();

		// Headers
		$data[]		=	array(
			'tag_id',
			'name',
			'slug',
			'description'
		);

		foreach( $tags as $tag ){

			$data[]		=	array(
				'tag_id'			=> $tag->term_id,
				'name'				=> $tag->name,
				'slug'				=> $tag->slug,
				'description'	=> $tag->description
			);

		}

		$this->dvphpexcel->createSheet(2);
		$this->dvphpexcel->setActiveSheetIndex(2);
		$this->dvphpexcel->getActiveSheet()->setTitle("tags");
		$this->dvphpexcel->getActiveSheet()->freezePane('A2');
		$this->dvphpexcel->getActiveSheet()->fromArray($data, null, 'A1');

	}


	/**
	 * Save excel
	 */
	function save_excel(){

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="DVWEI_'.date('m-d-Y_hia').'.xls"');
		header('Cache-Control: max-age=0');

		$writer = PHPExcel_IOFactory::createWriter($this->dvphpexcel, 'Excel5');
		$writer->save('php://output');
		wp_reset_postdata();
		exit();

	}

}
