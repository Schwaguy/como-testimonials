<?php

/*
Plugin Name: Como Testimonials
Plugin URI: http://www.comocreative.com/
Version: 1.0.3
Author: Como Creative LLC
Description: Plugin designed to work with the Bootstrap 4 to enable and easy Testimonial Integration. 
Shortcode example: [comoTestimonials featured=TRUE/FALSE limit=NUMBER template=TEMPLATE NAME orderby=DATE/TITLE/MENU_ORDER order=ASC/DESC].  
Custom templates can be created in your theme in a folder named "como-testimonial" 
*/

defined('ABSPATH') or die('No Hackers!');
/* Include plugin updater. */
require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/updater.php' );

/* ##################### Define Testimonial Post Type ##################### */
if ( ! function_exists('testimonial_post_type') ) {
	function testimonial_post_type() {
		$labels = array(
			'name'                  => _x('Testimonials', 'Post Type General Name', 'text_domain' ),
			'singular_name'         => _x('Testimonial', 'Post Type Singular Name', 'text_domain' ),
			'menu_name'             => __('Testimonials', 'text_domain' ),
			'name_admin_bar'        => __('Testimonial', 'text_domain' ),
			'archives'              => __('Testimonial Archives', 'text_domain' ),
			'parent_item_colon'     => __('Parent Testimonial:', 'text_domain' ),
			'all_items'             => __('All Testimonials', 'text_domain' ),
			'add_new_item'          => __('Add New Testimonial', 'text_domain' ),
			'add_new'               => __('Add New', 'text_domain' ),
			'new_item'              => __('New Testimonial', 'text_domain' ),
			'edit_item'             => __('Edit Testimonial', 'text_domain' ),
			'update_item'           => __('Update Testimonial', 'text_domain' ),
			'view_item'             => __('View Testimonial', 'text_domain' ),
			'search_items'          => __('Search Testimonials', 'text_domain' ),
			'not_found'             => __('Not found', 'text_domain' ),
			'not_found_in_trash'    => __('Not found in Trash', 'text_domain' ),
			'featured_image'        => __('Testimonial Image', 'text_domain' ),
			'set_featured_image'    => __('Set Testimonial image', 'text_domain' ),
			'remove_featured_image' => __('Remove Testimonial image', 'text_domain' ),
			'use_featured_image'    => __('Use as Testimonial image', 'text_domain' ),
			'insert_into_item'      => __('Insert into Testimonials', 'text_domain' ),
			'uploaded_to_this_item' => __('Uploaded to this Testimonial', 'text_domain' ),
			'items_list'            => __('Testimonial list', 'text_domain' ),
			'items_list_navigation' => __('Testimonial list navigation', 'text_domain' ),
			'filter_items_list'     => __('Filter Testimonials list', 'text_domain' ),
		);
		$args = array(
			'label'                 => __('testimonial', 'text_domain' ),
			'description'           => __('Testimonials to be displayed on website', 'text_domain' ),
			'labels'                => $labels,
			'supports'              => array('title','editor','excerpt','thumbnail','page-attributes'),
			'taxonomies'			=> array(),
			'hierarchical'          => true,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-format-quote',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,		
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);
		register_post_type( 'testimonial', $args );
	}
	add_action( 'init', 'testimonial_post_type', 0 );
}

// Testimonial Taxonomy 
add_action( 'init', 'create_testimonial_tax', 0 );
function create_testimonial_tax() {
	$labels = array(
		'name'              => _x( 'Testimonial Type', 'taxonomy general name' ),
		'singular_name'     => _x( 'Testimonial Type', 'taxonomy singular name' ),
		'search_items'      => __( 'Search Testimonial Types' ),
		'all_items'         => __( 'All Testimonial Types' ),
		'parent_item'       => __( 'Parent Testimonial Type' ),
		'parent_item_colon' => __( 'Parent Testimonial Type:' ),
		'edit_item'         => __( 'Edit Testimonial Type' ),
		'update_item'       => __( 'Update Testimonial Type' ),
		'add_new_item'      => __( 'Add New Testimonial Type' ),
		'new_item_name'     => __( 'New Testimonial Type' ),
		'menu_name'         => __( 'Testimonial Type' ),
	);
	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'testimonial-type' ),
	);
	register_taxonomy('testimonial-type', array('testimonial'), $args );
}

add_action( 'restrict_manage_posts', 'testimonials_restrict_manage_posts');
function testimonials_restrict_manage_posts() {
	global $typenow;
	$taxonomy = 'testimonial-type';
	if( $typenow != "page" && $typenow != "post" ){
		$filters = array($taxonomy);
		foreach ($filters as $tax_slug) {
			$tax_obj = get_taxonomy($tax_slug);
			$tax_name = $tax_obj->labels->name;
			$terms = get_terms($tax_slug);
			echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
			echo "<option value=''>Show All ". $tax_name ."</option>";
			foreach ($terms as $term) { echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>'; }
			echo "</select>";
		}
	}
}

/* ##################### Testimonial Info Meta Box ##################### */

function comoTestimonial_custom_meta() {
    add_meta_box('comoTestimonial_meta', __('Additional Testimonial Info','comoTestimonial-textdomain'),'comoTestimonial_meta_callback','Testimonial','normal','high');
}
add_action( 'add_meta_boxes', 'comoTestimonial_custom_meta' );

function comoTestimonial_meta_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'comoTestimonial_nonce' );
    $comotest_stored_meta = get_post_meta( $post->ID );
    ?>
 
    <p><label for="comoTestimonial-name" class="comometa-row-title"><?php _e( 'Source Name: ', 'comoTestimonial-textdomain' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comoTestimonial-name" id="comoTestimonial-name" value="<?php if ( isset ( $comotest_stored_meta['comoTestimonial-name'] ) ) echo $comotest_stored_meta['comoTestimonial-name'][0]; ?>" /></span></p>
    
    <p><label for="comoTestimonial-company" class="comometa-row-title"><?php _e( 'Source Company: ', 'comoTestimonial-textdomain' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comoTestimonial-company" id="comoTestimonial-company" value="<?php if ( isset ( $comotest_stored_meta['comoTestimonial-company'] ) ) echo $comotest_stored_meta['comoTestimonial-company'][0]; ?>" /></span></p>
    
    <p><label for="comoTestimonial-location" class="comometa-row-title"><?php _e( 'Source Location: ', 'comoTestimonial-textdomain' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comoTestimonial-location" id="comoTestimonial-location" value="<?php if ( isset ( $comotest_stored_meta['comoTestimonial-location'] ) ) echo $comotest_stored_meta['comoTestimonial-location'][0]; ?>" /></span></p>
    
    <p><label for="comoTestimonial-email" class="comometa-row-title"><?php _e( 'Source Email: ', 'comoTestimonial-textdomain' )?></label>
  	<span class="comometa-row-content"><input type="text" name="comoTestimonial-email" id="comoTestimonial-email" value="<?php if ( isset ( $comotest_stored_meta['comoTestimonial-email'] ) ) echo $comotest_stored_meta['comoTestimonial-email'][0]; ?>" /></span></p>
    
    <p><label for="comoTestimonial-date" class="comometa-row-title"><?php _e( 'Review Date', 'comoTestimonial-textdomain' )?></label>
    <span class="comometa-row-content"><input type="text" name="comoTestimonial-date" id="comoTestimonial-date" class="datepicker" value="<?php if ( isset ( $comotest_stored_meta['comoTestimonial-date'] ) ) echo $comotest_stored_meta['comoTestimonial-date'][0]; ?>" /></span></p>
    
    <p><label for="comoTestimonial-rating" class="comometa-row-title"><?php _e( 'Star Rating: ', 'comoTestimonial-textdomain' )?></label>
  	<span class="comometa-row-content"><input type="number" min="0" max="5" name="comoTestimonial-rating" id="comoTestimonial-rating" value="<?php if ( isset ( $comotest_stored_meta['comoTestimonial-rating'] ) ) echo $comotest_stored_meta['comoTestimonial-rating'][0] ?>" /></span></p>
    
    <p><label for="comoTestimonial-featured" class="comometa-row-title"><?php _e( 'Featured', 'comoTestimonial-textdomain' )?></label>
    <span class="comometa-row-content"><input type="checkbox" name="comoTestimonial-featured" id="comoTestimonial-featured" value="yes" <?php if ( isset ( $comoservice_stored_meta['comoTestimonial-featured'] ) ) checked( $comoservice_stored_meta['comoTestimonial-featured'][0], 'yes' ); ?> /> <?php _e( 'Feature this service on the Home Page', 'comoTestimonial-featured' )?></span></p>
    
    <input type="hidden" name="comoupdate_flag" value="true" />
    
    <script>
		jQuery(document).ready(function(){
			jQuery('.datepicker').datepicker({
				dateFormat : 'm/dd/yy'
				//dateFormat : 'yy-mm-dd'
			});
		});
	</script>
    
    <?php 
	
	// Enqueue Datepicker + jQuery UI CSS
	wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);
	}

// Saves the Testimonial Info Section meta input
function comoTestimonial_meta_save( $post_id ) {
	
	// Only do this if our custom flag is present
    if (isset($_POST['comoupdate_flag'])) {
	
		// Checks save status
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ 'comoTestimonial_nonce' ] ) && wp_verify_nonce( $_POST[ 'comoTestimonial_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

		// Exits script depending on save status
		if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
			return;
		}

		// Specify Meta Variables to be Updated
		$metaVars = array('comoTestimonial-name','comoTestimonial-company','comoTestimonial-location','comoTestimonial-email','comoTestimonial-rating','comoTestimonial-date','comoTestimonial-featured');
		$checkboxVars = array('comoTestimonial-featured');

		// Update Meta Variables
		foreach ($metaVars as $var) {
			if (in_array($var,$checkboxVars)) {
				if (isset($_POST[$var])) {
					update_post_meta($post_id, $var, 'yes');
				} else {
					update_post_meta($post_id, $var, '');
				}
			} else {
				if(isset($_POST[$var])) {
					update_post_meta($post_id, $var, $_POST[$var]);
				} else {
					update_post_meta($post_id, $var, '');
				}
			}
		}
	}
}
add_action( 'save_post', 'comoTestimonial_meta_save' );

// Adds the meta box stylesheet when appropriate 
function testimonials_admin_styles(){
    global $typenow;
    if($typenow == 'testimonial') {
        wp_enqueue_style('testimonial_meta_box_styles', plugin_dir_url( __FILE__ ) .'css/admin.min.css');
    }
}
add_action('admin_print_styles', 'testimonials_admin_styles');

// Custom Image Sizes
add_action( 'after_setup_theme', 'comoTestimonial_img_sizes' );
function comoTestimonial_img_sizes() {
	add_image_size( 'Testimonial-image', 300, 200, true ); // (cropped)
}


/* ##################### Shortcode to Show Testimonials ##################### */

// Usage: [comoTestimonials featured=TRUE/FALSE limit=NUMBER template=TEMPLATE NAME orderby=DATE/TITLE/MENU_ORDER order=ASC/DESC]
class Como_Testimonial_Members_Shortcode {
	static $add_script;
	static $add_style;
	static function init() {
		add_shortcode('comoTestimonials', array(__CLASS__, 'handle_shortcode'));
		//add_action('init', array(__CLASS__, 'register_script'));
		//add_action('wp_footer', array(__CLASS__, 'print_script'));
	}
	
	static function handle_shortcode($atts) {
		self::$add_style = true;
		self::$add_script = true;
		
		$testimonial_type = (isset($atts['featured']) ? $atts['featured'] : 'all');
		$testimonial_template = (isset($atts['template']) ? $atts['template'] : 'default');
		$limit = (isset($atts['limit']) ? $atts['limit'] : -1);
		$orderby = (isset($atts['orderby']) ? $atts['orderby'] : 'menu_order');
		$order = (isset($atts['order']) ? $atts['order'] : '');
		
		if ($testimonial_type == 'featured') {
			$args = array('post_type'=>'testimonial','post_status'=>'publish','meta_query'=>array(array('key'=>'service-featured','value'=>'yes')),'orderby'=>$orderby,'posts_per_page'=>$limit);
		} else {
			$args = array('post_type'=>'testimonial','post_status'=>'publish','orderby'=>$orderby,'posts_per_page'=>$limit);
		}
		if (!empty($order)) { $args['order'] = $order; }
		$query = new WP_Query( $args );
		
		if ($query->have_posts()) { 
			unset($testimonial_array);
			while ($query->have_posts()) {
				$query->the_post(); 
				unset($tm);
				$tm = array();
				$tm['id'] = get_the_ID();
				$meta = get_post_meta($tm['id']);
				$tm['image'] = get_the_post_thumbnail($tm['id'],'full',array('class'=>'Testimonial-photo'));
				$tm['title'] = get_the_title();
				$tm['name'] = ((isset($meta['comoTestimonial-name'])) ? $meta['comoTestimonial-name'][0] : '');
				$tm['company'] = ((isset($meta['comoTestimonial-company'])) ? $meta['comoTestimonial-company'][0] : '');
				$tm['location'] = ((isset($meta['comoTestimonial-location'])) ? $meta['comoTestimonial-location'][0] : '');
				$tm['rating'] = ((isset($meta['comoTestimonial-rating'])) ? $meta['comoTestimonial-rating'][0] : '');
				$tm['date'] = ((isset($meta['comoTestimonial-date'])) ? $meta['comoTestimonial-date'][0] : '');
				$tm['link'] = get_permalink();
				$tm['excerpt'] = wpautop(get_the_excerpt());
				$tm['content'] = get_the_content();
				$testimonial_array[] = $tm;
			}
			if ($testimonial_template) {
				$temp = (is_child_theme() ? get_stylesheet_directory() : get_template_directory() ) . '/como-testimonials/'. $testimonial_template .'.php';
				if (file_exists($temp)) {
					include($temp);
				} else {
					include(plugin_dir_path( __FILE__ ) .'templates/default.php');
				}
			} else {
				include(plugin_dir_path( __FILE__ ) .'templates/default.php');
			}
			$comoTestimonials = $testimonialDisplay;
		}
		if ($comoTestimonials) { echo $comoTestimonials; }
	}
	
	// Register & Print Scripts
	/*static function register_script() {
		wp_register_script('comoTestimonials_script', plugins_url('js/comoTestimonials.js', __FILE__), array('jquery'), '1.0', true);
	}
	static function print_script() {
		if ( ! self::$add_script )
			return;
		wp_print_scripts('comoTestimonials_script');
	}*/
}
Como_Testimonial_Members_Shortcode::init();


// Shortcode to display Agregate Reviews
// [comotest-agg visible=TRUE/FALSE]
function ratingsagg_func( $atts ) {
    $visible = (!empty($atts['visible']) ? $atts['visible'] : TRUE);
	$vizClass = (($visible) ? '' : 'hide');
	unset($rating_array);
	
	$args = array('post_type'=>'testimonial','post_status'=>'publish');
	$query = new WP_Query( $args );
	if ($query->have_posts()) { 
		while ($query->have_posts()) {
			$query->the_post(); 
			$rateID = get_the_ID();
			$ratings[] = get_post_meta($rateID,'comoTestimonial-rating',true);
		}
	}
	$aggregate = array_sum($ratings) / count($ratings);
	$ratingDisplay = '<div class="'. $vizClass .'" itemscope itemtype="http://schema.org/Product">
	<h3 itemprop="name">'. get_bloginfo('name') .'</h3>
  	<div itemprop="description">'. get_bloginfo('description') .'</div>
	  	<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
			<div>Average rating:
		  		<span itemprop="ratingValue">'. $aggregate .'</span> out of 
		  		<span itemprop="bestRating">5</span> with
		  		<span itemprop="ratingCount">'. count($ratings) .'</span> ratings
			</div>
	  	</div>
	</div>';
    return $ratingDisplay;
}
add_shortcode( 'comotest-agg', 'ratingsagg_func' );

?>