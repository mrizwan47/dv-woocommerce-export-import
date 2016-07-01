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

require_once( WPWEI_ROOT_PATH. "admin-panel.php" );

add_action( 'init', 'test_export_dd' );

function test_export_dd(){

	if( $_GET['fffff'] == 'gg'  ){

		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1
		);
		$loop = new WP_Query( $args );
		if ( $loop->have_posts() ) {

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

			while ( $loop->have_posts() ) : $loop->the_post();

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

			$doc = new PHPExcel();
			$doc->setActiveSheetIndex(0);
			$doc->getActiveSheet()->setTitle("products");
			$doc->getActiveSheet()->freezePane('A2');
			$doc->getActiveSheet()->fromArray($data, null, 'A1');

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="DVWEI_'.date('m-d-Y_hia').'.xls"');
			header('Cache-Control: max-age=0');

			$writer = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
			$writer->save('php://output');

		}else{
			echo 'No products found';
		}

		wp_reset_postdata();
		wp_die();

	}

}
