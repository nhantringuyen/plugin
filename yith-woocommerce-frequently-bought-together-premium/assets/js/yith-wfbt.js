/**
 * frontend.js
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Frequently Bought Together Premium
 * @version 1.0.0
 */

jQuery(document).ready(function($) {
    "use strict";

    var update_form = function( form, wrap, variation_id ){
        var input       = form.find('.yith-wfbt-items input'),
            group       = [],
            unchecked   = [];

        // show only necessary
        input.each(function(i){
            group[i] = this.value;
            if( ! $(this).is(':checked') ) {
                unchecked.push( this.value );
            }
        });

        form.block({
            message: null,
            overlayCSS: {
                background: '#fff url(' + yith_wfbt.loader + ') no-repeat center',
                opacity: 0.6
            }
        });

        $.ajax({
            type: 'post',
            url: yith_wfbt.ajaxurl.toString().replace( '%%endpoint%%', yith_wfbt.refreshForm ),
            data: {
                action: yith_wfbt.refreshForm,
                product_id: form.find( 'input[name="yith-wfbt-main-product"]' ).val(),
                variation_id : variation_id,
                group: group,
                unchecked: unchecked,
                context: 'frontend'
            },
            dataType: 'html',
            success: function( response ) {
                wrap.replaceWith( response );
            },
            complete: function () {
                form.unblock();
            }
        });
    }

    $(document).on( 'change', '.yith-wfbt-items input', function(){
        update_form( $(this).closest('.yith-wfbt-form'), $(this).closest( '.yith-wfbt-section' ), 0 );
    });


    $( 'form.variations_form.cart' ).on( 'show_variation', function( ev, data ){

        if( ! data.is_in_stock ){
            return;
        }

        update_form( $('.yith-wfbt-form'), $('.yith-wfbt-section' ), data.variation_id );
    });

    /********************
     * SLIDER SHORTCODE
     *******************/

    var slider = $(document).find( '.yith-wfbt-products-list' ),
        nav    = slider.next( '.yith-wfbt-slider-nav' );

    if( slider.length ) {

        slider.owlCarousel({
            loop: true,
            dots: false,
            responsive : {
                0: {
                    items: 2
                },
                // breakpoint from 480 up
                480: {
                    items: 3
                },
                // breakpoint from 768 up
                768: {
                    items: yith_wfbt.visible_elem
                }
            }
        });

        if( nav.length ) {
            nav.find('.yith-wfbt-nav-prev').click(function () {
                slider.trigger('prev.owl.carousel');
            });

            nav.find('.yith-wfbt-nav-next').click(function () {
                slider.trigger('next.owl.carousel');
            })
        }
    }
});