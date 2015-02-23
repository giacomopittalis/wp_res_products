<?php
/*
  Plugin Name: RunErgoSum Store
  Plugin URI: http://runergosum.it
  Description:  Display product information
  Version: 1.0
  Author: Giacomo Pittalis
  License: GPLv2
 */


// Call function when plugin is activated
register_activation_hook(__FILE__, 'runergosum_store_install');

function runergosum_store_install() {

    //setup default option values
    $res_options_arr = array(
        'currency_sign' => '€'
    );

    //save our default option values
    update_option('runergosum_options', $res_options_arr);
}

// create a new taxonomy for the products category
add_action('init', 'res_products_taxonomy');

function res_products_taxonomy() {
    // create a new taxonomy
    register_taxonomy(
    'type', 'runergosum-products', array(
    'hierarchical' => true,
    'label' => __('Type'),
    'query_var' => true,
        'rewrite' => true
    )
    );
}

// Action hook to initialize the plugin
add_action('init', 'runergosum_store_init');

//Initialize the Store
function runergosum_store_init() {

    //register the products custom post type
    $labels = array(
        'name' => __('Products', 'runergosum-plugin'),
        'singular_name' => __('Product', 'runergosum-plugin'),
        'add_new' => __('Add New', 'runergosum-plugin'),
        'add_new_item' => __('Add New Product', 'runergosum-plugin'),
        'edit_item' => __('Edit Product', 'runergosum-plugin'),
        'new_item' => __('New Product', 'runergosum-plugin'),
        'all_items' => __('All Products', 'runergosum-plugin'),
        'view_item' => __('View Product', 'runergosum-plugin'),
        'search_items' => __('Search Products', 'runergosum-plugin'),
        'not_found' => __('No products found', 'runergosum-plugin'),
        'not_found_in_trash' => __('No products found in Trash', 'runergosum-plugin'),
        'menu_name' => __('Products', 'runergosum-plugin')
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        // Select which databoxes to display
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            // Adding Categories and Tags Databoxes
            //'taxonomies' => array('category', 'post_tag')
    );

    register_post_type('runergosum-products', $args);
}

// Action hook to add the post products menu item
add_action('admin_menu', 'runergosum_store_menu');

//create the Store  Masks sub-menu
function runergosum_store_menu() {

    add_options_page(__('RunErgoSum Store Settings Page', 'runergosum-plugin'), __('RunErgoSum Store Settings', 'runergosum-plugin'), 'manage_options', 'runergosum-store-settings', 'runergosum_store_settings_page');
}

//build the plugin settings page
function runergosum_store_settings_page() {

    //load the plugin options array
    $res_options_arr = get_option('runergosum_options');

    //set the option array values to variables
    $hs_currency_sign = $res_options_arr['currency_sign'];
    ?>
    <div class="wrap">
        <h2><?php _e('RunErgoSum Store Options', 'runergosum-plugin') ?></h2>

        <form method="post" action="options.php">
            <?php settings_fields('runergosum-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Currency Sign', 'runergosum-plugin') ?></th>
                    <td><input type="text" name="runergosum_options[currency_sign]" value="<?php echo esc_attr($hs_currency_sign); ?>" size="1" maxlength="1" /></td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'runergosum-plugin'); ?>" />
            </p>

        </form>
    </div>
    <?php
}

// Action hook to register the plugin option settings
add_action('admin_init', 'runergosum_store_register_settings');

function runergosum_store_register_settings() {

    //register the array of settings
    register_setting('runergosum-settings-group', 'runergosum_options', 'runergosum_sanitize_options');
}

function runergosum_sanitize_options($options) {

    $options['currency_sign'] = (!empty($options['currency_sign']) ) ? sanitize_text_field($options['currency_sign']) : '';

    return $options;
}

//Action hook to register the Products meta box
add_action('add_meta_boxes', 'runergosum_store_register_meta_box');

function runergosum_store_register_meta_box() {

    // create our custom meta box 
    add_meta_box('runergosum-product-meta', __('Product Information', 'runergosum-plugin'), 'runergosum_meta_box', 'runergosum-products', 'side', 'default');
}

//build product meta box
function runergosum_meta_box($post) {

    // retrieve our custom meta box values
    $res_sku = get_post_meta($post->ID, '_runergosum_product_sku', true);
    $res_price = get_post_meta($post->ID, '_runergosum_product_price', true);
    $res_weight = get_post_meta($post->ID, '_runergosum_product_weight', true);
    $res_color = get_post_meta($post->ID, '_runergosum_product_color', true);
    $res_inventory = get_post_meta($post->ID, '_runergosum_product_inventory', true);

    //nonce field for security
    wp_nonce_field('meta-box-save', 'runergosum-plugin');

    // display meta box form
    echo '<table>';
    echo '<tr>';
    echo '<td>' . __('Amazon ID', 'runergosum-plugin') . ':</td><td><input type="text" name="runergosum_product_sku" value="' . esc_attr($res_sku) . '" size="10"></td>';
    echo '</tr><tr>';
    echo '<td>' . __('Price', 'runergosum-plugin') . ':</td><td><input type="text" name="runergosum_product_price" value="' . esc_attr($res_price) . '" size="5"></td>';
    echo '</tr><tr>';
    echo '<td>' . __('Weight', 'runergosum-plugin') . ':</td><td><input type="text" name="runergosum_product_weight" value="' . esc_attr($res_weight) . '" size="5"></td>';
    echo '</tr><tr>';
    echo '<td>' . __('Color', 'runergosum-plugin') . ':</td><td><input type="text" name="runergosum_product_color" value="' . esc_attr($res_color) . '" size="5"></td>';
    echo '</tr>';

    //display the meta box shortcode legend section
    echo '<tr><td colspan="2"><hr></td></tr>';
    echo '<tr><td colspan="2"><strong>' . __('Shortcode Legend', 'runergosum-plugin') . '</strong></td></tr>';
    echo '<tr><td>' . __('Amazon ID', 'runergosum-plugin') . ':</td><td>[hs show=sku]</td></tr>';
    echo '<tr><td>' . __('Price', 'runergosum-plugin') . ':</td><td>[hs show=price]</td></tr>';
    echo '<tr><td>' . __('Weight', 'runergosum-plugin') . ':</td><td>[hs show=weight]</td></tr>';
    echo '<tr><td>' . __('Color', 'runergosum-plugin') . ':</td><td>[hs show=color]</td></tr>';
    echo '</table>';
}

// Action hook to save the meta box data when the post is saved
add_action('save_post', 'runergosum_store_save_meta_box');

//save meta box data
function runergosum_store_save_meta_box($post_id) {

    //verify the post type is for RunErgoSum Products and metadata has been posted
    if (get_post_type($post_id) == 'runergosum-products' && isset($_POST['runergosum_product_sku'])) {

        //if autosave skip saving data
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        //check nonce for security
        check_admin_referer('meta-box-save', 'runergosum-plugin');

        // save the meta box data as post metadata
        update_post_meta($post_id, '_runergosum_product_sku', sanitize_text_field($_POST['runergosum_product_sku']));
        update_post_meta($post_id, '_runergosum_product_price', sanitize_text_field($_POST['runergosum_product_price']));
        update_post_meta($post_id, '_runergosum_product_weight', sanitize_text_field($_POST['runergosum_product_weight']));
        update_post_meta($post_id, '_runergosum_product_color', sanitize_text_field($_POST['runergosum_product_color']));
        update_post_meta($post_id, '_runergosum_product_inventory', sanitize_text_field($_POST['runergosum_product_inventory']));
    }
}

// Action hook to create the products shortcode
add_shortcode('hs', 'runergosum_store_shortcode');

//create shortcode
function runergosum_store_shortcode($atts, $content = null) {
    global $post;

    extract(shortcode_atts(array(
        "show" => ''
                    ), $atts));

    //load options array
    $res_options_arr = get_option('runergosum_options');

    if ($show == 'sku') {

        $hs_show = get_post_meta($post->ID, '_runergosum_product_sku', true);
    } elseif ($show == 'price') {

        $hs_show = $res_options_arr['currency_sign'] . get_post_meta($post->ID, '_runergosum_product_price', true);
    } elseif ($show == 'weight') {

        $hs_show = get_post_meta($post->ID, '_runergosum_product_weight', true);
    } elseif ($show == 'color') {

        $hs_show = get_post_meta($post->ID, '_runergosum_product_color', true);
    } elseif ($show == 'inventory') {

        $hs_show = get_post_meta($post->ID, '_runergosum_product_inventory', true);
    }

    //return the shortcode value to display
    return $hs_show;
}

// Action hook to create plugin widget
add_action('widgets_init', 'runergosum_store_register_widgets');

//register the widget
function runergosum_store_register_widgets() {

    register_widget('hs_widget');
}

//hs_widget class
class hs_widget extends WP_Widget {

    //process our new widget
    function hs_widget() {

        $widget_ops = array(
            'classname' => 'hs-widget-class',
            'description' => __('Display RunErgoSum Products', 'runergosum-plugin'));
        $this->WP_Widget('hs_widget', __('Products Widget', 'runergosum-plugin'), $widget_ops);
    }

    //build our widget settings form
    function form($instance) {

        $defaults = array(
            'title' => __('Products', 'runergosum-plugin'),
            'number_products' => '3');

        $instance = wp_parse_args((array) $instance, $defaults);
        $title = $instance['title'];
        $number_products = $instance['number_products'];
        ?>
        <p><?php _e('Title', 'runergosum-plugin') ?>: 
            <input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
        <p><?php _e('Number of Products', 'runergosum-plugin') ?>: 
            <input name="<?php echo $this->get_field_name('number_products'); ?>" type="text" value="<?php echo esc_attr($number_products); ?>" size="2" maxlength="2" />
        </p>
        <?php
    }

    //save our widget settings
    function update($new_instance, $old_instance) {

        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['number_products'] = absint($new_instance['number_products']);

        return $instance;
    }

    //display our widget
    function widget($args, $instance) {
        global $post;

        extract($args);

        echo $before_widget;
        $title = apply_filters('widget_title', $instance['title']);
        $number_products = $instance['number_products'];

        if (!empty($title)) {
            echo $before_title . esc_html($title) . $after_title;
        };

        //custom query to retrieve products
        $args = array(
            'post_type' => 'runergosum-products',
            'posts_per_page' => absint($number_products)
        );

        $dispProducts = new WP_Query();
        $dispProducts->query($args);

        while ($dispProducts->have_posts()) : $dispProducts->the_post();

            //load options array
            $res_options_arr = get_option('runergosum_options');

            //load custom meta values
            $hs_price = get_post_meta($post->ID, '_runergosum_product_price', true);
            $hs_inventory = get_post_meta($post->ID, '_runergosum_product_inventory', true);
            ?>
            <p>
                <a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?> Product Information">
                    <?php the_title(); ?><br/>
                    <?php
                    if (has_post_thumbnail()) { // check if the post has a Post Thumbnail assigned to it.
                        the_post_thumbnail('thumbnail');
                    }
                    ?>
                </a>
            </p>
            <?php
            echo '<p>' . __('Price', 'runergosum-plugin') . ': ' . $res_options_arr['currency_sign'] . $hs_price . '</p>';

            //check if Show Inventory option is enabled
            if ($res_options_arr['show_inventory']) {

                //display the inventory metadata for this product
                echo '<p>' . __('Stock', 'runergosum-plugin') . ': ' . $hs_inventory . '</p>';
            }
            echo '<hr>';

        endwhile;

        wp_reset_postdata();

        echo $after_widget;
    }

}










// Widget to show custom categories (Thanks to Nick Halsey)
// Register 'List Custom Taxonomy' widget
add_action( 'widgets_init', 'init_lc_taxonomy' );
function init_lc_taxonomy() { return register_widget('lc_taxonomy'); }

class lc_taxonomy extends WP_Widget {
	/** constructor */
	function lc_taxonomy() {
		parent::WP_Widget( 'lc_taxonomy', $name = 'List Custom Taxonomy' );
	}

	/**
	* This is the Widget
	**/
	function widget( $args, $instance ) {
		global $post;
		extract($args);

		// Widget options
		$title 	 = apply_filters('widget_title', $instance['title'] ); // Title		
		$this_taxonomy = $instance['taxonomy']; // Taxonomy to show
		$hierarchical = !empty( $instance['hierarchical'] ) ? '1' : '0';
		$showcount = !empty( $instance['count'] ) ? '1' : '0';
		if( array_key_exists('orderby',$instance) ){
			$orderby = $instance['orderby'];
		}
		else{
			$orderby = 'count';
		}
		if( array_key_exists('ascdsc',$instance) ){
			$ascdsc = $instance['ascdsc'];
		}
		else{
			$ascdsc = 'desc';
		}
		if( array_key_exists('exclude',$instance) ){
			$exclude = $instance['exclude'];
		}
		else {
			$exclude = '';
		}
		if( array_key_exists('childof',$instance) ){
			$childof = $instance['childof'];
		}
		else {
			$childof = '';
		}
		if( array_key_exists('dropdown',$instance) ){
			$dropdown = $instance['dropdown'];
		}
		else {
			$dropdown = false;
		}
        // Output
		$tax = $this_taxonomy;
		echo $before_widget;
		echo '<div id="lct-widget-'.$tax.'-container" class="list-custom-taxonomy-widget">';
		if ( $title ) echo $before_title . $title . $after_title;
		if($dropdown){
			$taxonomy_object = get_taxonomy( $tax );
			$args = array(
				'show_option_all'    => false,
				'show_option_none'   => '',
				'orderby'            => $orderby,
				'order'              => $ascdsc,
				'show_count'         => $showcount,
				'hide_empty'         => 1,
				'child_of'           => $childof,
				'exclude'            => $exclude,
				'echo'               => 1,
				//'selected'           => 0,
				'hierarchical'       => $hierarchical,
				'name'               => $taxonomy_object->query_var,
				'id'                 => 'lct-widget-'.$tax,
				//'class'              => 'postform',
				'depth'              => 0,
				//'tab_index'          => 0,
				'taxonomy'           => $tax,
				'hide_if_empty'      => true,
				'walker'			=> new lctwidget_Taxonomy_Dropdown_Walker()
			);
			echo '<form action="'. get_bloginfo('url'). '" method="get">';
			wp_dropdown_categories($args);
			echo '<input type="submit" value="go &raquo;" /></form>';
		}
		else {
			$args = array(
					'show_option_all'    => false,
					'orderby'            => $orderby,
					'order'              => $ascdsc,
					'style'              => 'list',
					'show_count'         => $showcount,
					'hide_empty'         => 1,
					'use_desc_for_title' => 1,
					'child_of'           => $childof,
					//'feed'               => '',
					//'feed_type'          => '',
					//'feed_image'         => '',
					'exclude'            => $exclude,
					//'exclude_tree'       => '',
					//'include'            => '',
					'hierarchical'       => $hierarchical,
					'title_li'           => '',
					'show_option_none'   => 'No Categories',
					'number'             => null,
					'echo'               => 1,
					'depth'              => 0,
					//'current_category'   => 0,
					//'pad_counts'         => 0,
					'taxonomy'           => $tax,
					'walker'             => null
				);
			echo '<ul id="lct-widget-'.$tax.'">';
			wp_list_categories($args);
			echo '</ul>';
		}
		echo '</div>';
		echo $after_widget;
	}
	/** Widget control update */
	function update( $new_instance, $old_instance ) {
		$instance    = $old_instance;
		
		$instance['title']  = strip_tags( $new_instance['title'] );
		$instance['taxonomy'] = strip_tags( $new_instance['taxonomy'] );
		$instance['orderby'] = $new_instance['orderby'];
		$instance['ascdsc'] = $new_instance['ascdsc'];
		$instance['exclude'] = $new_instance['exclude'];
		$instance['expandoptions'] = $new_instance['expandoptions'];
		$instance['childof'] = $new_instance['childof'];
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
        $instance['count'] = !empty($new_instance['count']) ? 1 : 0;
        $instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;

		return $instance;
	}
	
	/**
	* Widget settings
	**/
	function form( $instance ) {
		//for showing/hiding advanced options; wordpress moves this script to where it needs to go
			wp_enqueue_script('jquery');
			?><script>
			jQuery(document).ready(function(){
				var status = jQuery('#<?php echo $this->get_field_id('expandoptions'); ?>').val();
				if(status == 'expand')
					jQuery('.lctw-expand-options').hide();
				else if(status == 'contract'){
					jQuery('.lctw-all-options').hide();
				}
			});
			function lctwExpand(id){
				jQuery('#' + id).val('expand');
				jQuery('.lctw-all-options').show(500); 
				jQuery('.lctw-expand-options').hide(500);
			}
			function lctwContract(id){
				jQuery('#' + id).val('contract');
				jQuery('.lctw-all-options').hide(500); 
				jQuery('.lctw-expand-options').show(500);
			}
			</script><?php
		  // instance exist? if not set defaults
		    if ( $instance ) {
				$title  = $instance['title'];
				$this_taxonomy = $instance['taxonomy'];
				$orderby = $instance['orderby'];
				$ascdsc = $instance['ascdsc'];
				$exclude = $instance['exclude'];
				$expandoptions = $instance['expandoptions'];
				$childof = $instance['childof'];
                $showcount = isset($instance['count']) ? (bool) $instance['count'] :false;
                $hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
                $dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
		    } else {
			    //These are our defaults
				$title  = '';
				$orderby  = 'count';
				$ascdsc  = 'desc';
				$exclude  = '';
				$expandoptions  = 'contract';
				$childof  = '';
				$this_taxonomy = 'category';//this will display the category taxonomy, which is used for normal, built-in posts
				$hierarchical = true;
				$showcount = true;
				$dropdown = false;
		    }
			
		// The widget form ?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __( 'Title:' ); ?></label>
				<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" class="widefat" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php echo __( 'Select Taxonomy:' ); ?></label>
				<select name="<?php echo $this->get_field_name('taxonomy'); ?>" id="<?php echo $this->get_field_id('taxonomy'); ?>" class="widefat" style="height: auto;" size="4">
			<?php 
			$args=array(
			  'public'   => true,
			  '_builtin' => false //these are manually added to the array later
			); 
			$output = 'names'; // or objects
			$operator = 'and'; // 'and' or 'or'
			$taxonomies=get_taxonomies($args,$output,$operator); 
			$taxonomies[] = 'category';
			$taxonomies[] = 'post_tag';
			$taxonomies[] = 'post_format';
			foreach ($taxonomies as $taxonomy ) { ?>
				<option value="<?php echo $taxonomy; ?>" <?php if( $taxonomy == $this_taxonomy ) { echo 'selected="selected"'; } ?>><?php echo $taxonomy;?></option>
			<?php }	?>
			</select>
			</p>
			<h4 class="lctw-expand-options"><a href="javascript:void(0)" onclick="lctwExpand('<?php echo $this->get_field_id('expandoptions'); ?>')" >More Options...</a></h4>
			<div class="lctw-all-options">
				<h4 class="lctw-contract-options"><a href="javascript:void(0)" onclick="lctwContract('<?php echo $this->get_field_id('expandoptions'); ?>')" >Hide Extended Options</a></h4>
				<input type="hidden" value="<?php echo $expandoptions; ?>" id="<?php echo $this->get_field_id('expandoptions'); ?>" name="<?php echo $this->get_field_name('expandoptions'); ?>" />
				
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $showcount ); ?> />
				<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts' ); ?></label><br />
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
				<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy' ); ?></label></p>
				
				<p>
					<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php echo __( 'Order By:' ); ?></label>
					<select name="<?php echo $this->get_field_name('orderby'); ?>" id="<?php echo $this->get_field_id('orderby'); ?>" class="widefat" >
						<option value="ID" <?php if( $orderby == 'ID' ) { echo 'selected="selected"'; } ?>>ID</option>
						<option value="name" <?php if( $orderby == 'name' ) { echo 'selected="selected"'; } ?>>Name</option>
						<option value="slug" <?php if( $orderby == 'slug' ) { echo 'selected="selected"'; } ?>>Slug</option>
						<option value="count" <?php if( $orderby == 'count' ) { echo 'selected="selected"'; } ?>>Count</option>
						<option value="term_group" <?php if( $orderby == 'term_group' ) { echo 'selected="selected"'; } ?>>Term Group</option>
					</select>
				</p>
				<p>
					<label><input type="radio" name="<?php echo $this->get_field_name('ascdsc'); ?>" value="asc" <?php if( $ascdsc == 'asc' ) { echo 'checked'; } ?>/> Ascending</label><br/>
					<label><input type="radio" name="<?php echo $this->get_field_name('ascdsc'); ?>" value="desc" <?php if( $ascdsc == 'desc' ) { echo 'checked'; } ?>/> Descending</label>
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('exclude'); ?>">Exclude (comma-separated list of ids to exclude)</label><br/>
					<input type="text" class="widefat" name="<?php echo $this->get_field_name('exclude'); ?>" value="<?php echo $exclude; ?>" />
				</p>
				<p>
					<label for="<?php echo $this->get_field_id('exclude'); ?>">Only Show Children of (category id)</label><br/>
					<input type="text" class="widefat" name="<?php echo $this->get_field_name('childof'); ?>" value="<?php echo $childof; ?>" />
				</p>
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
				<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as Dropdown' ); ?></label></p>
			</div>
<?php 
	}

} // class lc_taxonomy

/* Custom version of Walker_CategoryDropdown */
class lctwidget_Taxonomy_Dropdown_Walker extends Walker {
	var $tree_type = 'category';
	var $db_fields = array ( 'id' => 'term_id', 'parent' => 'parent' );

	function start_el( &$output, $term, $depth, $args ) {
		$url = get_term_link( $term, $term->taxonomy );

		$text = str_repeat( '&nbsp;', $depth * 3 ) . $term->name;
		if ( $args['show_count'] ) {
			$text .= '&nbsp;('. $term->count .')';
		}

		$class_name = 'level-' . $depth;

		$output.= "\t" . '<option' . ' class="' . esc_attr( $class_name ) . '" value="' . esc_url( $url ) . '">' . esc_html( $text ) . '</option>' . "\n";
	}
}
?>
