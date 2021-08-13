<?php
/**
 * Form template
 *
 * @author YITH
 * @package YITH WooCommerce Frequently Bought Together Premium
 * @version 1.3.0
 */

if( empty( $product ) ) {
    global $product;
}

if( ! isset( $products ) ) {
	return;
}
/**
 * @type $product WC_Product
 */
// set query
$url        = ! is_null( $product ) ? $product->get_permalink() : '';
$url        = add_query_arg( 'action', 'yith_bought_together', $url );
$url        = wp_nonce_url( $url, 'yith_bought_together' );
$is_wc_30   = version_compare( WC()->version, '3.0.0', '>=' );

?>

<div class="yith-wfbt-section woocommerce">
	<?php if( $title != '' ) {
		echo '<h3>' . esc_html( $title ) . '</h3>';
	}

    if( ! empty( $additional_text ) ){
        echo '<p class="additional-text">' . $additional_text . '</p>';
    }

	?>

	<form class="yith-wfbt-form" method="post" action="<?php echo esc_url( $url ) ?>">

        <?php if( ! $show_unchecked ) : ?>
            <table class="yith-wfbt-images">
                <tbody>
                    <tr>
                        <?php $i = 0; foreach( $products as $product ) :

                            if( in_array( $product->get_id(), $unchecked ) ) {
                                continue;
                            }
                            ?>

                            <?php if( $i > 0 ) : ?>
                                <td class="image_plus image_plus_<?php echo $i ?>" data-rel="offeringID_<?php echo $i ?>">+</td>
                            <?php endif; ?>
                            <td class="image-td" data-rel="offeringID_<?php echo $i ?>">
                                <a href="<?php echo $product->get_permalink() ?>">
                                    <?php echo $product->get_image( 'yith_wfbt_image_size' ); ?>
                                </a>
                            </td>
                        <?php $i++; endforeach; ?>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if( ! $is_empty ) : ?>
            <div class="yith-wfbt-submit-block">
                <div class="price_text">
                    <span class="total_price_label">
                        <?php echo esc_html( $label_total ) ?>:
                    </span>
                    &nbsp;
                    <span class="total_price">
                        <?php echo $total ?>
                    </span>
                </div>

                <input type="submit" class="yith-wfbt-submit-button button" value="<?php echo esc_html( $label ); ?>">
            </div>
        <?php endif; ?>

		<ul class="yith-wfbt-items">
            <?php $j = 0; foreach( $products as $product ) :
                $product_id = $product->get_id();
                ?>
                <li class="yith-wfbt-item">
                    <label for="offeringID_<?php echo $j ?>">
                        <input type="checkbox" name="offeringID[]" id="offeringID_<?php echo $j ?>" class="active" value="<?php echo $product_id ?>"
                               <?php echo ( ! in_array( $product_id, $unchecked ) && ! $show_unchecked ) ? 'checked="checked"' : '' ?>>

                        <?php if( $product_id != $main_product_id ) : ?>
                            <a href="<?php echo $product->get_permalink() ?>">
                        <?php endif ?>

                        <span class="product-name">
				            <?php echo ( ( $product_id == $main_product_id ) ? __( 'This Product', 'yith-woocommerce-frequently-bought-together' ) . ': ' : '' ) . sprintf( '%1$s %2$s', $product->get_title(), wc_get_formatted_variation( $product, true ) ); ?>
                        </span>

                        <?php if( $product_id != $main_product_id ) : ?>
                            </a>
                        <?php endif; ?>

                        - <span class="price"><?php echo $product->get_price_html() ?></span>

                    </label>
                    <?php do_action('yith_wfbt_end_item',$product); ?>
                </li>
            <?php $j++; endforeach; ?>
		</ul>

        <input type="hidden" name="yith-wfbt-main-product" value="<?php echo $main_product_id ?>" >
	</form>
</div>