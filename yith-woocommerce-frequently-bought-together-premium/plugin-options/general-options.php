<?php
/**
 * GENERAL ARRAY OPTIONS
 */

$general = array(

	'general'  => array(

		array(
			'title' => __( 'General Options', 'yith-woocommerce-frequently-bought-together' ),
			'type' => 'title',
			'desc' => '',
			'id' => 'yith-wcfbt-general-options'
		),

        array(
            'id'        => 'yith-wfbt-form-title',
            'name'      => __( 'Box title', 'yith-woocommerce-frequently-bought-together' ),
            'desc'      => __( 'Title shown on "Frequently Bought Together" box.', 'yith-woocommerce-frequently-bought-together' ),
            'type'      => 'text',
            'default'   => __( 'Frequently Bought Together', 'yith-woocommerce-frequently-bought-together' )
        ),

        array(
            'id'        => 'yith-wfbt-image-size',
            'name'      => __( 'Image Size', 'yith-woocommerce-frequently-bought-together' ),
            'desc'      => sprintf( __( 'Set image size (px). After changing these settings you may need to %s.', 'yith-woocommerce-frequently-bought-together' ), '<a href="http://wordpress.org/extend/plugins/regenerate-thumbnails/">' . __( 'regenerate your thumbnails', 'yith-woocommerce-frequently-bought-together' ) . '</a>' ),
            'type'      => 'yith_image_size',
            'default'   => array(
                'width'     => '70',
                'height'    => '70',
                'crop'      => 1
            )
        ),

        array(
            'id'        => 'yith-wfbt-redirect-checkout',
            'name'      => __( 'Redirect to checkout', 'yith-woocommerce-frequently-bought-together' ),
            'desc'      => __( 'Redirect to checkout page after add to cart action.', 'yith-woocommerce-frequently-bought-together' ),
            'type'      => 'checkbox',
            'default'   => 'no'
        ),

        array(
            'id'        => 'yith-wfbt-discount-name',
            'name'      => __( 'Discount name', 'yith-woocommerce-frequently-bought-together' ),
            'desc'      => __( 'Choose the discount name to use. This name will be visible in cart and checkout pages.', 'yith-woocommerce-frequently-bought-together' ),
            'type'      => 'text',
            'default'   => 'frequently-bought-discount'
        ),

        array(
            'id'        => 'yith-wfbt-form-position',
            'name'      => __( 'Select box position', 'yith-woocommerce-frequently-bought-together' ),
            'type'      => 'select',
            'options'   => array(
                '1' =>  __( 'Below product summary', 'yith-woocommerce-frequently-bought-together' ),
                '2' =>  __( 'Above product tabs', 'yith-woocommerce-frequently-bought-together' ),
                '3' =>  __( 'Below product tabs', 'yith-woocommerce-frequently-bought-together' ),
                '4' =>  __( 'Use shortcode [ywfbt_form product_id=""]', 'yith-woocommerce-frequently-bought-together' )
            ),
            'default'   => '2'
        ),

        array(
            'id'        => 'yith-wfbt-loader',
            'name'      => __( 'Loader Image', 'yith-woocommerce-frequently-bought-together' ),
            'desc'      => __( 'Upload a custom loading image.', 'yith-woocommerce-frequently-bought-together' ),
            'type'      => 'yith_wfbt_upload',
            'default'   => YITH_WFBT_ASSETS_URL . '/images/loader.gif'
        ),

        array(
            'type'      => 'sectionend',
            'id'        => 'yith-wcfbt-general-options'
        ),

        array(
            'title' => __( 'Labels and Style Options', 'yith-woocommerce-frequently-bought-together' ),
            'type' => 'title',
            'desc' => '',
            'id' => 'yith-wcfbt-label-style-options'
        ),

		array(
			'id'        => 'yith-wfbt-total-single-label',
			'name'      => __( 'Total label for single product', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'This is the text shown for total price label when only one product has been checked.', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'text',
			'default'   => __( 'Price', 'yith-woocommerce-frequently-bought-together' )
		),

		array(
			'id'        => 'yith-wfbt-total-double-label',
			'name'      => __( 'Total label for double products', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'This is the text shown for total price label when two products have been checked.', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'text',
			'default'   =>  __( 'Price for both', 'yith-woocommerce-frequently-bought-together' )
		),

		array(
			'id'        => 'yith-wfbt-total-three-label',
			'name'      => __( 'Total label for three products', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'This is the text shown for total price label when three products have been checked.', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'text',
			'default'   =>  __( 'Price for all three', 'yith-woocommerce-frequently-bought-together' )
		),

		array(
			'id'        => 'yith-wfbt-total-multi-label',
			'name'      => __( 'Total label for multiple products', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'This is the label shown for total price label when more than three products have been checked.', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'text',
			'default'   => __( 'Price for all', 'yith-woocommerce-frequently-bought-together' )
		),

		array(
			'id'        => 'yith-wfbt-button-single-label',
			'name'      => __( 'Button label for single product', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'This is the label shown for "Add to cart" button when only one product has been checked.', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'text',
			'default'   => __( 'Add to Cart', 'yith-woocommerce-frequently-bought-together' )
		),

		array(
			'id'        => 'yith-wfbt-button-double-label',
			'name'      => __( 'Button label for two products', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'This is the label shown for "Add to cart" button when two products have been checked.', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'text',
			'default'   =>  __( 'Add both to Cart', 'yith-woocommerce-frequently-bought-together' )
		),

		array(
			'id'        => 'yith-wfbt-button-three-label',
			'name'      => __( 'Button label for three products', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'This is the label shown for "Add to cart" button when three products have been checked.', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'text',
			'default'   =>  __( 'Add all three to Cart', 'yith-woocommerce-frequently-bought-together' )
		),

		array(
			'id'        => 'yith-wfbt-button-multi-label',
			'name'      => __( 'Button label for multiple products', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'This is the label shown for "Add to cart" button when more than two products have been checked.', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'text',
			'default'   => __( 'Add all to Cart', 'yith-woocommerce-frequently-bought-together' )
		),

		array(
			'id'        => 'yith-wfbt-form-background-color',
			'name'      => __( 'Form Background Color', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'Select background color for Frequently Bought form', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'color',
			'default'   => '#ffffff'
		),

		array(
			'id'        => 'yith-wfbt-button-color',
			'name'      => __( 'Button Color', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'Select button background color', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'color',
			'default'   => '#222222'
		),

		array(
			'id'        => 'yith-wfbt-button-color-hover',
			'name'      => __( 'Button Hover Color', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'Select button background hover color', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'color',
			'default'   => '#222222'
		),

		array(
			'id'        => 'yith-wfbt-button-text-color',
			'name'      => __( 'Button Text Color', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'Select button text color', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'color',
			'default'   => '#ffffff'
		),

		array(
			'id'        => 'yith-wfbt-button-text-color-hover',
			'name'      => __( 'Button Text Hover Color', 'yith-woocommerce-frequently-bought-together' ),
			'desc'      => __( 'Select button text hover color', 'yith-woocommerce-frequently-bought-together' ),
			'type'      => 'color',
			'default'   => '#ffffff'
		),

        array(
            'type'      => 'sectionend',
            'id'        => 'yith-wcfbt-label-style-options'
        ),

	)
);

if( defined( 'ICL_SITEPRESS_VERSION' ) && ICL_SITEPRESS_VERSION ) {
	$general['general'][] = array(
		'title' => __( 'WPML Options', 'yith-woocommerce-frequently-bought-together' ),
		'type' => 'title',
		'desc' => '',
		'id' => 'yith-wcfbt-wpml-options'
	);
	$general['general'][] = array(
		'title' => __( 'WPML Associated Products', 'yith-woocommerce-frequently-bought-together' ),
		'type'  => 'checkbox',
		'desc'  => __( 'Inherit "frequently bought together" products from original language products.', 'yith-woocommerce-frequently-bought-together' ),
		'id'    => 'yith-wcfbt-wpml-association',
		'default'   => 'yes'
	);
	$general['general'][] = array(
		'type'      => 'sectionend',
		'id'        => 'yith-wcfbt-wpml-options'
	);
}

if( defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM ){
	$general['general'][] = array(
		'title' => __( 'Vendor Products', 'yith-woocommerce-frequently-bought-together' ),
		'type' => 'checkbox',
		'desc' => __( 'Allow vendor also add products of other vendors to frequently bought form.', 'yith-woocommerce-frequently-bought-together' ),
		'id' => 'yith-wfbt-vendor-products',
		'default'   => 'no'
	);
}

return apply_filters( 'yith_wcfbt_panel_general_options', $general );