<?php
/**
 * @link https://dsmart.com
 * @author Vietsmiler
 * @package Dsmart
 */
if ( ! function_exists( 'dsmart_setup' ) ) :
	function dsmart_setup() {
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		register_nav_menus( array(
			'primary' => esc_html__( 'Primary', 'dsmart' ),
		) );
		register_nav_menus( array(
			'top-header' => esc_html__( 'Top Header', 'dsmart' ),
		) );
		register_nav_menus( array(
			'news' => esc_html__( 'Menu news', 'dsmart' ),
		) );
		register_nav_menus( array(
			'side' => esc_html__( 'Side menu', 'dsmart' ),
		) );
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );
		add_theme_support( 'woocommerce' );
	}
endif;
add_action( 'after_setup_theme', 'dsmart_setup' );
function my_login_logo() { ?>
    <style type="text/css">
    	body{background:#23282d!important;}
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/logo.png);
		    height: 65px;
		    width: 320px;
		    background-size: contain;
		    background-repeat: no-repeat;
		    padding-bottom: 0;
        }
        .login #backtoblog a, .login #nav a{color: #f2f2f2!important}
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );
function dsmart_scripts() {		
	wp_enqueue_style( 'dsmart-style', get_stylesheet_uri() );
	wp_enqueue_style( 'materialize-style', get_template_directory_uri().'/css/assets/owl.carousel.css' );

	wp_enqueue_script( 'dsmart-swiper', get_template_directory_uri() . '/js/swiper.min.js', array('jquery'), '2.0', true );
	wp_enqueue_script( 'dsmart-froogaloop2', get_template_directory_uri() . '/js/froogaloop2.min.js', array('jquery'), '2.0', true );
	wp_enqueue_script( 'dsmart-html5lightbox', get_template_directory_uri() . '/js/html5lightbox.js', array('jquery'), '2.0', true );
	wp_enqueue_script( 'dsmart-wow', get_template_directory_uri() . '/js/wow.min.js', array('jquery'), '2.0', true );
	wp_enqueue_script( 'dsmart-owl', get_template_directory_uri() . '/js/owl.carousel.js', array('jquery'), '2.0', true );
	wp_enqueue_script( 'dsmart-bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), '2.0', true );
	wp_enqueue_script( 'dsmart-magnific', get_template_directory_uri() . '/js/jquery.magnific-popup.min.js', array('jquery'), '2.0', true );
	wp_enqueue_script( 'dsmart-elevateZoom', get_template_directory_uri() . '/js/jquery.elevateZoom.min.js', array('jquery'), '2.0', true );
	wp_enqueue_script( 'dsmart-bootstrap-notify', get_template_directory_uri() . '/js/bootstrap-notify.min.js', array('jquery'), '3.8', true );	
	wp_enqueue_script( 'dsmart-isotope', get_template_directory_uri() . '/js/isotope.pkgd.min.js', array('jquery'), '3.8', true );	
	wp_enqueue_script( 'dsmart-scrollbar', get_template_directory_uri() . '/js/jquery.scrollbar.js', array('jquery'), '3.8', true );	
	wp_enqueue_script( 'main_js', get_template_directory_uri().'/js/main.js',array('jquery'), '3.8', true );
	wp_enqueue_script( 'plus_js', get_template_directory_uri().'/js/plus.js',array('jquery'), '3.8', true );
	wp_enqueue_script( 'slide_js', get_template_directory_uri().'/js/slide.js',array('jquery'), '3.8', true );
}

add_action( 'wp_enqueue_scripts', 'dsmart_scripts' );
// admin scripts
function arrowicode_admin_scripts() {
	wp_enqueue_style( 'jquery-ui.css', get_template_directory_uri() . '/css/jquery-ui.css', false, '1.0.0' );
    wp_enqueue_style( 'theme.default.css', get_template_directory_uri() . '/css/theme.default.css', false, '1.0.0' );
	wp_enqueue_style( 'admin_css', get_template_directory_uri() . '/css/admin.css', false, '1.0.0' );

    // wp_enqueue_script( 'jquery.tablesorter', get_template_directory_uri() . '/js/jquery.tablesorter.combined.js', array('jquery'), '3.3.7', true );
  //   wp_enqueue_script( 'dsmart-bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), '2.0', true );
 	// wp_enqueue_script( 'admin_style_js', get_template_directory_uri().'/js/admin.js',array('jquery'), '3.8', true );
}
add_action( 'admin_enqueue_scripts', 'arrowicode_admin_scripts' );
/**
 * Theme option.
 */
require get_template_directory() . '/inc/theme-option.php';
/**
 * widgets.
 */
require get_template_directory() . '/inc/widgets.php';
require get_template_directory() . '/BFI_Thumb.php';
require_once get_template_directory() . '/inc/shortcode.php';
require get_template_directory() . '/custom-function/woocommerce-function.php';
// require get_template_directory() . '/custom-function/comment-function.php';
require get_template_directory() . '/custom-function/account-function.php';
require get_template_directory() . '/custom-function/services-function.php';
require get_template_directory() . '/custom-function/buy-functions.php';
/**
 * Register widget area.
 */
function dsmart_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Contact infomation', 'dsmart' ),
		'id'            => 'contact_info',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
    
    register_sidebar( array(
		'name'          => esc_html__( 'Shop', 'dsmart' ),
		'id'            => 'shop_product',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) ); 
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar right', 'dsmart' ),
		'id'            => 'sidebar_right',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) ); 
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar home', 'dsmart' ),
		'id'            => 'sidebar_home',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="widget news-wg">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) ); 
  
    register_sidebar( array(
		'name'          => esc_html__( 'Về chúng tôi', 'dsmart' ),
		'id'            => 'about_us',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="widget about-us-wg">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) ); 
	register_sidebar( array(
		'name'          => esc_html__( 'Quick link', 'dsmart' ),
		'id'            => 'quick_link',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="widget quick-link-wg box-1">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) ); 
	register_sidebar( array(
		'name'          => esc_html__( 'Quick link 2', 'dsmart' ),
		'id'            => 'quick_link2',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="widget quick-link-wg box-2">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) ); 
   
    register_sidebar( array(
		'name'          => esc_html__( 'Quick link 3', 'dsmart' ),
		'id'            => 'quick_link3',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="widget quick-link-wg box-3">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );    
    register_sidebar( array(
		'name'          => esc_html__( 'Product sidebar', 'dsmart' ),
		'id'            => 'sidebar_product',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="product-sidebar">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="sb-title">',
		'after_title'   => '</h3>',
		'class' => 'myClass'
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Image widget', 'dsmart' ),
		'id'            => 'image_widget',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="images-wg">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="sb-title">',
		'after_title'   => '</h3>',
		'class' => 'myClass'
	) );    
	register_sidebar( array(
		'name'          => esc_html__( 'News sidebar', 'dsmart' ),
		'id'            => 'news_sidebar',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="news-wg">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title"><span>',
		'after_title'   => '</span></h3>',
		'class' => 'myClass'
	) );    
	register_sidebar( array(
		'name'          => esc_html__( 'promotion news', 'dsmart' ),
		'id'            => 'promotion_news',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="news-wg">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title"><span>',
		'after_title'   => '</span></h3>',
		'class' => 'myClass'
	) );   
	register_sidebar( array(
		'name'          => esc_html__( 'menu category in single news', 'dsmart' ),
		'id'            => 'menu_category',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="news-wg">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title"><span>',
		'after_title'   => '</span></h3>',
		'class' => 'myClass'
	) );  
	register_sidebar( array(
		'name'          => esc_html__( 'news tag sidebar', 'dsmart' ),
		'id'            => 'news_tag',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="news-wg">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title"><span>',
		'after_title'   => '</span></h3>',
		'class' => 'myClass'
	) );  
	register_sidebar( array(
		'name'          => esc_html__( 'recruitment sidebar', 'dsmart' ),
		'id'            => 'recruitment_sidebar',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="recruitment-wg">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="sec-title"><span>',
		'after_title'   => '</span></h3>',
		'class' => 'myClass'
	) );    
	register_sidebar( array(
		'name'          => esc_html__( 'product tag sidebar', 'dsmart' ),
		'id'            => 'product_tag_sidebar',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="news-wg">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title"><span>',
		'after_title'   => '</span></h3>',
		'class' => 'myClass'
	) );    
	register_sidebar( array(
		'name'          => esc_html__( 'prodcuct related news sidebar', 'dsmart' ),
		'id'            => 'product_news_sidebar',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="news-wg">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title"><span>',
		'after_title'   => '</span></h3>',
		'class' => 'myClass'
	) );   
	register_sidebar( array(
		'name'          => esc_html__( 'sidebar of related product page', 'dsmart' ),
		'id'            => 'sidebar_related_product_page',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="news-wg">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title"><span>',
		'after_title'   => '</span></h3>',
		'class' => 'myClass'
	) );  
	register_sidebar( array(
		'name'          => esc_html__( 'sidebar of deal page', 'dsmart' ),
		'id'            => 'sidebar_deal_page',
		'description'   => esc_html__( 'Add widgets here.', 'dsmart' ),
		'before_widget' => '<section id="%1$s" class="news-wg deal-wg">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title"><span>',
		'after_title'   => '</span></h3>',
		'class' => 'myClass'
	) );  
	 
}
add_action( 'widgets_init', 'dsmart_widgets_init' );

add_filter( 'widget_text', 'do_shortcode' );

//hide adminbar
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
	  show_admin_bar(false);
	}
}

//filter logout
add_action( 'wp_logout', 'auto_redirect_external_after_logout');
function auto_redirect_external_after_logout(){
  wp_redirect(home_url());
  exit();
}
//filter wp mail html
add_filter('wp_mail_content_type','wpdocs_set_html_mail_content_type');
function wpdocs_set_html_mail_content_type($content_type){
	return 'text/html';
}
//funtion crop_img
function crop_img($w, $h, $url_img){
 $params = array( 'width' => $w, 'height' => $h,'crop' => true);
 return bfi_thumb($url_img, $params );
}

//funtion crop_img
function get_favicon(){
    global $arrow_option;
    $favicon  = $arrow_option['favicon'];
    echo '<link rel="icon" type="image/png" href="'.$favicon['url'].'" sizes="32x32"/>';
}

//set view
function postview_set($postID) {
    $count_key = 'postview_number';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}
function postview_get($postID){
    $count_key = 'postview_number';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "0";
    }
    return $count;
}
function setPostViews($postID) {
    $count_key = 'post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}
//update version acf
function my_acf_init() {	
	acf_update_setting('select2_version', 4);	
}
add_action('acf/init', 'my_acf_init');

//=========================================================================
function merge_querystring($url = null,$query = null,$recursive = false) {
  if($url == null)
    return false;
  if($query == null)
    return $url;
  $url_components = parse_url($url);
  if(empty($url_components['query']))
    return $url.'?'.ltrim($query,'?');
  parse_str($url_components['query'],$original_query_string);
  parse_str(parse_url($query,PHP_URL_QUERY),$merged_query_string);
  if($recursive == true)
    $merged_result = array_merge_recursive($original_query_string,$merged_query_string);
  else
    $merged_result = array_merge($original_query_string,$merged_query_string);
  return str_replace($url_components['query'],http_build_query($merged_result),$url);
}

//=========================================================================
if(!function_exists('get_page_id_by_template')):
	function get_page_id_by_template($template_name){
		$pages = get_pages(array(
		    'meta_key' => '_wp_page_template',
		    'meta_value' => $template_name
		));
		if(isset($pages[0]->ID))
			return $pages[0]->ID;
		else
			return false;
	}
endif;	

//=========================================================================
function show_product_category($terms, $parent_id, $show_icon = true){
	global $wp;
    $current_link = home_url($wp->request);
    $html = '';
    $terms_cat = get_terms( array( 'taxonomy' => $terms, 'hide_empty' => false, 'parent' => $parent_id) );
    if(!empty( $terms_cat ) && !is_wp_error( $terms_cat )):
        foreach( $terms_cat as $parent_term ) {      
            $link = get_term_link($parent_term);
            if ($link==$current_link): $class_name ="class='active"; else: $class_name=""; endif;
            if( count( get_term_children( $parent_term->term_id, $terms ) ) > 0){
            	if($class_name == ""){
            		$class_name .="class='has-sub'";
            	}else{
            		$class_name .=" has-sub'";
            	}            	
            }else{
            	if($class_name == ""){
            		$class_name .="";
            	}else{
            		$class_name .="'";
            	}
            }         
            $thumbnail_id = get_term_meta($parent_term->term_id, 'thumbnail_id', true);
        	$image = wp_get_attachment_url($thumbnail_id);
        	if($image != null):
            	$icon = "<img src='{$image}' alt='icon' class='icon'/>";
            else:
            	$icon = "";
            endif;
            $html .= '<li '. $class_name .'>';
            if($show_icon == true):
            	$html .= '<a href="'. $link .'">'. $icon .  $parent_term->name . '</a>';
            else:
            	$html .= '<a href="'. $link .'">'. $parent_term->name . '</a>';
            endif;
                if( count( get_term_children( $parent_term->term_id, $terms) ) > 0){
                    $html .= '<ul class="sub-menu">';
                    $html .= show_product_category2($terms, $parent_term->term_id, false);
                    $html .= '</ul>';
                }
            $html .= '</li>';
        }
    endif;
    return $html;
}

if(!function_exists('search_suggestion_default')){
	function search_suggestion_default(){
		$frontpage_id = get_option( 'page_on_front' );
		ob_start();  ?>
		<label class="with-radio-checkbox ship-box <?php echo (isset($_GET['fast-ship'])) ? "active" : ''; ?>">
            <input type="radio" name="fast-ship" id="fast-ship" value="<?php echo (isset($_GET['fast-ship'])) ? $_GET['fast-ship'] : 0; ?>" <?php echo (isset($_GET['fast-ship']) && $_GET['fast-ship']==1) ? 'checked' : ''; ?>>
            <span class="checkmark"></span>
            <span class="text"><?php  _e('Giao hàng nhanh trong 2h'); ?></span>
        </label>
		<?php $post_objects = get_field('search_suggestion_product', $frontpage_id);
		$output ='';
		if( $post_objects ): $count=1;
			$output .= '<div class="list">';	
		    foreach( $post_objects as $post): setup_postdata($post); 
		    	$current_id = $post->ID;
		    	$img = wp_get_attachment_url(get_post_thumbnail_id($current_id));
		   		if (!$img) {
		   			$img = get_stylesheet_directory_uri().'/images/no_img.jpg';
		   		}
		   		$url_img = crop_img(40, 40, $img);
		   		$fileName = pathinfo($url_img, PATHINFO_FILENAME);
		   		$product = wc_get_product($current_id);
		   		if($count <= 6):
			   		$output .= '<div class="item">';
			   		$output .= '<a href="'.get_permalink($current_id).'" title="'.get_the_title($current_id).'" class="thumb">';
			   		$output .= '<img src="'.$url_img.'" alt="'.$fileName.'">';
			   		$output .= '</a>';
			   		$output .= '<div class="desc">';
			   		$output .= '<h4 class="title"><a href="'. get_permalink($current_id) .'" title="'.get_the_title($current_id).'">'.get_the_title($current_id).'</a></h4>';
			   		$output .= '<p class="price">'. $product->get_price_html() .'</p>';
			   		$output .= '</div>';
			   		$output .= '</div>';	
			   	endif;		   		
	         $count++; endforeach; wp_reset_postdata(); 
	         $output .= '</div>';	
		endif;
		echo $output;
	    $search_suggestion = ob_get_contents();
	    ob_end_clean();
	    return $search_suggestion;
	}
}

// search product ajax
//=========================================================================
function ajax_search_product() {
	global $wpdb;
	$output = "";
	$s = $_REQUEST["s"];
	$tb_post_meta = $wpdb->prefix.'postmeta';
	$tb_post = $wpdb->prefix.'posts';
	$tb_term_relationships = $wpdb->prefix.'term_relationships';
	//=======================================================================
	if(!isset($_REQUEST["fast_ship"]) && $_REQUEST["fast_ship"] != '' && $_REQUEST["fast_ship"] != '0'){
		$fast_ship = $_REQUEST["fast_ship"];
		$query_ship = "AND {$tb_post_meta}.meta_key = 'fast_ship' AND {$tb_post_meta}.meta_value = {$fast_ship}";
	}else{
		$query_ship = "and meta_key = '_price'";
	}
	//=======================================================================
	$temps = explode(" ",$s);
	$query ="";
	$count = 1;
	foreach($temps as $temp){
	    if($count==1){
	        $query .= "post_title LIKE '%{$temp}%'"; 
	    }else{
	        $query .= "and post_title LIKE '%{$temp}%'";
	    }
	    $count++;
	}	
	//=======================================================================
	$data = $wpdb->get_results("
		SELECT {$tb_post}.post_title, {$tb_post}.ID, {$tb_post_meta}.meta_value 
		FROM {$tb_post} 
		INNER JOIN {$tb_post_meta} ON {$tb_post}.ID = {$tb_post_meta}.post_id 
		WHERE {$query} AND {$tb_post}.post_type LIKE 'product' AND {$tb_post}.post_status LIKE 'publish' {$query_ship} 
		GROUP BY {$tb_post}.ID LIMIT 0, 6");	
	//=======================================================================
	if($s == ''){
		$output .= search_suggestion_default();
	}else{
		if ($data) {
			$output .= '<label class="with-radio-checkbox ship-box'. ((isset($_REQUEST['fast_ship'])) ? "active" : '') .'">
            <input type="radio" name="fast-ship" id="fast-ship" value="'. ((isset($_REQUEST['fast_ship'])) ? $_REQUEST['fast_ship'] : 0) .'" '. ((isset($_REQUEST['fast_ship']) && $_REQUEST['fast_ship']==1) ? 'checked' : '') .'>
            <span class="checkmark"></span>
            <span class="text">'.  __('Giao hàng nhanh trong 2h') .'</span>
        </label>';
			$count = 0;
			$output .= '<div class="list">';	
			foreach ($data as $item) {
				$count++;
				$img = wp_get_attachment_url(get_post_thumbnail_id($item->ID));
		   		if (!$img) {
		   			$img = get_stylesheet_directory_uri().'/images/no_img.jpg';
		   		}
		   		$url_img = crop_img(40, 40, $img);
		   		$fileName = pathinfo($url_img, PATHINFO_FILENAME);
		   		$product = wc_get_product($item->ID); 
		   		$output .= '<div class="item">';
		   		$output .= '<a href="'.get_permalink($item->ID).'" title="'.$item->post_title.'" class="thumb">';
		   		$output .= '<img src="'.$url_img.'" alt="'.$fileName.'">';
		   		$output .= '</a>';
		   		$output .= '<div class="desc">';
		   		$output .= '<h4 class="title"><a href="'. get_permalink($item->ID) .'" title="'.$item->post_title.'">'.$item->post_title.'</a></h4>';
		   		$output .= '<p class="price">'. $product->get_price_html() .'</p>';
		   		$output .= '</div>';
		   		$output .= '</div>';
			}
			if ($count == 6) {
				$output .= '<div class="item readmore">';
				$output .= '<a href="'.home_url().'/?s='.$s.'&post_type=product">' .  __('Xem thêm','arrowicode') . '</a>';
				$output .= '</div>';
			}
			$output .= '</div>';
		} else {
			$output .= '<div class="no-item">';
			$output .= '<p class="empty"><a href="'.home_url().'/?s='.$s.'&post_type=product">' .  __('Bấm vào đây','arrowicode') . '</a> ' .  __('để tìm kiếm thêm bài viết về','arrowicode') .' "'. $s . '"</p>';
			$output .= '</div>';
		}
	}
	//=======================================================================
	echo $output;
	exit;
}
add_action( 'wp_ajax_ajax_search_product', 'ajax_search_product' );
add_action( 'wp_ajax_nopriv_ajax_search_product', 'ajax_search_product' );

// check cat has a parent
//=========================================================================
function category_has_parent($term){
    if (isset($term->parent) && $term->parent > 0){
        return true;
    }
    return false;
}
// check cat has a parent 2
//=========================================================================
function category_has_parent2($term_id){
    $tam_cat =  get_ancestors($term_id , 'product_cat' );				
	if(!empty($tam_cat)): 
		// if(count($tam_cat)>0){
		// 	$parent_cat_id = $tam_cat[count($tam_cat)-1];
		// }else{
		// 	$parent_cat_id = $tam_cat[count($tam_cat)];
		// }
		return true;
	else:
		return false;
	endif;
}

// check cat has a children
//=========================================================================
function has_Children($cat_id){
    $children = get_terms(
        'product_cat',
        array( 'parent' => $cat_id, 'hide_empty' => false )
    );
    if ($children){
        return true;
    }
    return false;
}

/*
* Change product-cat in URL
*/
//=========================================================================
add_filter( 'term_link', 'dsmart_product_cat_permalink', 10, 3 );
function dsmart_product_cat_permalink( $url, $term, $taxonomy ){
    switch ($taxonomy):
        case 'product_cat':
            $taxonomy_slug = 'product_cat'; //Thay bằng slug hiện tại của bạn. Mặc định là product-category
            if(strpos($url, $taxonomy_slug) === FALSE) break;
            $url = str_replace('/' . $taxonomy_slug, '', $url);
            break;
    endswitch;
    return $url;
}

// Add our custom product cat rewrite rules
//=========================================================================
function dsmart_product_category_rewrite_rules($flash = false) {
    $terms = get_terms( array(
        'taxonomy' => 'product_cat',
        'post_type' => 'product',
        'hide_empty' => false,
    ));
    if($terms && !is_wp_error($terms)){
        $siteurl = esc_url(home_url('/'));
        foreach ($terms as $term){
            $term_slug = $term->slug;
            $baseterm = str_replace($siteurl,'',get_term_link($term->term_id,'product_cat'));
            add_rewrite_rule($baseterm.'?$','index.php?product_cat='.$term_slug,'top');
            add_rewrite_rule($baseterm.'/page/([0-9]{1,})?$', 'index.php?product_cat='.$term_slug.'&paged=$matches[1]','top');
			add_rewrite_rule($baseterm.'/(?:feed/)?(feed|rdf|rss|rss2|atom)?$', 'index.php?product_cat='.$term_slug.'&feed=$matches[1]','top');
        }
    }
    if ($flash == true)
        flush_rewrite_rules(false);
}
add_action('init', 'dsmart_product_category_rewrite_rules');

/*Sửa lỗi khi tạo mới taxomony bị 404*/
//=========================================================================
add_action( 'create_term', 'dsmart_new_product_cat_edit_success', 10, 2 );
function dsmart_new_product_cat_edit_success( $term_id, $taxonomy ) {
   dsmart_product_category_rewrite_rules(true);
}

/*
* Change /product/  ... có hỗ trợ dạng %product_cat%
*/
//=========================================================================
function dsmart_remove_slug( $post_link, $post ) {
    if ( !in_array( get_post_type($post), array( 'product' ) ) || 'publish' != $post->post_status ) {
        return $post_link;
    }
    if('product' == $post->post_type){
        $post_link = str_replace( '/product/', '/san-pham/', $post_link ); //Thay cua-hang bằng slug hiện tại của bạn
    }
    return $post_link;
}
add_filter( 'post_type_link', 'dsmart_remove_slug', 10, 2 );

/*Sửa lỗi 404 sau khi đã remove slug product*/
//=========================================================================
function dsmart_woo_product_rewrite_rules($flash = false) {
    global $wp_post_types, $wpdb;
    $siteLink = esc_url(home_url('/'));
    foreach ($wp_post_types as $type=>$custom_post) {
        if($type == 'product'){
            if ($custom_post->_builtin == false) {
                $querystr = "SELECT {$wpdb->posts}.post_name, {$wpdb->posts}.ID
                            FROM {$wpdb->posts} 
                            WHERE {$wpdb->posts}.post_status = 'publish' 
                            AND {$wpdb->posts}.post_type = '{$type}'";
                $posts = $wpdb->get_results($querystr, OBJECT);
                foreach ($posts as $post) {
                    $current_slug = get_permalink($post->ID);
                    $base_product = str_replace($siteLink,'',$current_slug);
                    add_rewrite_rule($base_product.'?$', "index.php?{$custom_post->query_var}={$post->post_name}", 'top');                    
                    add_rewrite_rule($base_product.'comment-page-([0-9]{1,})/?$', 'index.php?'.$custom_post->query_var.'='.$post->post_name.'&cpage=$matches[1]', 'top');
                    add_rewrite_rule($base_product.'(?:feed/)?(feed|rdf|rss|rss2|atom)/?$', 'index.php?'.$custom_post->query_var.'='.$post->post_name.'&feed=$matches[1]','top');
                }
            }
        }
    }
    if ($flash == true)
        flush_rewrite_rules(false);
}
add_action('init', 'dsmart_woo_product_rewrite_rules');

/*Fix lỗi khi tạo sản phẩm mới bị 404*/
//=========================================================================
function dsmart_woo_new_product_post_save($post_id){
    global $wp_post_types;
    $post_type = get_post_type($post_id);
    foreach ($wp_post_types as $type=>$custom_post) {
        if ($custom_post->_builtin == false && $type == $post_type) {
            dsmart_woo_product_rewrite_rules(true);
        }
    }
}
add_action('wp_insert_post', 'dsmart_woo_new_product_post_save');

//For product
//=========================================================================
function dsmart_178112_permastruct_html( $post_type, $args ) {
    if ( $post_type === 'product' )
        add_permastruct( $post_type, "{$args->rewrite['slug']}/%$post_type%.html", $args->rewrite );
}
add_action('registered_post_type', 'dsmart_178112_permastruct_html', 10, 2 );

//=========================================================================
function get_price_product($product){
	///var_dump($product->is_type('variable'));	
	if($product->is_type('variable') == false):
		if($product->is_on_sale()){ 
			$regular_price = (float) $product->get_regular_price();
			$sale_price = (float) $product->get_sale_price();
			$saving = $regular_price - $sale_price;
			$saving_percentage = round( 100 - ( $sale_price / $regular_price * 100 ), 1 ) . '%'; 
		}else{
			$sale_price = 0;
			$regular_price = (float) $product->get_price();
			$saving_percentage = 0;
			$saving = 0;
		}
		$variable_price = 0;
		$variation_id = 0;
	else:
		$sale_price = 0;
		$regular_price = 0;
		$saving_percentage = 0;
		$saving = 0;
	    $variation_id = 0;
	    $variable_price = 0;
	    foreach($product->get_available_variations() as $variation_values ){
	        foreach($variation_values['attributes'] as $key => $attribute_value ){
	            $attribute_name = str_replace( 'attribute_', '', $key );
	            $default_value = $product->get_variation_default_attribute($attribute_name);
	            if( $default_value == $attribute_value ){
	                $is_default_variation = true;
	            } else {
	                $is_default_variation = false;
	                break; // Stop this loop to start next main lopp
	            }
	            if( $is_default_variation ){
	                $variation_id = $variation_values['variation_id'];
	                $default_variation = wc_get_product($variation_id);
	                // Get The active price
	                $regular_price = (float) $default_variation->get_regular_price();
	                $sale_price = (float) $default_variation->get_sale_price();
	                if($sale_price > 0){
	                    $saving = $regular_price - $sale_price;
	                    $saving_percentage = round( 100 - ( $sale_price / $regular_price * 100 ), 1 ) . '%'; 
	                }else{
	                    $sale_price = 0;
	                    $saving_percentage = 0;
	                    $saving = 0;
	                }
	                $variable_price = $default_variation->get_price(); 
	                 // $variable_price = $default_variation->price; 
	                break; // Stop the main loop
	            }
	        }            
	    }
	endif;
	$array_price = array('regular_price' => $regular_price, 'sale_price' => $sale_price, 'saving_percentage' => $saving_percentage, 'saving' => $saving, 'variable_price' => $variable_price, 'variation_id'=> $variation_id);
	return $array_price;
}

//search by title
//=========================================================================
function search_by_title_only( $search, $wp_query){
	global $wpdb;
    if(empty($search)) {
        return $search; // skip processing - no search term in query
    }
    $q = $wp_query->query_vars;
    $n = !empty($q['exact']) ? '' : '%';
    $search =
    $searchand = '';
    foreach ((array)$q['search_terms'] as $term) {
         $term = esc_sql($wpdb->esc_like($term));
        $search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
        $searchand = ' AND ';
    }
    if (!empty($search)) {
        $search = " AND ({$search}) ";
        if (!is_user_logged_in())
            $search .= " AND ($wpdb->posts.post_password = '') ";
    }
    return $search;
}
add_filter( 'posts_search', 'search_by_title_only', 500, 2 );

//=========================================================================
function dsmart_content_product($product_id){ 
	global $arrow_option; 
	$arrowicode = $arrow_option['arrowicode'];
	$product = new WC_Product($product_id);

	if(has_post_thumbnail($product_id)):
	    $tam =  wp_get_attachment_url(get_post_thumbnail_id($product_id));
	    if($tam == false){
	        $url_img = get_stylesheet_directory_uri().'/images/no_img.jpg';
	    }else{
	        $url_img = $tam;
	    }
	else:
	    $url_img = get_stylesheet_directory_uri().'/images/no_img.jpg';
	endif;
	$fileName = pathinfo($url_img, PATHINFO_FILENAME);
	$array_price = get_price_product($product);
	if($array_price['variation_id'] > 0){
	   $product_stock_status = '';
	    foreach($product->get_available_variations() as $variation_values ){
	        foreach($variation_values['attributes'] as $key => $attribute_value ){
	            $attribute_name = str_replace( 'attribute_', '', $key );
	            $default_value = $product->get_variation_default_attribute($attribute_name);
	            if( $default_value == $attribute_value ){
	                $is_default_variation = true;
	            } else {
	                $is_default_variation = false;
	                break; // Stop this loop to start next main lopp
	            }
	            if( $is_default_variation ){
	                $variation_id = $variation_values['variation_id'];
	                $default_variation = wc_get_product($variation_id);
	                $product_stock_status = $default_variation->get_stock_status();
	                break; // Stop the main loop
	            }
	        }            
	    }
	}else{
	    $product_stock_status  = $product->get_stock_status();
	}

	$count_rating = $product->get_rating_count();
	$avg_rating = $product->get_average_rating();
	$fashShip_id = get_page_id_by_template('template/template-fast-ship.php'); 
	$sale_status = trim(get_field('sale_status'));
	switch ($sale_status) {
	    case 'Đặt Trước':
	        $class_sale = 'sky-blue';
	        break;
	    case 'Đặt hàng':
	        $class_sale = 'sky-blue';
	        break;    
	    case 'Mới về':
	        $class_sale = 'blue';
	        break;
	    case 'Sẵn hàng':
	        $class_sale = 'green';
	        break;    
	    default:
	        $class_sale = 'green';
	        break;
	}
	 ob_start();   ?>
	<div <?php wc_product_class( "item", $product ); ?>>
		<a href="<?php echo get_permalink($product_id); ?>" class="thumb">
	        <img src="<?php echo crop_img(236, 236, $url_img); ?>" alt="<?php echo $fileName; ?>">
	        <?php if(get_field('installment_percent',$product_id) !== NULL && get_field('installment_percent',$product_id) !== ''): ?>
	            <span class="installment-percent"><?php the_field('installment_percent',$product_id);  ?></span>
	        <?php endif;  ?>
	    </a>
	    <div class="list-code">
	        <?php 
	        if($array_price['sale_price'] > 0): ?>
	            <span class="percent">- <?php echo $array_price['saving_percentage'];  ?></span>
	        <?php endif;  ?>
	        <?php if(get_field('sale_status', $product_id) !== NULL && get_field('sale_status', $product_id) !== ''): ?>
	            <span class="sale-status <?php echo $class_sale; ?>"><?php the_field('sale_status', $product_id);  ?></span>
	        <?php endif;  ?>
	    </div>
	    <?php echo get_field('fast_ship',$product_id)? '<a href="'. get_page_link($fashShip_id) .'" class="fast-ship" data-toggle="tooltip" title="sản phẩm này hỗ trợ giao nhanh trong 2h"><img src="'. get_stylesheet_directory_uri() .'/images/2h.png" alt=""></a>' : ''; ?>
	    <div class="desc">
	        <h3 class="title">
	            <?php echo  '<a href="'. get_permalink($product_id) .'">'. get_the_title($product_id); ?></a>
	        </h3>
	        <?php 
	        if(get_field('sale_status',$product_id) === 'Ngừng kinh doanh' || get_field('installment_percent',$product_id) === 'Hết hàng' || $product_stock_status ==='outofstock'):
	            if(get_field('sale_status',$product_id) == 'Ngừng kinh doanh'): ?>
	                <div class="price"><?php echo __("Ngừng kinh doanh", $arrowicode); ?></div>  
	            <?php else: ?> 
	                <div class="price"><?php echo __("Hết hàng", $arrowicode); ?></div>      
	            <?php endif; 
	        elseif($product_stock_status == 'onbackorder'): ?>
	            <div class="price"><?php echo __("Hàng sắp về", $arrowicode); ?></div>    
	        <?php elseif((get_field('sale_status',$product_id) != 'Ngừng kinh doanh' && get_field('installment_percent',$product_id) != 'Hết hàng')):
	            if($array_price['sale_price'] > 0): ?>
	                <div class="price">
	                    <ins><?php echo wc_price($array_price['sale_price']); ?></ins>
	                    <del><?php echo wc_price($array_price['regular_price']); ?></del>                   
	                </div>  
	            <?php else: ?>
	                <div class="price"><?php echo wc_price($array_price['regular_price']); ?></div>    
	            <?php endif; 
	        endif;   
	        if(get_field('summary_of_promotional_information',$product_id)!=null): 
	            $prom = preg_replace('/<p\b[^>]*>(.*?)<\/p>/i', '\1', get_field('summary_of_promotional_information',$product_id)); ?>
	            <p class="prom-info"><?php echo $prom; ?></p>
	        <?php endif;  
	        if ($count_rating > 0): ?>
	            <div class="product-rating">
	                <div class="star-rating">
	                    <span style="width:<?php echo round($avg_rating/5*100,2);?>%">Rated <strong class="rating"><?php echo round($avg_rating,2) .'/5';?></strong> out of 5 based on 
	                    <span class="rating"></span>
	                </div>      
	            </div> 
	        <?php else: ?>
	            <p class="no-rating">chưa có đánh giá</p> 
	        <?php endif;  ?>   
	    </div>
	</div>
<?php
	$content_item = ob_get_contents();
    ob_end_clean();
    return $content_item;
}


//=========================================================================
function dsmart_ucfirst($string){
    return mb_strtoupper(mb_substr($string, 0, 1)).mb_strtolower(mb_substr($string, 1));
}

//=========================================================================
if (!(is_admin() )) {
	function defer_parsing_of_js ( $url ) {
	if ( FALSE === strpos( $url, '.js' ) ) return $url;
		if ( strpos( $url, 'jquery.js' ) ) return $url;
		// return "$url' defer ";
			return "$url' defer onload='";
	}
	add_filter( 'clean_url', 'defer_parsing_of_js', 11, 1 );
}

//=========================================================================
function getNumber($price){
	return number_format($price,'0',',','.');
}

//installment info
//=========================================================================
if ( ! function_exists( 'installment_info' ) ) :
	function installment_info() {
		global $wpdb, $arrow_option;
		$arrowicode = $arrow_option['arrowicode'];
		$admin_email = $arrow_option['email'];
		// $admin_email = 'nhantringuyen893109@gmail.com';
		$client_name = $_REQUEST['client_name'];
		$client_phone = $_REQUEST['client_phone'];
		$client_email = $_REQUEST['client_email'];

		$product_price = $_REQUEST['product_price'];
		$installment_money = $_REQUEST['installment_money'];
		$pay_per_month = $_REQUEST['pay_per_month'];
		$payment_more = $_REQUEST['payment_more'];

		$bank_name = $_REQUEST['bank_name'];
		$credit_type = $_REQUEST['credit_type'];
		$number_month = $_REQUEST['number_month'];

		$product_name = $_REQUEST['product_name'];
		$product_id = $_REQUEST['product_id'];
		$current_id = $_REQUEST['current_id'];		
		
		
		$info = get_field('installment_info', $current_id);

		$title1 = __('Chúc mừng anh/chị', $arrowicode). ' ' . $client_name .' '. __('đã đăng ký thành công gói trả góp của',$arrowicode) .' '. get_option( 'blogname' );
	    $body1 = '<h3>Thông tin trả góp</h3>
			<ul>
				<li>Sản phẩm trả góp '. $product_name .'</li>
				<li>Giá gốc của sản phẩm '. $product_price .'</li>
				<li>Trả góp thông qua ngân hàng '. $bank_name .'</li>
				<li>Trả góp thông qua loại thẻ tín dụng '. $credit_type .'</li>
				<li>Tổng số tiền trả góp phải trả: '. $installment_money .' qua '. $number_month .'</li>
				<li>Số tiền phải trả mỗi tháng: '. $pay_per_month .'</li>
				<li>Số tiền phải trả thêm so với giá thường: '. $payment_more .'</li>
			</ul>
			<p>'. $info .'</p>
	    ';
	    $title2 = __('Có thành viên đăng ký tư vấn trả góp trên website của bạn', $arrowicode) .' '.get_option( 'blogname' );
	    $body2 = '<h3>Thông tin khách hàng</h3>
	    <ul>
	    	<li>Họ tên khách hàng: '. $client_name .'</li>
	    	<li>Email khách hàng: '. $client_email .'</li>
	    	<li>Số điện thoại khách hàng: '. $client_phone .'</li>
	    	<li>Sản phẩm yêu cầu trả góp: <a href="'. get_permalink($product_id) .'">'. $product_name .'</a></li>
	    	<li>Giá gốc của sản phẩm '. $product_price .'</li>
	    	<li>Ngân hàng trả góp: '. $bank_name .'</li>
	    	<li>Trả góp thông qua loại thẻ tín dụng '. $credit_type .'</li>
	    	<li>Số tháng trả góp: '. $number_month .'</li>
	    	<li>Số tiền trả góp mỗi tháng: '. $pay_per_month .'</li>
	    	<li>Tổng số tiền trả góp: '. $installment_money .'</li>
	    	<li>Số tiền phải trả thêm so với giá thường: '. $payment_more .'</li>
	    </ul>';
	    wp_mail($client_email, $title1, $body1);
	    wp_mail($admin_email, $title2, $body2);
	    exit();      		
	}
	add_action( 'wp_ajax_installment_info', 'installment_info' );
	add_action( 'wp_ajax_nopriv_installment_info', 'installment_info' );
endif;	

//payment info
//=========================================================================
if ( ! function_exists( 'payment_info' ) ) :
	function payment_info() {
		global $wpdb, $arrow_option;
		$arrowicode = $arrow_option['arrowicode'];
		$admin_email = $arrow_option['email'];
		// $admin_email = 'nhantringuyen893109@gmail.com';
		$client_name = $_REQUEST['client_name'];
		$client_phone = $_REQUEST['client_phone'];
		$client_email = $_REQUEST['client_email'];

		$product_price = $_REQUEST['product_price'];
		$installment_money = $_REQUEST['installment_money'];
		$payment_more = $_REQUEST['payment_more'];

		$bank_name = $_REQUEST['bank_name'];
		$credit_type = $_REQUEST['credit_type'];

		$product_name = $_REQUEST['product_name'];
		$product_id = $_REQUEST['product_id'];
		$current_id = $_REQUEST['current_id'];		
		
		
		$info = get_field('installment_info', $current_id);

		$title1 = __('Chúc mừng anh/chị', $arrowicode). ' ' . $client_name .' '. __('đã đăng ký thành công thanh toán qua thẻ tín dụng của',$arrowicode) .' '. get_option( 'blogname' );
	    $body1 = '<h3>Thông tin trả góp</h3>
			<ul>
				<li>Sản phẩm cần thanh toán '. $product_name .'</li>
				<li>Giá gốc của sản phẩm '. $product_price .'</li>
				<li>Thanh toán thông qua ngân hàng '. $bank_name .'</li>
				<li>Thanh toán thông qua loại thẻ tín dụng '. $credit_type .'</li>
				<li>Tổng số tiền thanh toán phải trả: '. $installment_money .'</li>
				<li>Số tiền phải trả thêm so với giá thường: '. $payment_more .'</li>
			</ul>
			<p>'. $info .'</p>
	    ';
	    $title2 = __('Có thành viên đăng ký thanh toán qua thẻ tín dụng trên website của bạn', $arrowicode) .' '.get_option( 'blogname' );
	    $body2 = '<h3>Thông tin khách hàng</h3>
	    <ul>
	    	<li>Họ tên khách hàng: '. $client_name .'</li>
	    	<li>Email khách hàng: '. $client_email .'</li>
	    	<li>Số điện thoại khách hàng: '. $client_phone .'</li>
	    	<li>Sản phẩm yêu cầu thanh toán: <a href="'. get_permalink($product_id) .'">'. $product_name .'</a></li>
	    	<li>Giá gốc của sản phẩm '. $product_price .'</li>
	    	<li>Ngân hàng thanh toán: '. $bank_name .'</li>
	    	<li>Thanh toán thông qua loại thẻ tín dụng '. $credit_type .'</li>
	    	<li>Tổng số tiền thanh toán: '. $installment_money .'</li>
	    	<li>Số tiền phải trả thêm so với giá thường: '. $payment_more .'</li>
	    </ul>';
	    wp_mail($client_email, $title1, $body1);
	    wp_mail($admin_email, $title2, $body2);
	    exit();      		
	}
	add_action( 'wp_ajax_payment_info', 'payment_info' );
	add_action( 'wp_ajax_nopriv_payment_info', 'payment_info' );
endif;	

//=========================================================================
if ( ! function_exists( 'provider_installment' ) ) :
	function provider_installment() {
		global $wpdb, $arrow_option;
		$arrowicode = $arrow_option['arrowicode'];
		$admin_email = $arrow_option['email'];
		// $admin_email = 'nhantringuyen893109@gmail.com';

		$client_name = $_REQUEST['client_name'];
		$client_phone = $_REQUEST['client_phone'];
		$client_email = $_REQUEST['client_email'];

		$product_id = $_REQUEST['product_id'];
		$product_name = $_REQUEST['product_name'];
		$product_price = $_REQUEST['product_price'];
		$current_id = $_REQUEST['current_id'];		

		$company = $_REQUEST['company'];
		$pre_pay_money = $_REQUEST['pre_pay_money'];
		$reduce_money = $_REQUEST['reduce_money'];
		$pay_per_month = $_REQUEST['pay_per_month'];
		$number_month = $_REQUEST['number_month'];
		$installment_money = $_REQUEST['installment_money'];
		$pay_more = $_REQUEST['pay_more'];
		$info = get_field('note', $current_id);

		$title1 = __('Chúc mừng anh/chị', $arrowicode). ' ' . $client_name .' '. __('đã đăng ký thành công gói trả góp của',$arrowicode) .' '. get_option( 'blogname' );
	    $body1 = '<h3>Thông tin trả góp</h3>
			<ul>
				<li>Sản phẩm trả góp: '. $product_name .'</li>
				<li>Giá gốc của sản phẩm: '. $product_price .'</li>
				<li>Trả góp thông qua công ty tài chính: '. $company .'</li>
				<li>Số tiền đã trả: '. $pre_pay_money .'</li>
				<li>Số tiền đã vay: '. $reduce_money .'</li>
				<li>Tổng số tiền sau trả góp phải trả: '. $installment_money .' qua '. $number_month .'</li>
				<li>Số tiền phải trả mỗi tháng: '. $pay_per_month .'</li>
				<li>Số tiền phải trả thêm so với giá gốc: '. $pay_more .'</li>
			</ul>
			<p>'. $info .'</p>
	    ';
	    $title2 = __('Có thành viên đăng ký tư vấn trả góp trên website của bạn', $arrowicode) .' '.get_option( 'blogname' );
	    $body2 = '<h3>Thông tin khách hàng</h3>
	    <ul>
	    	<li>Họ tên khách hàng: '. $client_name .'</li>
	    	<li>Email khách hàng: '. $client_email .'</li>
	    	<li>Số điện thoại khách hàng: '. $client_phone .'</li>
	    	<li>Sản phẩm yêu cầu trả góp: <a href="'. get_permalink($product_id) .'">'. $product_name .'</a></li>
	    	<li>Giá gốc của sản phẩm: '. $product_price .'</li>
	    	<li>Trả góp thông qua công ty tài chính: '. $company .'</li>
	    	<li>Số tiền đã trả: '. $pre_pay_money .'</li>
			<li>Số tiền đã vay: '. $reduce_money .'</li>
	    	<li>Số tháng trả góp: '. $number_month .'</li>
	    	<li>Số tiền trả góp mỗi tháng: '. $pay_per_month .'</li>
	    	<li>Tổng số tiền sau trả góp: '. $installment_money .'</li>
	    	<li>Số tiền phải trả thêm so với giá gốc: '. $pay_more .'</li>
	    </ul>';
	    wp_mail($client_email, $title1, $body1);
	    wp_mail($admin_email, $title2, $body2);
	    exit();      		
	}
	add_action( 'wp_ajax_provider_installment', 'provider_installment' );
	add_action( 'wp_ajax_nopriv_provider_installment', 'provider_installment' );
endif;	

// 
//=========================================================================
if ( ! function_exists( 'dsmart_add_js_css' ) ) :
add_action( 'wp_enqueue_scripts', 'dsmart_add_js_css', 99 );
function dsmart_add_js_css() {
	//remove generator meta tag
	//remove_action( 'wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );

	//first check that woo exists to prevent fatal errors
	if (is_page_template('template/landing_page.php') || is_front_page()) {
		//equeue scripts and styles
			wp_enqueue_style( 'woocommerce_frontend_styles' );
			wp_enqueue_style( 'woocommerce_fancybox_styles' );
			wp_enqueue_style( 'woocommerce_chosen_styles' );
			wp_enqueue_style( 'woocommerce_prettyPhoto_css' );
			wp_enqueue_script( 'wc_price_slider' );
			wp_enqueue_script( 'wc-single-product' );
			wp_enqueue_script( 'wc-add-to-cart' );
			wp_enqueue_script( 'wc-cart-fragments' );
			wp_enqueue_script( 'wc-checkout' );
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			wp_enqueue_script( 'wc-single-product' );
			wp_enqueue_script( 'wc-cart' );
			wp_enqueue_script( 'wc-chosen' );
			wp_enqueue_script( 'woocommerce' );
			wp_enqueue_script( 'prettyPhoto' );
			wp_enqueue_script( 'prettyPhoto-init' );
			wp_enqueue_script( 'jquery-blockui' );
			wp_enqueue_script( 'jquery-placeholder' );
			wp_enqueue_script( 'fancybox' );
			wp_enqueue_script( 'jqueryui' );
	}
}
endif;

//=========================================================================
if ( ! function_exists( 'rebuild_date' ) ) :
function rebuild_date( $format, $time = 0 ){
    if ( ! $time ) $time = time();

	$lang = array();
	$lang['sun'] = 'CN';
	$lang['mon'] = 'T2';
	$lang['tue'] = 'T3';
	$lang['wed'] = 'T4';
	$lang['thu'] = 'T5';
	$lang['fri'] = 'T6';
	$lang['sat'] = 'T7';
	$lang['sunday'] = 'Chủ nhật';
	$lang['monday'] = 'Thứ hai';
	$lang['tuesday'] = 'Thứ ba';
	$lang['wednesday'] = 'Thứ tư';
	$lang['thursday'] = 'Thứ năm';
	$lang['friday'] = 'Thứ sáu';
	$lang['saturday'] = 'Thứ bảy';
	$lang['january'] = 'Tháng Một';
	$lang['february'] = 'Tháng Hai';
	$lang['march'] = 'Tháng Ba';
	$lang['april'] = 'Tháng Tư';
	$lang['may'] = 'Tháng Năm';
	$lang['june'] = 'Tháng Sáu';
	$lang['july'] = 'Tháng Bảy';
	$lang['august'] = 'Tháng Tám';
	$lang['september'] = 'Tháng Chín';
	$lang['october'] = 'Tháng Mười';
	$lang['november'] = 'Tháng M. một';
	$lang['december'] = 'Tháng M. hai';
	$lang['jan'] = 'T01';
	$lang['feb'] = 'T02';
	$lang['mar'] = 'T03';
	$lang['apr'] = 'T04';
	$lang['may2'] = 'T05';
	$lang['jun'] = 'T06';
	$lang['jul'] = 'T07';
	$lang['aug'] = 'T08';
	$lang['sep'] = 'T09';
	$lang['oct'] = 'T10';
	$lang['nov'] = 'T11';
	$lang['dec'] = 'T12';

    $format = str_replace( "r", "D, d M Y H:i:s O", $format );
    $format = str_replace( array( "D", "M" ), array( "[D]", "[M]" ), $format );
    $return = date( $format, $time );

    $replaces = array(
        '/\[Sun\](\W|$)/' => $lang['sun'] . "$1",
        '/\[Mon\](\W|$)/' => $lang['mon'] . "$1",
        '/\[Tue\](\W|$)/' => $lang['tue'] . "$1",
        '/\[Wed\](\W|$)/' => $lang['wed'] . "$1",
        '/\[Thu\](\W|$)/' => $lang['thu'] . "$1",
        '/\[Fri\](\W|$)/' => $lang['fri'] . "$1",
        '/\[Sat\](\W|$)/' => $lang['sat'] . "$1",
        '/\[Jan\](\W|$)/' => $lang['jan'] . "$1",
        '/\[Feb\](\W|$)/' => $lang['feb'] . "$1",
        '/\[Mar\](\W|$)/' => $lang['mar'] . "$1",
        '/\[Apr\](\W|$)/' => $lang['apr'] . "$1",
        '/\[May\](\W|$)/' => $lang['may2'] . "$1",
        '/\[Jun\](\W|$)/' => $lang['jun'] . "$1",
        '/\[Jul\](\W|$)/' => $lang['jul'] . "$1",
        '/\[Aug\](\W|$)/' => $lang['aug'] . "$1",
        '/\[Sep\](\W|$)/' => $lang['sep'] . "$1",
        '/\[Oct\](\W|$)/' => $lang['oct'] . "$1",
        '/\[Nov\](\W|$)/' => $lang['nov'] . "$1",
        '/\[Dec\](\W|$)/' => $lang['dec'] . "$1",
        '/Sunday(\W|$)/' => $lang['sunday'] . "$1",
        '/Monday(\W|$)/' => $lang['monday'] . "$1",
        '/Tuesday(\W|$)/' => $lang['tuesday'] . "$1",
        '/Wednesday(\W|$)/' => $lang['wednesday'] . "$1",
        '/Thursday(\W|$)/' => $lang['thursday'] . "$1",
        '/Friday(\W|$)/' => $lang['friday'] . "$1",
        '/Saturday(\W|$)/' => $lang['saturday'] . "$1",
        '/January(\W|$)/' => $lang['january'] . "$1",
        '/February(\W|$)/' => $lang['february'] . "$1",
        '/March(\W|$)/' => $lang['march'] . "$1",
        '/April(\W|$)/' => $lang['april'] . "$1",
        '/May(\W|$)/' => $lang['may'] . "$1",
        '/June(\W|$)/' => $lang['june'] . "$1",
        '/July(\W|$)/' => $lang['july'] . "$1",
        '/August(\W|$)/' => $lang['august'] . "$1",
        '/September(\W|$)/' => $lang['september'] . "$1",
        '/October(\W|$)/' => $lang['october'] . "$1",
        '/November(\W|$)/' => $lang['november'] . "$1",
        '/December(\W|$)/' => $lang['december'] . "$1" );

    return preg_replace( array_keys( $replaces ), array_values( $replaces ), $return );
}
endif;

/** Show deal category
 *
 * @param $terms string a string of taxonomy
 * @param $parent_id int 
 * @param $show_icon boolean
 * @return string 
 */
if ( ! function_exists( 'show_product_deal' ) ) :
	function show_deal_category($terms, $parent_id, $show_icon = true){
		global $wp;
	    $current_link = home_url($wp->request);
	    $html = '';
	    $terms_cat = get_terms( array( 'taxonomy' => $terms, 'hide_empty' => false, 'parent' => $parent_id) );
	    if(!empty( $terms_cat ) && !is_wp_error( $terms_cat )):
	        foreach( $terms_cat as $parent_term ) {      
	            $link = get_term_link($parent_term);
	            if ($link==$current_link): $class_name ="class='active"; else: $class_name=""; endif;
	            if( count( get_term_children( $parent_term->term_id, $terms ) ) > 0){
	            	if($class_name == ""){
	            		$class_name .="class='has-sub'";
	            	}else{
	            		$class_name .=" has-sub'";
	            	}          
	            }else{
	            	if($class_name == ""){
	            		$class_name .="";
	            	}else{
	            		$class_name .="'";
	            	}
	            }            
	        	if(get_field('icon',$terms.'_'.$parent_term->term_id) != null):
	            	$icon = get_field('icon',$terms.'_'.$parent_term->term_id);
	            else:
	            	$icon = "";
	            endif;
	            $html .= '<li '. $class_name .'>';
	            if($show_icon == true):
	            	$html .= '<a href="'. $link .'">'. $icon .  $parent_term->name . '</a>';
	            else:
	            	$html .= '<a href="'. $link .'">'. $parent_term->name . '</a>';
	            endif;
	                if( count( get_term_children( $parent_term->term_id, $terms) ) > 0){
	                    $html .= '<ul class="sub-menu">';
	                    $html .= show_deal_category($terms, $parent_term->term_id, false);
	                    $html .= '</ul>';
	                }
	            $html .= '</li>';
	        }
	    endif;
	    return $html;
	}
endif;

function webp_upload_mimes( $existing_mimes ) {
	// add webp to the list of mime types
	$existing_mimes['webp'] = 'image/webp';

	// return the array back to the function with our added mime type
	return $existing_mimes;
}
add_filter( 'mime_types', 'webp_upload_mimes' );

function get_price($number){
	$number = round(intval($number)/10)*10;
	if(($number / 1000000000) > 1):  
    	$number = $number / 1000000000; 
    	$unit = "tỷ";
    elseif(($number / 1000000) > 1):  
    	$number = $number / 1000000; 
    	$unit =  "triệu";
    elseif(($number / 1000) > 1):  
    	$number = $number / 1000; 
    	$unit =  "ngàn";	
    else:  
    	$number = getNumber(intval($number)); 		
    	$unit =  "";	
    endif;
    return ['number'=> $number, 'unit' => $unit];
}
function get_text_filter_price($minprice, $maxprice){
	if($minprice == null || $minprice == ''):
		$minprice = 0;
	endif;
	if($maxprice == null || $maxprice == ''):
		$maxprice = 0;
	endif;
	if($minprice == 0 && $maxprice == 0):
		$text = '';
	elseif($minprice == 0):
		$text = __('Dưới') .' '. get_price($maxprice)['number'] .' '. get_price($maxprice)['unit'] ; 
	elseif($maxprice == 0):
		$text = __('Trên') .' '. get_price($minprice)['number'] .' '. get_price($minprice)['unit'] ; 
	else:
		$max_unit = get_price($maxprice)['unit'];
		$min_unit = get_price($minprice)['unit'];
		$max_num = get_price($maxprice)['number'];
		$min_num = get_price($minprice)['number']; 
		if( $max_unit == $min_unit ):
			$text = __("Từ") .' '. $min_num .' - '. $max_num .' '. $max_unit;
		else:
			$text = __("Từ") .' '. $min_num .' '. $min_unit .' - '. $max_num .' '. $max_unit;
		endif;
	endif;	
	return $text;
}