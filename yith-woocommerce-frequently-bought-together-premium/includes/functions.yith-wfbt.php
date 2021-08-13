<?php
/**
 * Common functions
 *
 * @author YITH
 * @package YITH WooCommerce Frequently Bought Together Premium
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WFBT' ) ) {
	exit;
} // Exit if accessed directly

if( ! function_exists( 'yith_wfbt_get_meta' ) ) {
    /**
     * Get plugin product meta
     *
     * @since 1.3.0
     * @author Francesco Licandro
     * @param mixed $product
     * @param string $key
     * @return mixed
     */
    function yith_wfbt_get_meta( $product, $key = '' ){
        // get product if id is passed
        ( $product instanceof WC_Product ) || $product = wc_get_product( intval( $product ) );

        if( ! $product ) {
            return '';
        }

        $metas = yit_get_prop( $product, YITH_WFBT_META, true );
        if( ! is_array( $metas ) || empty( $metas ) ) {
            $metas = array();
            // search for single meta
            foreach(  YITH_WFBT()->old_metas as $old_key => $new_key ) {
                $metas[ $new_key ] = yit_get_prop( $product, $old_key, true );
                delete_post_meta( $product->get_id(), $old_key );
            }

            update_post_meta( $product->get_id(), YITH_WFBT_META, $metas );
        }

        // merge with default
        $metas = array_merge( array(
            'default_variation'     => '',
            'use_related'           => 'no',
            'products'              => array(),
            'num_visible'           => '',
            'show_unchecked'        => 'no',
            'additional_text'       =>  '',
            'discount_type'         => 'percentage',
            'discount_fixed'        => '',
            'discount_percentage'   => '',
            'discount_min_spend'    => '',
            'discount_min_products' => '2'
        ), $metas );

        if( ! $key ) {
            return $metas;
        }

        return isset( $metas[ $key ] ) ? $metas[ $key ] : '';
    }
}

if( ! function_exists( 'yith_wfbt_set_meta' ) ) {
    /**
     * Get plugin product meta
     *
     * @since 1.3.0
     * @author Francesco Licandro
     * @param mixed $product
     * @param array $value
     */
    function yith_wfbt_set_meta( $product, $value = array() ){
        // get product if id is passed
        ( $product instanceof WC_Product ) || $product = wc_get_product( intval( $product ) );

        if( $product && is_array( $value ) ) {
            $metas = yit_get_prop( $product, YITH_WFBT_META, true );
            is_array( $metas ) && $value = array_merge( $metas, $value );


            yit_save_prop( $product, YITH_WFBT_META, $value );
        }
    }
}

if( ! function_exists( 'yith_wfbt_delete_meta' ) ) {
    /**
     * Get plugin product meta
     *
     * @since 1.3.0
     * @author Francesco Licandro
     * @param mixed $product
     */
    function yith_wfbt_delete_meta( $product ){
        // get product if id is passed
        ( $product instanceof WC_Product ) || $product = wc_get_product( intval( $product ) );

        if( $product ) {
            yit_delete_prop( $product, YITH_WFBT_META );
        }
    }
}

if( ! function_exists( 'yith_wfbt_discount_message' ) ) {
    /**
     * Build message based on discount passed
     *
     * @since 1.3.0
     * @author Francesco Licandro
     * @param array $discount
     * @return string
     */
    function yith_wfbt_discount_message( $discount ) {

        if( $discount['min_spend'] && $discount['min_products'] ) {
            $message = sprintf( __( 'Spend at least %s for %s or more products of this group and get a %s off.', 'yith-woocommerce-frequently-bought-together' ), $discount['min_spend'], $discount['min_products'], $discount['amount'] );
        }
        else {
            $message = sprintf( __( 'Purchase %s or more products and get a %s off.', 'yith-woocommerce-frequently-bought-together' ), array( $discount['min_products'], $discount['amount'] ) );
        }

        return apply_filters( 'yith_wfbt_discount_message', $message, $discount );
    }
}

if( ! function_exists( 'yith_wfbt_discount_code_validation' ) ){
    /**
     * Validate a discount code
     *
     * @since 1.3.4
     * @author Francesco Licandro
     * @param string $code
     * @return string
     */
    function yith_wfbt_discount_code_validation( $code ) {

        $code = strtolower( trim( $code ) );
        $code = str_replace( ' ', '-', $code );
        $code = preg_replace( '/[^0-9a-z-]/', '', $code );

        return $code;
    }
}