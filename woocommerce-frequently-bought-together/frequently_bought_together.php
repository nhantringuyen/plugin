<?php
/*
Plugin Name: Woocommerce Frequently Bought Together
Plugin URI: http://aheadzen.com/
Description: Frequently bought together plugin to display frequently bought products on product detail page and cart page.
Author: Aheadzen Team
Version: 1.0.2
Author URI: http://aheadzen.com/
*/

add_action('init','bought_together_init');
function bought_together_init()
{
	add_filter('template_include','bought_together_include');
	load_plugin_textdomain('aheadzen', false, basename( dirname( __FILE__ ) ) . '/languages');
}

function bought_together_include($template)
{
	bought_together_to_cart();
	return $template;
}

function bought_together_to_cart()
{
	global $woocommerce;
	if($_GET['add-to-cart-multiple']){
		$pids = $_GET['add-to-cart-multiple'];
		$pid_arr = explode(',',$pids);
		if($pid_arr){
			for($i=0;$i<count($pid_arr);$i++){
				$product_id = $pid_arr[$i];
				$quantity = 1;
				// Add the product to the cart
				if ( WC()->cart->add_to_cart( $product_id, $quantity ) ) {
					wc_add_to_cart_message( $product_id );
					$was_added_to_cart = true;
					$added_to_cart[] = $product_id;
				}
			}
		}
		wp_redirect($woocommerce->cart->get_cart_url());exit;
	}
}

function get_bought_together_products($pids,$exclude_pids=0)
{
	$all_products = array();
	$pids_count = count($pids);
	$pid = implode(',',$pids);
	global $wpdb,$table_prefix;
	if ($pids_count>1 ||  ($pids_count==1 && !$all_products = wp_cache_get( 'bought_together_'.$pid, 'ah_bought_together' )) ) {
		$subsql = "SELECT oim.order_item_id FROM ".$table_prefix."woocommerce_order_itemmeta oim where oim.meta_key='_product_id' and oim.meta_value in ($pid)";
		$sql = "SELECT oi.order_id from  ".$table_prefix."woocommerce_order_items oi where oi.order_item_id in ($subsql) limit 100";
		$all_orders = $wpdb->get_col($sql);
		if($all_orders){
			$all_orders_str = implode(',',$all_orders);
			$subsql2 = "select oi.order_item_id FROM ".$table_prefix."woocommerce_order_items oi where oi.order_id in ($all_orders_str) and oi.order_item_type='line_item'";
			if($exclude_pids){
				$sub_exsql2 = " and oim.meta_value not in ($pid)";
			}
			$sql2 = "select oim.meta_value as product_id,count(oim.meta_value) as total_count from ".$table_prefix."woocommerce_order_itemmeta oim where oim.meta_key='_product_id' $sub_exsql2 and oim.order_item_id in ($subsql2) group by oim.meta_value order by total_count desc limit 15";
			$all_products = $wpdb->get_col($sql2);
			if($pids_count==1){
				wp_cache_add( 'bought_together_'.$pid, $all_products, 'ah_bought_together' );
			}
		}
	}
	return $all_products;
}

add_action( 'woocommerce_after_single_product_summary', 'bought_together_product_detail_display', 9 );
function bought_together_product_detail_display()
{
	if(get_option('az_fbp_on_product_detail')=='on')return true;
	
	$pid =  get_the_id();
	$products = get_bought_together_products(array($pid));
	if($products){
		bought_together_addto_cart(array_splice($products,0,3));
		bought_together_related_products($products);
	}
}

add_action('woocommerce_after_cart','bought_together_cart_display');
function bought_together_cart_display()
{
	if(get_option('az_fbp_on_cart')=='on')return true;
	
	global $woocommerce;
	$cart_contents_count = $woocommerce->cart->cart_contents_count;
	$product_arr = array();
	if(!$woocommerce->cart->cart_contents){return;}
	foreach($woocommerce->cart->cart_contents as $key => $cart_content){
		$product_arr[] = $cart_content['product_id'];
	}
	if($product_arr){
		$products = get_bought_together_products($product_arr,1);
		$title = __('Customers Who Bought Items in Your Cart Also Bought','aheadzen');
		bought_together_related_products($products,$title);
	}
}

/*Function to display the bought together product list*/
function bought_together_related_products($products,$title='')
{
	if(count($products)<4){return;}
	$woocommerce_loop['columns'] = $columns;
	$args =array(
		'post_type'            => 'product',
		'ignore_sticky_posts'  => 1,
		'no_found_rows'        => 1,
		'posts_per_page'       => 12,
		'post__in'             => $products
	);
	$products_list = new WP_Query( $args );
	if(!$title){$title=__( 'Customers Who Bought This Item Also Bought', 'aheadzen' );}
	if ( $products_list->have_posts() ) : ?>

		<div class="related products">

			<h2><?php echo $title; ?></h2>

			<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products_list->have_posts() ) : $products_list->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

		</div>

	<?php endif;

	wp_reset_postdata();
}

/*Function to display products with add to cart button*/
function bought_together_addto_cart($products)
{
	if(count($products)<=1){return;}
	$args =array(
		'post_type'            => 'product',
		'ignore_sticky_posts'  => 1,
		'no_found_rows'        => 1,
		'posts_per_page'       => 10,
		'post__in'             => $products
	);
	
	$products_buy = new WP_Query( $args );	
	if($products_buy){
		$add_to_cart_pid_arr = array();
		$add_to_cart_arr = array();
		$total_price = 0;
		if ( $products_buy->have_posts() ){
			while ( $products_buy->have_posts() ){
				$products_buy->the_post();
				$size = 'shop_thumbnail';
				$pid = get_the_id();
				$add_to_cart_pid_arr[] = $pid;
				echo $products_buy->add_to_cart_url();
				if ( has_post_thumbnail() ) {
					$image = get_the_post_thumbnail($pid, $size );
				} elseif ( wc_placeholder_img_src() ) {
					$image =  wc_placeholder_img( $size );
				}
				global $product;
				$prd_price = $product->get_display_price();
				$prd_link = get_permalink();
				$total_price += $prd_price;
				$cart_content = '';
				$cart_content .= '<div class="bought_prd" price="'.$prd_price.'">';
				$cart_content .= '<a href="'.$prd_link.'">'.$image.'</a>';
				$cart_content .= '<div class="bought_title"><input type="checkbox" name="bought_pid[]" value="'.$pid.'" checked > <a href="'.$prd_link.'">'.get_the_title($pid).'</a></div>';
				$cart_content .= '<div class="bought_price">'.$product->get_price_html().'</div>';
				$cart_content .= '</div>';
				$add_to_cart_arr[] = $cart_content;
			}
		}
		
		if($add_to_cart_arr){
			echo '<h4>'.__('Frequently Bought Together','aheadzen').'</h4>';
			echo '<div id="bought_together_frm">';
			echo '<div class="bought_together_prds">';
			echo implode(' <div class="bought_plus">+</div> ',$add_to_cart_arr);
			$pids = implode(',',$add_to_cart_pid_arr);
			echo '</div>';
			echo '<div class="boubht_add_to_cart"><div class="bought_price_total">'.get_woocommerce_currency_symbol().$total_price.'</div><a class="single_add_to_cart_button button also_bought_css_button" href="#">'.__('Add 3 Items To Cart','aheadzen').'</a></div>';
			echo '<input type="hidden" name="bought_selected_prdid" value="'.$pids.'" id="bought_selected_prdid" >';
			echo '</div>';
			echo '<script>
				jQuery("#bought_together_frm input:checkbox").click(function() {
					var total_price = 0;
					var priceval = [];
					var counter = 0;
					var currency = "'.get_woocommerce_currency_symbol().'";
					jQuery("#bought_together_frm :checkbox:checked").each(function(i){
					  pid = jQuery(this).val();
					  priceval[i] = pid;
					  price = jQuery(this).closest(".bought_prd").attr("price");
					  total_price = parseInt(total_price) + parseInt(price);
					  counter = i+1;
					});
					jQuery(".bought_price_total").html(currency+total_price);
					jQuery("#bought_selected_prdid").val(priceval);
					if(counter==3){
						var button_text = "'.__('Add 3 Items To Cart','aheadzen').'";
					}else if(counter==2){
						var button_text = "'.__('Add Both Items To Cart','aheadzen').'";
					}else if(counter==1){
						var button_text = "'.__('Add To Cart','aheadzen').'";
					}else if(counter==0){
						var button_text = "'.__('Select Atleast One Item','aheadzen').'";
					}
					jQuery(".single_add_to_cart_button").html(button_text);
					
				});
				
				jQuery(".single_add_to_cart_button").click(function() {
					var addtocarturl = "'.site_url('?add-to-cart-multiple=').'";
					var priceval = jQuery("#bought_selected_prdid").val();
					if(priceval){
						addtocarturl = addtocarturl+priceval;
						jQuery("a.single_add_to_cart_button").attr("href",addtocarturl);
					}else{
						return false;
					}
				});
				
			</script>';
		}		
	}
}

add_action('wp_head','bought_together_head');
function bought_together_head()
{
?>
<style>
#bought_together_frm{display: inline-block;margin-bottom: 30px;margin-top: 30px;width: 100%;}
.bought_together_prds {display: inline-block;max-width: 425px;width: 100%;float:left;}
div.bought_prd{display: inline-block;float: left;width:120px;max-width:120px;text-align: center;}
div.bought_prd div{font-size:14px;text-align: left;}
div.bought_plus{float: left;font-size: 25px;padding:25px 15px 0 0;}
.boubht_add_to_cart{float: left; margin-left:30px;text-align: center;}
.bought_price{ border-top: 1px solid #ccc;margin-top: 5px;padding-top: 2px;}
.bought_title a{border-bottom: 0 none;}


@media screen and (max-width: 600px) 
{
	.bought_together_prds {max-width:100%;}
	div.bought_plus { padding: 0; margin:0 auto;}
	div.bought_prd{max-width: 30%; width:auto; margin:0 auto;}
	.boubht_add_to_cart {display: inline;float: left;margin-left: 0;text-align: center;width: 100%;}
}

</style>
<?php
}

add_action('admin_menu','aheadzen_voter_admin_menu');
add_action('admin_enqueue_scripts','az_enqueue_color_picker');

/*************************************************
Admin Settings For plugin menu function
*************************************************/
function aheadzen_voter_admin_menu()
{
	add_submenu_page('options-general.php', 'Frequently Bought Together Options', 'Frequently Bought Together Options', 'manage_options', 'fbp','az_fbp_settings_page');
}

function az_fbp_settings_page()
{
	global $bp,$post;	
	if($_POST)
	{
		update_option('az_fbp_on_cart',$_POST['az_fbp_on_cart']);
		update_option('az_fbp_on_product_detail',$_POST['az_fbp_on_product_detail']);
		echo '<script>window.location.href="'.admin_url().'options-general.php?page=fbp&msg=success";</script>';
		exit;
	}
	?>
	<h2><?php _e('Frequently Bought Together Settings','aheadzen');?></h2>
	<?php
	if($_GET['msg']=='success'){
	echo '<p class="success">'.__('Your settings updated successfully.','aheadzen').'</p>';
	}
	?>
	<style>.success{padding:10px; border:solid 1px green; width:70%; color:green;font-weight:bold;}</style>
	<form method="post" action="<?php echo admin_url();?>options-general.php?page=fbp">
		<table class="form-table">
			
			<tr id="az_fbp_on_product_detail_tr" valign="top">
				<td>
					<h4><?php _e('Product Display Settings','aheadzen');?> </h4>
					<label for="az_fbp_on_product_detail">
					<input type="checkbox" id="az_fbp_on_product_detail" name="az_fbp_on_product_detail" <?php echo get_option('az_fbp_on_product_detail')=='on' ? 'checked':' '; ?>>
					<?php _e('Hide on Product Detail Page?','aheadzen');?>
					</label>
				</td>
			</tr>
			<tr id="az_fbp_on_cart_tr" valign="top">
				<td>
					<label for="az_fbp_on_cart">
					<input type="checkbox" id="az_fbp_on_cart" name="az_fbp_on_cart" <?php echo get_option('az_fbp_on_cart')=='on'? 'checked':' '; ?>>
					<?php _e('Hide on Cart Page?','aheadzen');?>
					</label>
				</td>
			</tr>
			
			<tr valign="top">
				<td>
					<input type="hidden" name="page_options" value="<?php echo $value;?>" />
					<input type="hidden" name="action" value="update" />
					<input type="submit" value="Save settings" class="button-primary"/>
				</td>
			</tr>					
		</table>
	</form>
	<?php
	// Check that the user is allowed to update options  
	if (!current_user_can('manage_options'))
	{
		wp_die('You do not have sufficient permissions to access this page.');
	}
}