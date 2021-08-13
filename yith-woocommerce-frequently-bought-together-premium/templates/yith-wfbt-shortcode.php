<?php
/**
 * shortcode template
 *
 * @author YITH
 * @package YITH WooCommerce Frequently Bought Together Premium
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( empty( $args['products'] ) ){
	return;
}

// enqueue style
wp_enqueue_style( 'yith-wfbt-carousel-style' );
wp_enqueue_script( 'yith-wfbt-carousel-js' );
wp_enqueue_style( 'yith-wfbt-style' );

$title = get_option( 'yith-wfbt-slider-title' );

?>
<div class="woocommerce yith-wfbt-slider-wrapper">

	<?php if( $title ) : ?>
		<h3><?php echo $title ?></h3>
	<?php endif; ?>

	<div class="yith-wfbt-slider">

		<ul class="yith-wfbt-products-list products">
			<?php foreach( $args['products'] as $product_id ) : $product = wc_get_product( $product_id ); ?>
				<?php if( is_bool( $product ) ) continue; ?>

			<li class="yith-wfbt-single-product product">

				<?php if( get_option( 'yith-wfbt-slider-product-image' ) == 'yes' ) : ?>
					<div class="yith-wfbt-product-image">
						<a href="<?php echo $product->get_permalink() ?>">
							<?php echo $product->get_image( 'shop_catalog' ); ?>
						</a>
					</div>
				<?php endif; ?>

				<div class="yith-wfbt-product-info">

					<?php if( get_option( 'yith-wfbt-slider-product-title' ) == 'yes' ) : ?>
						<h3 class="product-title">
							<a href="<?php echo $product->get_permalink() ?>">
								<?php echo $product->get_title(); ?>
							</a>
						</h3>
					<?php endif; ?>

					<?php if( $product->is_type( 'variation' ) && get_option( 'yith-wfbt-slider-product-variation' ) == 'yes' ) : ?>
						<div class="product-attributes">
							<?php echo implode( ',', $product->get_variation_attributes() ) ?>
						</div>
					<?php endif; ?>

					<?php echo get_option( 'yith-wfbt-slider-product-price' ) == 'yes' ? '<div class="product-price">' . $product->get_price_html() . '</div>' : '' ?>

					<?php if( get_option( 'yith-wfbt-slider-product-rating' ) == 'yes' ) {
					    if( function_exists( 'wc_get_rating_html' ) ) {
					        echo wc_get_rating_html( $product->get_average_rating() );
                        }
                        else {
					        echo $product->get_rating_html();
                        }
                    } ?>

				</div>


				<?php
				//build add_to_cart url
                $product_base_id = yit_get_base_product_id( $product );
                $url = add_query_arg( 'add-to-cart', $product_base_id );
				if( $product->is_type( 'variation' ) ){
					$url = add_query_arg( array_merge( array( 'variation_id' => $product_id ), yit_get_prop( $product, 'variation_data', true ) ), $url );
				}

				$url = add_query_arg( 'yith_wfbt_shortcode', 1, $url );
				$label_buy  = esc_html( get_option( 'yith-wfbt-slider-buy-button' ) );
				$label_wish = esc_html( get_option( 'yith-wfbt-slider-wishlist-button' ) );
				?>

				<div class="yith-wfbt-product-actions">
					<a class="button yith-wfbt-add-to-cart alt" href="<?php echo esc_url_raw( $url ) ?>"
					   data-product_id="<?php echo ( $product->is_type( 'variation' ) ) ? $product_id : $product_base_id ?>">
						<?php echo $label_buy ?>
					</a>
					<a href="#" class="button yith-wfbt-add-wishlist" data-product-id="<?php echo $product_base_id ?>">
						<?php echo $label_wish ?>
					</a>
				</div>

			</li>

			<?php endforeach; ?>
		</ul>

		<div class="yith-wfbt-slider-nav">
			<div class="yith-wfbt-nav-prev"></div>
			<div class="yith-wfbt-nav-next"></div>
		</div>

	</div>

</div>