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
		$this->prepare_pa_tax();
		$this->prepare_pa_terms();
		$this->prepare_attributes();
		$this->prepare_variations();

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
	 * Prepare Products Attributes Taxonomies
	 */
	function prepare_pa_tax(){

		$pa_taxes		= wc_get_attribute_taxonomies();

		$data	=	array();

		// Headers
		$data[]		=	array(
			'attribute_id',
			'attribute_name',
			'attribute_label',
			'attribute_orderby',
			'attribute_public'
		);

		foreach( $pa_taxes as $pa_tax ){

			$data[]		=	array(
				'attribute_id'			=>	$pa_tax->attribute_id,
				'attribute_name'		=>	$pa_tax->attribute_name,
				'attribute_label'		=>	$pa_tax->attribute_label,
				'attribute_orderby'	=>	$pa_tax->attribute_orderby,
				'attribute_public'	=>	$pa_tax->attribute_public
			);

		}

		$this->dvphpexcel->createSheet(3);
		$this->dvphpexcel->setActiveSheetIndex(3);
		$this->dvphpexcel->getActiveSheet()->setTitle("attributes");
		$this->dvphpexcel->getActiveSheet()->freezePane('A2');
		$this->dvphpexcel->getActiveSheet()->fromArray($data, null, 'A1');

	}


	/**
	 * Prepare Products Attributes Terms
	 */
	function prepare_pa_terms(){

		$pa_taxes		= wc_get_attribute_taxonomies();

		$data	=	array();

		// Headers
		$data[]		=	array(
			'attribute_name',
			'term_id',
			'term_name',
			'term_slug',
			'term_parent',
			'term_description'
		);

		foreach( $pa_taxes as $pa_tax ){

			$taxonomy_name		=	'pa_'.$pa_tax->attribute_name;

			$terms = get_terms( array(
				'taxonomy'		=> $taxonomy_name,
				'hide_empty'	=> false,
			));

			foreach( $terms as $term ){

				$data[]		=	array(
					'attribute_name'		=>	$taxonomy_name,
					'term_id'						=>	$term->term_id,
					'term_name'					=>	$term->name,
					'term_slug'					=>	$term->slug,
					'term_parent'				=>	$term->parent,
					'term_description'	=>	$term->description
				);

			}

		}

		$this->dvphpexcel->createSheet(4);
		$this->dvphpexcel->setActiveSheetIndex(4);
		$this->dvphpexcel->getActiveSheet()->setTitle("attributes_terms");
		$this->dvphpexcel->getActiveSheet()->freezePane('A2');
		$this->dvphpexcel->getActiveSheet()->fromArray($data, null, 'A1');

	}

	/**
	 * Prepare Attributes
	 */
	function prepare_attributes(){

		$pa_taxes		= wc_get_attribute_taxonomies();

		$data	=	array();

		// Headers
		$data[]		=	array(
			'product_id',
			'taxonomy',
			'term_id'
		);

		while ( $this->WPQ_Object->have_posts() ) : $this->WPQ_Object->the_post();

			global $product;

			//if( !$product->is_type( 'variable' ) )
				//continue;

			foreach( $pa_taxes as $pa_tax ){

				$taxonomy_name		=	'pa_'.$pa_tax->attribute_name;
				$product_terms		= wp_get_object_terms( $product->id,  $taxonomy_name );

				if ( ! empty( $product_terms ) && ! is_wp_error( $product_terms ) ) {

					foreach( $product_terms as $term ) {

						$data[]		=	array(
							'product_id'	=>	$product->id,
							'taxonomy'		=>	$taxonomy_name,
							'term_id'			=>	$term->term_id
						);

					}

				}

			}

		endwhile;

		$this->dvphpexcel->createSheet(5);
		$this->dvphpexcel->setActiveSheetIndex(5);
		$this->dvphpexcel->getActiveSheet()->setTitle("terms_relation");
		$this->dvphpexcel->getActiveSheet()->freezePane('A2');
		$this->dvphpexcel->getActiveSheet()->fromArray($data, null, 'A1');

	}


	/**
	 * Prepare Variations
	 */
	function prepare_variations(){

		// Headers
		$data	=	array();
		$data[]		=	array(
			'product_id',
			'variation_id',
      'variation_is_visible',
      'variation_is_active',
      'is_purchasable',
      'display_price',
      'display_regular_price',
      'attributes',
      'image_src',
      'image_link',
      'image_title',
      'image_alt',
      'image_caption',
      'image_srcset',
      'image_sizes',
      'price_html',
      'availability_html',
      'sku',
      'weight',
      'dimensions',
      'min_qty',
      'max_qty',
      'backorders_allowed',
      'is_in_stock',
      'is_downloadable',
      'is_virtual',
      'is_sold_individually',
      'variation_description'
		);

		while ( $this->WPQ_Object->have_posts() ) : $this->WPQ_Object->the_post();

			global $product;

			// Filter variable products only
			if( !$product->is_type( 'variable' ) )
				continue;

			foreach ( $product->get_children() as $child_id ) {

				$vars				= $product->get_child( $child_id );
		    $variation	= $product->get_available_variation( $vars );

				$data[]		=	array(
					'product_id'							=>	$product->id,
					'variation_id'						=>	$variation['variation_id'],
		      'variation_is_visible'		=>	$variation['variation_is_visible'],
		      'variation_is_active'			=>	$variation['variation_is_active'],
		      'is_purchasable'					=>	$variation['is_purchasable'],
		      'display_price'						=>	$variation['display_price'],
		      'display_regular_price'		=>	$variation['display_regular_price'],
		      'attributes'							=>	json_encode($variation['attributes']),
		      'image_src'								=>	$variation['image_src'],
		      'image_link'							=>	$variation['image_link'],
		      'image_title'							=>	$variation['image_title'],
		      'image_alt'								=>	$variation['image_alt'],
		      'image_caption'						=>	$variation['image_caption'],
		      'image_srcset'						=>	$variation['image_srcset'],
		      'image_sizes'							=>	$variation['image_sizes'],
		      'price_html'							=>	$variation['price_html'],
		      'availability_html'				=>	$variation['availability_html'],
		      'sku'											=>	$variation['sku'],
		      'weight'									=>	$variation['weight'],
		      'dimensions'							=>	$variation['dimensions'],
		      'min_qty'									=>	$variation['min_qty'],
		      'max_qty'									=>	$variation['max_qty'],
		      'backorders_allowed'			=>	$variation['backorders_allowed'],
		      'is_in_stock'							=>	$variation['is_in_stock'],
		      'is_downloadable'					=>	$variation['is_downloadable'],
		      'is_virtual'							=>	$variation['is_virtual'],
		      'is_sold_individually'		=>	$variation['is_sold_individually'],
		      'variation_description'		=>	$variation['variation_description']
				);

			}

		endwhile;

		$this->dvphpexcel->createSheet(6);
		$this->dvphpexcel->setActiveSheetIndex(6);
		$this->dvphpexcel->getActiveSheet()->setTitle("variations");
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
