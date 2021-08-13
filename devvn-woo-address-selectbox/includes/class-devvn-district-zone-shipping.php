<?php
class DevVn_District_Zone_Shipping extends WC_Shipping_Method {
	public $shippingrate_title;
	function __construct( $instance_id = 0 ) {
		global $wpdb;
				
		parent::__construct( $instance_id );
		
		$this->supports = array( 'zones', 'shipping-zones', 'instance-settings' );
		$this->id = 'devvn_district_zone_shipping'; 
		$this->method_title = __( 'District Zone', 'devvn' );
		$this->method_description    = __( 'Thay đổi giá vận chuyển dựa trên quận/huyện', 'devvn' );
		$this->admin_page_heading     = __( 'Phí vận chuyển dựa trên quận/ huyện', 'devvn' );
		$this->admin_page_description = __( 'Xác định giá theo quận/huyện', 'devvn' );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		
		$this->district_rate_table = $wpdb->prefix . 'woocommerce_devvn_district_shipping_rates';

        //add_action('woocommerce_after_checkout_validation', array('devvn_woocommerce_after_checkout_validation'), 100 , 2);
		
		$this->init();
	}
    function devvn_woocommerce_after_checkout_validation($data, $errors){
        wc_add_notice( __( 'Passwords do not match.', 'woocommerce' ), 'error' );
    }
	function init() {
		
		$this->init_form_fields();
		$this->init_settings();

		$this->enabled              = $this->get_option('enabled');
		$this->type                 = 'order';
		//$this->tax_status         = $this->get_option('tax_status');
		$this->fee                  = $this->get_option('fee');
		$this->cost                 = $this->get_option('cost');
				
		$this->title                = $this->get_option( 'title' );
		$this->all_price_condition  = $this->get_option( 'all_price_condition' );
		$this->all_price_condition_w  = $this->get_option( 'all_price_condition_w' );

		$this->shippingrate_title   = $this->title;
	}
		
	function init_form_fields() {
		$this->instance_form_fields = array(
				'title'      => array(
					'title'       => __( 'Tiêu đề phương thức', 'devvn' ),
					'type'        => 'text',
					'description' => __( 'The title which the user sees during checkout, if not defined in Shipping Rates.', 'devvn' ),
					'default'     => __( 'Phí vận chuyển cho Quận/Huyện', 'devvn' ),
					'desc_tip'    => true,

				),				
				/*'tax_status' => array(
					'title'       => __( 'Tax Status', 'devvn' ),
					'type'        => 'select',
					'class' 			=> 'wc-enhanced-select',
					'description' => '',
					'default'     => 'taxable',
					'options'     => array(
						'taxable' 	=> __( 'Taxable', 'devvn' ),
						'none' 		=> _x( 'None', 'Tax status', 'devvn' )
						)
				),
				*/
				'fee'        => array(
					'title'       => __( 'Phụ thu', 'devvn' ),
					'type'        => 'text',
					'description' => __( 'Fee excluding tax, e.g. 3.50. Leave blank to disable.', 'devvn' ),
					'default'     => '',
					'desc_tip'		=> true,
				),
				
				'cost'        => array(
					'title'       => __( 'Phí vận chuyển mặc định', 'devvn' ),
					'type'        => 'text',
					'description' => __( 'Phí vận chuyển mặc định cho tất cả quận/huyện', 'devvn' ),
					'default'     => '0',
					'desc_tip'	  => true,
				),

                'all_price_condition'   => array(
                    'title'       => __( 'Tùy chỉnh điều kiện', 'devvn' ),
                    'type'        => 'text',
                    'description' => __( 'Điều kiện mặc định cho toàn bộ quận/huyện', 'devvn' ),
                    'default'     => '',
                    'desc_tip'	  => false,
                ),
                'all_price_condition_w'   => array(
                    'title'       => __( 'Tùy chỉnh điều kiện cân nặng', 'devvn' ),
                    'type'        => 'text',
                    'description' => __( 'Điều kiện cân nặng mặc định cho toàn bộ quận/huyện', 'devvn' ),
                    'default'     => '',
                    'desc_tip'	  => false,
                ),
		);
	}
	
	/**
	 * Return the instance form fields
	 *
	 * @return array of instance form fields
	 */
	function get_instance_form_fields() {
		$this->init_form_fields();
		return( $this->instance_form_fields );
	}
	
	/**
	 * Return if the method is available
	 */
	
	function is_available( $package ) {
		return( true );
	}
	
	function calculate_shipping( $package = array() ) {
        $final_rate = null;
		$final_rate = $this->get_final_district_rate($package);
        if($final_rate == 'shipdisable'){
            $rate = array(
                'id'        => 'shipdisable',
                'label'     => $this->shippingrate_title,
                'cost'      => '',
            );
            $this->add_rate( $rate );
        }elseif ( $final_rate !== false && is_numeric( $final_rate )) {
			if ( $this->fee > 0 && $package['destination']['country'] ) {
				$final_rate += $this->fee;
			}
			$rate = array(
				'id'        => $this->id . "_" .  $this->instance_id, 
				'label'     => $this->shippingrate_title,
				'cost'      => $final_rate,
				'taxes'     => '',
				'calc_tax'  => 'per_order'
			);							 
			$this->add_rate( $rate );
		}elseif($final_rate == 'free'){
			$rate = array(
                'id'        => $this->id . "_" .  $this->instance_id,
                'label'     => __('Miễn phí vận chuyển','devvn'),
                'cost'      => 0,
                'taxes'     => '',
                'calc_tax'  => 'per_order'
            );
            $this->add_rate( $rate );
        }elseif($this->cost != "" && is_numeric($this->cost)){
		    $rateCost = $this->get_final_all_district_condition($package);
            if($rateCost == 0) $this->shippingrate_title = __('Miễn phí vận chuyển','devvn');
            if ( $this->fee > 0 && $package['destination']['country'] && $rateCost > 0) {
                $rateCost += $this->fee;
            }
            $rate = array(
				'id'        => $this->id . "_" .  $this->instance_id, 
				'label'     => $this->shippingrate_title,
				'cost'      => $rateCost,
				'taxes'     => '',
				'calc_tax'  => 'per_order'
			);							 
			$this->add_rate( $rate );
		} else {
			add_filter( "woocommerce_cart_no_shipping_available_html", array( $this, 'no_shipping_available') );
		}
	}
	function set_shippingrate_title( $rate ) {

        if ( isset( $rate['box_title'] ) && $rate['box_title'] != "" ) {
            $title = $rate['box_title'];
        } else {
            $title = $this->title;
        }
		$this->shippingrate_title = $title;
		return( $title );
	}
		
	function get_final_district_rate( $package = array() ) {
        $rate = null;
        $woocommerce = function_exists('WC') ? WC() : $GLOBALS['woocommerce'];
        $rates = $this->get_boxes();
        if ( defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION, '3.0.0', '>=' ) ) {
            $customer_district = $woocommerce->customer->get_shipping_city();
        }else{
            $customer_district = $woocommerce->customer->shipping_city;
        }
        $subtotal = $woocommerce->cart->subtotal;

        $cost_rate2 = devvn_vietnam_shipping();

		$cost_rate = $cost_rate2->search_in_array($rates,'box_district',$customer_district);

		if($cost_rate && !empty($cost_rate)){
		    $box_hasadvance = isset($cost_rate[0]['box_hasadvance'])?$cost_rate[0]['box_hasadvance']:0;
		    $box_shipdisable = isset($cost_rate[0]['box_shipdisable'])?$cost_rate[0]['box_shipdisable']:0;

            $box_advance = isset($cost_rate[0]['box_advance'])?maybe_unserialize($cost_rate[0]['box_advance']):array();
            $box_advance = $cost_rate2->dwas_sort_desc_array($box_advance);
            $box_advance = $cost_rate2->dwas_search_bigger_in_array($box_advance,'dk',$subtotal);

            $box_district_hequydoi = isset($cost_rate[0]['box_district_hequydoi'])?$cost_rate[0]['box_district_hequydoi']:0;
            $total_weight = $this->get_total_cart_weight($package, $box_district_hequydoi);

		    $box_advance_w = isset($cost_rate[0]['box_advance_w'])?$cost_rate[0]['box_advance_w']:0;
            $box_district_condition_w = isset($cost_rate[0]['box_district_condition_w'])?maybe_unserialize($cost_rate[0]['box_district_condition_w']):array();
            $box_district_condition_w = $cost_rate2->dwas_sort_desc_array($box_district_condition_w);
            $box_district_condition_w2 = $cost_rate2->dwas_search_bigger_in_array_weight($box_district_condition_w,'dk',$total_weight);
            if(empty($box_district_condition_w2) && $box_advance_w == 2){
                $box_district_condition_w = $cost_rate2->dwas_format_key_array($box_district_condition_w);
            }else {
                $box_district_condition_w = $cost_rate2->dwas_sort_asc_array($box_district_condition_w2);
            }

            $box_condition_limit = isset($cost_rate[0]['box_condition_limit'])?$cost_rate[0]['box_condition_limit']:0;
            $box_condition_limitprice = isset($cost_rate[0]['box_condition_limitprice'])?$cost_rate[0]['box_condition_limitprice']:0;

		    if($box_shipdisable == 1){
                $rate = 'shipdisable';
            }else{
                if($box_hasadvance && $box_advance && is_array($box_advance)){
                    $rate = (isset($box_advance[0]['price']) && $box_advance[0]['price'] != 0) ? $box_advance[0]['price'] : 'free';
                }elseif($box_advance_w && $box_district_condition_w && is_array($box_district_condition_w)){
                    $price_fix =  (isset($box_district_condition_w[0]['price']) && $box_district_condition_w[0]['price'] != 0) ? $box_district_condition_w[0]['price'] : 0;
                    $price_dk =  (isset($box_district_condition_w[0]['dk']) && $box_district_condition_w[0]['dk'] != 0) ? $box_district_condition_w[0]['dk'] : 0;

                    if($box_condition_limit && $box_condition_limitprice && $box_condition_limit > 0 && $total_weight > $price_dk){
                        $total_weight = devvn_round_up($total_weight, $box_condition_limit);
                        $rate = $price_fix + ((($total_weight - $price_dk)*$box_condition_limitprice)/$box_condition_limit);
                    }else {
                        $rate = $price_fix;
                    }
                    if (!$rate) {
                        $rate = (isset($cost_rate[0]['box_cost']) && $cost_rate[0]['box_cost'] != 0) ? $cost_rate[0]['box_cost'] : 'free';
                    }
                }else {
                    $rate = (isset($cost_rate[0]['box_cost']) && $cost_rate[0]['box_cost'] != 0) ? $cost_rate[0]['box_cost'] : 'free';
                }


            }
			$this->set_shippingrate_title( $cost_rate[0] );
		}	
		return $rate;
	}

	function get_final_all_district_condition( $package = array() ){
        $rate = null;
        $all_price_condition  = wp_parse_args(maybe_unserialize($this->get_option( 'all_price_condition' )),array(
            'checked'   =>  0,
            'all_condition' =>  array()
        ));
        $checked = $all_price_condition['checked'];
        $all_condition = $all_price_condition['all_condition'];

        $all_price_condition_w  = wp_parse_args(maybe_unserialize($this->get_option( 'all_price_condition_w' )),array(
            'checked'   =>  0,
            'all_condition' =>  array(),
            'all_limit' =>  '',
            'all_limitprice' =>  '',
            'all_hequydoi' =>  '',
        ));
        $checked_w = $all_price_condition_w['checked'];
        $all_condition_w = $all_price_condition_w['all_condition'];
        $all_limit = $all_price_condition_w['all_limit'];
        $all_limitprice = $all_price_condition_w['all_limitprice'];
        $all_hequydoi = $all_price_condition_w['all_hequydoi'];

        $total_weight = $this->get_total_cart_weight($package, $all_hequydoi);

        $cost_rate2 = devvn_vietnam_shipping();
        $woocommerce = function_exists('WC') ? WC() : $GLOBALS['woocommerce'];
        $subtotal = $woocommerce->cart->subtotal;

        if($checked && $all_condition && is_array($all_condition) ) {
            $all_condition = $cost_rate2->dwas_sort_desc_array($all_condition);
            $all_condition = $cost_rate2->dwas_search_bigger_in_array($all_condition, 'dk', $subtotal);
            if($all_condition && is_array($all_condition)){
                $rate = (isset($all_condition[0]['price'])) ? $all_condition[0]['price'] : 0;
            }else{
                $rate = $this->cost;
            }
        }elseif($checked_w && $all_condition_w && is_array($all_condition_w) ) {
            $all_condition_w = $cost_rate2->dwas_sort_desc_array($all_condition_w);
            $all_condition_w2 = $cost_rate2->dwas_search_bigger_in_array_weight($all_condition_w, 'dk', $total_weight);
            if(empty($all_condition_w2)){
                $all_condition_w = $cost_rate2->dwas_format_key_array($all_condition_w);
            }else {
                $all_condition_w = $cost_rate2->dwas_sort_asc_array($all_condition_w2);
            }
            $price_fix =  (isset($all_condition_w[0]['price']) && $all_condition_w[0]['price'] != 0) ? $all_condition_w[0]['price'] : 0;
            $price_dk =  (isset($all_condition_w[0]['dk']) && $all_condition_w[0]['dk'] != 0) ? $all_condition_w[0]['dk'] : 0;
            if($all_limit && $all_limitprice && $all_limit > 0 && $total_weight > $price_dk){
                $total_weight = devvn_round_up($total_weight, $all_limit);
                $rate = $price_fix + ((($total_weight - $price_dk) * $all_limitprice) / $all_limit);
            }else {
                $rate = $price_fix;
            }
            if (!$rate) {
                $rate = $this->cost;
            }
        }else{
            $rate = $this->cost;
        }
        return $rate;
    }

    function get_total_cart_weight( $package = array() , $hequydoi = '' ){
	    if(!$package || !is_array($package)) return 0;
        $cost_rate2 = devvn_vietnam_shipping();
        $prods = array();
        $total_weight = 0;
        if(isset($package['contents'])) {
            $items = $package['contents'];
            if ($items && !empty($items)) {
                foreach ($items as $prod) {
                    $prods[] = array(
                        'weight' => ($prod['data']->get_weight()) ? $cost_rate2->convert_weight_to_kg($prod['data']->get_weight()) : 0,
                        'length' => ($prod['data']->get_length()) ? $cost_rate2->convert_dimension_to_cm($prod['data']->get_length()) : 0,
                        'width' => ($prod['data']->get_width()) ? $cost_rate2->convert_dimension_to_cm($prod['data']->get_width()) : 0,
                        'height' => ($prod['data']->get_height()) ? $cost_rate2->convert_dimension_to_cm($prod['data']->get_height()) : 0,
                        'qty' => ($prod['quantity']) ? $prod['quantity'] : 0,
                    );
                }
            }
        }
        if($prods && !empty($prods)){
            foreach($prods as $prod){
                if($hequydoi){
                    $khoiluong_quydoi = $hequydoi;
                }else {
                    $khoiluong_quydoi = $cost_rate2->get_options('khoiluong_quydoi');
                }
                $prod_weight = 0;
                if($khoiluong_quydoi && $khoiluong_quydoi > 0 && $prod['length'] && $prod['width'] && $prod['height']){
                    $prod_weight = ($prod['length']*$prod['width']*$prod['height'])/$khoiluong_quydoi;
                }
                $prod_weight = max($prod_weight, $prod['weight']);
                $total_weight += $prod_weight * $prod['qty'];
            }
        }
        return $total_weight;
    }

	function no_shipping_available( $html ) {
		if ( $this->shippingrate_title && $this->shippingrate_title != $this->title ) {
			$html = $this->shippingrate_title;
		}
		return( $html );
	}
	//Add custom field
	public function instance_options() {
		?>
		<table class="form-table dwas_table">
			<?php
			// Generate the HTML For the settings form.
			$this->generate_settings_html( $this->get_instance_form_fields() );
			?>
            <tr>
                <th><?php _e( 'Tùy chỉnh điều kiện', 'devvn' ); ?> <span class="woocommerce-help-tip" data-tip="<?php _e( 'Điều kiện mặc định cho toàn bộ quận huyện', 'devvn' ); ?>"></span></th>
                <td>
                    <?php devvn_box_condition_price( $this ); ?>
                </td>
            </tr>
			<tr>
				<th><?php _e( 'Giá vận chuyển', 'devvn' ); ?> <span class="woocommerce-help-tip" data-tip="<?php _e( 'Phí vận chuyển cho từng quận/huyện', 'devvn' ); ?>"></span></th>
				<td>
					<?php devvn_box_shipping_admin_rows( $this ); ?>
				</td>
			</tr>
		</table>
		<?php
	}
	public function admin_options() {
        $this->all_price_condition  = $this->get_option( 'all_price_condition' );
        $this->all_price_condition_w  = $this->get_option( 'all_price_condition_w' );
		$this->instance_options();
	}
	
	public function process_admin_options() {
		parent::process_admin_options();
		devvn_box_shipping_admin_rows_process( $this->instance_id );
	}
	
	public function get_boxes( $args = array() ) {
			global $wpdb;
	
			$defaults = array();
	
			$args = wp_parse_args( $args, $defaults );
	
			extract( $args, EXTR_SKIP );
	
			return $wpdb->get_results(
				$wpdb->prepare( "
					SELECT * FROM {$this->district_rate_table}
					WHERE shipping_method_id IN ( %s )
				", $this->instance_id ),ARRAY_A
			);
		}
	
    
} // end DevVn_District_Zone_Shipping

function devvn_woocommerce_confirm_password_validation( $posted, $errors ) {
    if ( WC()->cart->needs_shipping() ) {
        $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
        foreach ( WC()->shipping->get_packages() as $i => $package ) {
            if ( isset($chosen_shipping_methods[0]) && $chosen_shipping_methods[0] == 'shipdisable' && isset( $chosen_shipping_methods[ $i ], $package['rates'][ $chosen_shipping_methods[ $i ] ] ) ) {
                $shipping_rate = $package['rates'][ $chosen_shipping_methods[ $i ] ];
                $errors->add( 'shipping', $shipping_rate->label );
            }
        }
    }
}
add_action( 'woocommerce_after_checkout_validation', 'devvn_woocommerce_confirm_password_validation', 10, 2 );