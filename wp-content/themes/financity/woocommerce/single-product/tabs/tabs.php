<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter tabs and allow third parties to add their own.
 *
 * Each tab is an array containing title, callback and priority.
 *
 * @see woocommerce_default_product_tabs()
 */
if( class_exists('gdlr_core_pb_element_tab') ){
	
	$tabs = apply_filters( 'woocommerce_product_tabs', array() );

	if( !empty($tabs) ){
		echo '<div class="financity-woocommerce-tab gdlr-core-tab-item gdlr-core-js gdlr-core-tab-style2-horizontal gdlr-core-center-align" >';
		
		$count = 0; $active = 1;
		echo '<div class="gdlr-core-tab-item-title-wrap clearfix gdlr-core-title-font" >';
		foreach( $tabs as $key => $tab ){ $count++;
			echo '<div class="gdlr-core-tab-item-title financity-title-font ' . ($count == $active? ' gdlr-core-active': '') . '" data-tab-id="' . esc_attr($count) . '" >';
			echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', esc_html( $tab['title'] ), $key );
			echo '</div>';
		}
		echo '<div class="gdlr-core-tab-item-title-line gdlr-core-skin-divider"></div>';
		echo '</div>'; // gdlr-core-tab-item-tab-title-wrap
		
		$count = 0;
		echo '<div class="gdlr-core-tab-item-content-wrap clearfix" >';
		foreach( $tabs as $key => $tab ){ $count++;
			echo '<div class="gdlr-core-tab-item-content ' . ($count == $active? ' gdlr-core-active': '') . '" data-tab-id="' . esc_attr($count) . '" >';
			call_user_func( $tab['callback'], $key, $tab );
			echo '</div>';
		}
		echo '</div>'; // gdlr-core-tab-item-tab
		echo '</div>'; // gdlr-core-tab-item
	}

}else{

$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $product_tabs ) ) : ?>

	<div class="woocommerce-tabs wc-tabs-wrapper">
		<ul class="tabs wc-tabs" role="tablist">
			<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
				<li class="<?php echo esc_attr( $key ); ?>_tab" id="tab-title-<?php echo esc_attr( $key ); ?>" role="tab" aria-controls="tab-<?php echo esc_attr( $key ); ?>">
					<a href="#tab-<?php echo esc_attr( $key ); ?>">
						<?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
			<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> panel entry-content wc-tab" id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel" aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
				<?php
				if ( isset( $product_tab['callback'] ) ) {
					call_user_func( $product_tab['callback'], $key, $product_tab );
				}
				?>
			</div>
		<?php endforeach; ?>

		<?php do_action( 'woocommerce_product_after_tabs' ); ?>
	</div>

<?php endif; ?>
<?php } ?>