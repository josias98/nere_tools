<?php
/**
 * Single Product Meta
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/meta.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post, $product;

?>
<div class="product_meta financity-title-font">

	<?php do_action( 'woocommerce_product_meta_start' ); ?>

	<?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>

		<span class="sku_wrapper"><?php esc_html_e( 'SKU:', 'financity' ); ?> <span class="sku"><?php 
			if( $sku = $product->get_sku() ){
				echo gdlr_core_text_filter($sku);
			}else{
			 	echo esc_html__( 'N/A', 'financity' );
			}
		?></span></span>

	<?php endif; ?>

	<?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'financity' ) . ' ', '</span>' ); ?>

	<?php echo wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'financity' ) . ' ', '</span>' ); ?>

	<?php do_action( 'woocommerce_product_meta_end' ); ?>

</div>

<?php

 	// sharing option
	if( financity_get_option('general', 'blog-social-share', 'enable') == 'enable' ){
		if( class_exists('gdlr_core_pb_element_social_share') ){
			echo '<div class="financity-woocommerce-social-share" >';
			echo gdlr_core_pb_element_social_share::get_content(array(
				'social-head' => 'none',
				'text-align'=>'left',
				'facebook'=>financity_get_option('general', 'blog-social-facebook', 'enable'),
				'linkedin'=>financity_get_option('general', 'blog-social-linkedin', 'enable'),
				'google-plus'=>financity_get_option('general', 'blog-social-google-plus', 'enable'),
				'pinterest'=>financity_get_option('general', 'blog-social-pinterest', 'enable'),
				'stumbleupon'=>financity_get_option('general', 'blog-social-stumbleupon', 'enable'),
				'twitter'=>financity_get_option('general', 'blog-social-twitter', 'enable'),
				'email'=>financity_get_option('general', 'blog-social-email', 'enable'),
				'padding-bottom'=>'0px',
				'no-pdlr'=>true
			));
			echo '</div>';
		}
	}
?>
