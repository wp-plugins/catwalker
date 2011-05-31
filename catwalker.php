<?php
/*
Plugin Name: catWalker
Plugin URI: http://wordpress.blogs.wesleyan.edu/plugins/catwalker/
Description: List categories, cross-categorizations or category posts in page or 
post contents. Let users search for the intersection of two categories. 
Version: 0.7
Author: Kevin Wiliarty
Author URI: http://kwiliarty.blogs.wesleyan.edu/
*/

/* 
Copyright 2011  Wesleyan University 

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * include class(es)
 */

include('crosscat_query.class.php');

/**
 * Custom Taxonomy: Attribute
 *
 * registers a custom taxonomy called 'attribute' only if one
 * does not already exist and if the user has opted in on the 
 * Settings > Writing page in the Catwalker Options section
 *
 */

// If the taxonomy 'attribute' is not already registered
if ( !taxonomy_exists( 'attribute' ) && ( get_option('catwalker_custom_taxonomy') == "true" )) {

	// register the taxonomy
	function create_attributes_taxonomy() {

		//set the labels
		$labels = array(
			'name'              => _x( 'Attributes' , 'taxonomy general name' ),
			'singular_name'     => _x( 'Attribute' , 'taxonomy singular name' ),
			'search_items'      => __( 'Search Attributes' ),
			'all_items'         => __( 'All Attributes' ),
			'parent_item'       => __( 'Parent Attribute' ),
			'parent_item_colon' => __( 'Parent Attribute:' ),
			'edit_item'         => __( 'Edit Attribute' ),
			'update_item'       => __( 'Update Attribute' ),
			'add_new_item'      => __( 'Add New Attribute' ),
			'new_item_name'     => __( 'New Attribute Name' ),
			'menu_name'         => __( 'Attributes' ),
		);

		register_taxonomy( 'attribute' , array( 'post' , 'page' ) , array(
			'hierarchical' => true,
			'labels'       => $labels,
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => array( 'slug'=>'attribute' , 'hierarchical'=>true ),
			)
		);
	}
}

if ( ( get_option('catwalker_custom_taxonomy') == "true" ) ) {
	//hook into the init action to create the taxonomy
	add_action( 'init' , 'create_attributes_taxonomy' , 0 );
}

/**
 *
 * Option: to use or not use the custom taxonomy
 *
 */

//validation callback
function catwalker_sanitize_checkbox( $checkbox ) {
	if ( $checkbox != true ) {
		$checkbox = "false";
	}
	return $checkbox;
}

////register setting
//function register_catwalker_settings() {
//	//use the custom taxonomy: attribute?
//	register_setting( 'catwalker-group' , 
//		'catwalker_custom_taxonomy' , 
//		'catwalker_sanitize_checkbox' );
//}
//
////call register settings function
//add_action( 'admin_init' , 'register_catwalker_settings' );

//function to generate section on writing options page
function catwalker_options() {
	echo <<<EOF
<p>Settings for the Catwalker plugin</p>
EOF;
}

//function to generate checkbox for custom taxonomy
function catwalker_custom_taxonomy_option() {
	$checked="";
	if ( get_option( 'catwalker_custom_taxonomy' ) == "true" ) {
		$checked = ' checked="yes"';
	}
	echo <<<EOF
<input type='checkbox' name='catwalker_custom_taxonomy' value='true'{$checked} /> Check the box to use a custom hierarchical taxonomy (called "Attributes") that can be set for both Posts and Pages.
EOF;
}

//add the catwalker options to the writing preferences page
function catwalker_menu() {
	add_settings_section( 'catwalker-options' ,
		'Catwalker Options' ,
		'catwalker_options' ,
		'writing'
	);
	add_settings_field( 'catwalker-custom-taxonomy' , 
		'Use Custom Taxonomy' ,
		'catwalker_custom_taxonomy_option' ,
		'writing' ,
		'catwalker-options'
	);
	register_setting( 'writing' , 'catwalker_custom_taxonomy' , 'catwalker_sanitize_checkbox' );
}

//register the options functions
add_action( 'admin_init' , 'catwalker_menu' );

/**
 * Cross Category requests
 * 
 * handles the query strings created by the crosscat widget
 * by redirecting them to a pretty URL
 *
 */

// handle cross category requests
function cc_form() {

	// If the query "type" is "crosscat"
	if ( esc_html( $_GET['type'] == 'crosscat' ) ) {

		// get the URL for the site
		$url = get_bloginfo('url');
		// $plus will add a "plus-sign" or not as needed
		$plus = '';
		// get the id's for the categories/attributes from the query string
		$cat1id = esc_html( $_GET['cat1'] );
		$cat2id = esc_html( $_GET['cat2'] );
		// get the taxonomy from the query string
		$taxonomy = esc_html( $_GET['tax1'] );
		// get the slugs corresponding to the id's
		$cat1 = get_term_by( 'id' , $cat1id , $taxonomy );
		if ( $cat1 != '' ) {
			$cat1slug = $cat1->slug;
		}
		$cat2 = get_term_by( 'id' , $cat2id , $taxonomy );
		if ( $cat2 != '' ) {
			$cat2slug = $cat2->slug;
		}
		// if you have two terms, set $plus to "+"
		if ( $cat1slug && $cat2slug ) {
			$plus = '+';
		}
		// save the id's in cookies
		// this is the primary way to make the dropdowns persist
		setcookie( 'crosscat-cat1' , $cat1id );
		setcookie( 'crosscat-cat2' , $cat2id );
		// build the new URL and redirect to it
		$crosscat_url = "{$url}/?{$taxonomy}={$cat1slug}{$plus}{$cat2slug}";
		wp_redirect($crosscat_url);
		exit;
	}
}

// add the action hook for the redirect
add_action( 'wp_loaded' , 'cc_form' );

/**
 * Catwalker Styles
 *
 * for the widget and for the in-post listings
 *
 */

//function to register and enqueue catwalker stylesheet
function catwalker_css () {
	//only for non-admin pages
	if ( !is_admin() ) {
		//register and enqueue the catwalker style sheet
		$catwalker_css = plugins_url( '/catwalker.css' , (__FILE__) );
		wp_register_style( 'catwalker-css' , $catwalker_css );
		wp_enqueue_style( 'catwalker-css' );
	}
}

//add the catwalker_css function to the init hook
add_action( 'init' , 'catwalker_css' );

////function to register and enqueue the catwalker js
//function catwalker_js () {
//	//only for non-admin pages
//	if ( !is_admin() ) {
//		//register and enqueue the catwalker js
//		$catwalker_js = plugins_url( '/catwalker.js' , (__FILE__) );
//		wp_register_script( 'catwalker-js' , $catwalker_js , 'jquery' , false , false );
//		wp_enqueue_script( 'jquery' );
//		wp_enqueue_script( 'catwalker-js' );
//	}
//}
//
////add the catwalker_js function to the init action
//add_action( 'init' , 'catwalker_js' );

/**
 *
 * function: shortcode for category listings
 *
 */

//function to process the straightup category listing shortcode
function catwalker( $atts ) {
	
	//parse the shortcode attributes
	extract(
		shortcode_atts(
			array(
				'show_option_all'    => '',
				'orderby'            => 'name',
				'order'              => 'ASC',
				'show_last_update'   => 0,
				'style'              => 'list',
				'show_count'         => 1,
				'hide_empty'         => 0,
				'use_desc_for_title' => 1,
				'child_of'		     => 0,
				'feed'               => '',
				'feed_type'          => '',
				'feed_image'         => '',
				'exclude'            => '',
				'exclude_tree'       => '',
				'include'            => '',
				'hierarchical'       => true,
				'title_li'           => __( 'Categories' ),
				'show_option_none'   => __( 'No categories' ),
				'number'             => NULL,
				//do not allow the echo attribute in the shortcode
				//'echo'               => 0,
				'depth'              => 0,
				'current_category'   => 0,
				'pad_counts'         => 0,
				'taxonomy'           => 'category',
				//do not allow the shortcode to set a custom walker
				//'walker'             => 'Walker_Category'
			),
			$atts
		)
	);

	//set arguments for wp_list_categories
	$args = array(
		'show_option_all'    => $show_option_all,
		'orderby'            => $orderby,
		'order'              => $order,
		'show_last_update'   => $show_last_update,
		'style'              => $style,
		'show_count'         => $show_count,
		'hide_empty'         => $hide_empty,
		'use_desc_for_title' => $use_desc_for_title,
		'child_of'		     => $child_of,
		'feed'               => $feed,
		'feed_type'          => $feed_type,
		'feed_image'         => $feed_image,
		'exclude'            => $exclude,
		'exclude_tree'       => $exclude_tree,
		'include'            => $include,
		'hierarchical'       => $hierarchical,
		'title_li'           => $title_li,
		'show_option_none'   => $show_option_none,
		'number'             => $number,
		'echo'               => 0,
		'depth'              => $depth,
		'current_category'   => $current_category,
		'pad_counts'         => $pat_counts,
		'taxonomy'           => $taxonomy,
		'walker'             => 'Walker_Category',
	);


	//return the contents
	$content = wp_list_categories( $args );
	return $content;
}

//add the shortcode that will call the function
add_shortcode( 'categories' , 'catwalker' );

/**
 *
 * function: list a series of category intersections
 *
 */

//function to list a series of category intersections
//called by the crosscat shortcode
function crosscat_func( $atts ) {
	//parse the shortcode attributes
	extract(
		shortcode_atts(
			array(
				'type'         => 'post',
				'child_of'     => 0,
				'parent'       => '',
				'orderby'      => 'name',
				'order'        => 'ASC',
				'hide_empty'   => 1,
				'hierarchical' => 1,
				'exclude'      => '',
				'include'      => '',
				'number'       => '',
				'taxonomy'     => 'category',
				'pad_counts'   => false,
				'intersector'  => '',
			),
			$atts
		)
	);

	//set arguments for get_categories
	$args = array(
		'type'         => $type,
		'child_of'     => $child_of,
		'parent'       => $parent,
		'orderby'      => $orderby,
		'order'        => $order,
		'hide_empty'   => $hide_empty,
		'hierarchical' => $hierarchical,
		'exclude'      => $exclude,
		'include'      => $include,
		'number'       => $number,
		'taxonomy'     => $taxonomy,
		'pad_counts'   => $pad_counts,
	);

	//get the categories using the arguments above (except $intersector)
	$categories = get_categories( $args );

	//get the blog URL
	$url = get_bloginfo('url');

	//build the list of category intersections
	$plus = '';
	$crosscat_list =  "<ul class='crosscat'>\n";
	if( $intersector != '' ) { 
		$intersector_id = $intersector;
		$intersector_cat = get_term_by( 'id' , $intersector_id , $taxonomy );
		$intersector_slug = $intersector_cat->slug;
		$intersector_name = $intersector_cat->name;
		$plus = '+';
		$in = ' / ';
	}
	foreach( $categories as $category ) {
		$cat_query = new crosscat_query( $category->term_id , $intersector_id , $taxonomy );
		$test_posts = new WP_Query( $cat_query->cc_query );
		$cc_results = '';
		if ( !$test_posts->have_posts() ) { 
			if ( $hide_empty == '1' ) {
				continue;
			}
			$cc_results = ' cc-no-results';
		} 
		$crosscat_list .= <<<EOF
<li class='categories{$cc_results}'><a href='{$url}?{$taxonomy}={$category->slug}{$plus}{$intersector_slug}'>{$intersector_name}{$in}{$category->name}</a> ({$test_posts->post_count})</li>\n
EOF;
	}
	$crosscat_list .= "</ul>";
	wp_reset_postdata();
	return $crosscat_list;
}

//add the shortcode to call the specialized category listings
add_shortcode( 'crosscat' , 'crosscat_func' );

/**
 *
 * function: list the posts belonging to a category
 * shortcode: category-posts
 *
 */

//function to process the category-posts shortcode
function catwalker_posts( $atts ) {

	//parse the shortcode attributes
	extract(
		shortcode_atts(
			array(
				'numberposts' => -1, //default is to show all
				'offset'      => 0,
				'category'    => '',
				'taxonomy'    => 'category',
				'terms'       => '',
				'field'       => 'term_id',
			),
			$atts
		)
	);

	//set arguments for get_posts
	$args = array(
		'numberposts' => $numberposts,
		'offset'      => $offset,
		'category'    => $category,
		'taxonomy'    => $taxonomy,
		'terms'       => array($terms),
		'field'       => $field
	);

	//return the contents

	//create internal query
	$internal_query = new WP_Query(
		array(
			'tax_query' => array(
				'relation' => 'AND',
				array(
					'taxonomy' => $taxonomy,
					'terms'    => $terms,
					'field'    => 'term_id'
				)
			)
		)
	);

	//The internal loop
	$content = '';
	while( $internal_query->have_posts() ) {
		$internal_query->the_post();
		$content .= the_title( '' , "<br />\n" , false );
	}
	wp_reset_postdata();
	return $content;
	
//	global $post;
//	$save_post = $post;
//	$content = '';
//	$category_posts = get_posts( $args );
//	foreach( $category_posts as $post ) {
//		$content .= $post->post_title;
//		$content .= "<br />\n";
//	}
//	$post = $save_post;
//	return $content;

}

//add the shortcode to call the specialized category listings
add_shortcode( 'category-posts' , 'catwalker_posts' );

/**
 *
 * widget: crossCategorizer widget
 *
 */

//crossCategorizer widget
class crossCategorizerWidget extends WP_Widget 
{
	/**
	* Declares the crossCategorizerWidget class.
	*
	*/
	function crossCategorizerWidget(){
		$widget_ops = array('classname' => 'widget-cross-categorizer', 'description' => __( "A widget to find the posts at the intersection of two categories") );
		$control_ops = array('height' => 300);
		$this->WP_Widget('cross-categorizer', __('Cross Categorizer'), $widget_ops, $control_ops);
	}
	
	/**
	* Displays the Widget
	*
	*/
	function widget($args, $instance){
		extract($args);
		$taxonomy = get_query_var( 'taxonomy' );
		$values = explode( '+' , get_query_var($taxonomy) );
		if (isset($_COOKIE['crosscat-cat1'])) {
			$cat1_id = intval( $_COOKIE['crosscat-cat1'] );
		}
		else {
			$cat1_slug = $values[0];
			$cat1 = get_term_by( 'slug' , $cat1_slug , $taxonomy );
			$cat1_id = $cat1->term_id;
		}
		if (isset($_COOKIE['crosscat-cat2'])) {
			$cat2_id = intval( $_COOKIE['crosscat-cat2'] );
		}
		else {
			$cat2_slug = $values[1];
			$cat2 = get_term_by( 'slug' , $cat2_slug , $taxonomy );
			$cat2_id = $cat2->term_id;
		}
		$option_all = 'All categories';
		$child_of1 = empty($instance['child_of1']) ? '0' : $instance['child_of1'];
		$child_of2 = empty($instance['child_of2']) ? '0' : $instance['child_of2'];
		$taxonomy1 = empty($instance['taxonomy1']) ? 'category' : $instance['taxonomy1'];
		$taxonomy2 = empty($instance['taxonomy2']) ? 'category' : $instance['taxonomy2'];
		$dropdownOneArgs = array( 
			'id'               => 'crosscat-dd1',
			'name'             => 'cat1',
			'hierarchical'     => 1,
			'orderby'          => 'name',
			'order'            => 'ASC',
			'selected'         => $cat1_id,
			'show_option_all'  => $option_all,
			'child_of'         => $child_of1,
			'taxonomy'         => $taxonomy1,
		);
		$dropdownTwoArgs = array( 
			'id'               => 'crosscat-dd2',
			'name'             => 'cat2',
			'hierarchical'     => 1,
			'orderby'          => 'name',
			'order'            => 'ASC',
			'selected'         => $cat2_id,
			'show_option_all'  => $option_all,
			'child_of'         => $child_of2,
			'taxonomy'         => $taxonomy1,
	   	);
		$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
		$label1 = empty($instance['label1']) ? '&nbsp;' : $instance['label1'];
		$label2 = empty($instance['label2']) ? '&nbsp;' : $instance['label2'];
		
		# Before the widget
		echo $before_widget;
		
		# The title
		if ( $title )
			echo $before_title . $title . $after_title;
		
		# Make the widget
		$url = get_bloginfo('url');
		$catwalker_action = $url;
		echo "<form class='crosscat-form' action='{$catwalker_action}' method='get'>\n";
		echo "<input type='hidden' name='url' value='{$url}' />\n";
		echo "<input type='hidden' name='type' value='crosscat' />\n";
		echo "<input type='hidden' name='tax1' value='{$taxonomy1}' />\n";
		echo "<input type='hidden' name='tax2' value='{$taxonomy2}' />\n";
		echo "<div id='dd1-label'>{$instance['label1']}:</div>\n";
		$dropdownOne = wp_dropdown_categories( $dropdownOneArgs );
		echo "<div id='dd2-label'>{$instance['label2']}:</div>\n";
		$dropdownTwo = wp_dropdown_categories( $dropdownTwoArgs );		
		echo "<input type='submit' value='Search' />\n";
		echo "</form>\n";
		
		# After the widget
		echo $after_widget;
	}
	
	/**
	* Saves the widgets settings.
	*
	*/
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['label1'] = strip_tags(stripslashes($new_instance['label1']));
		$instance['child_of1'] = $new_instance['child_of1'];
		$instance['label2'] = strip_tags(stripslashes($new_instance['label2']));
		$instance['child_of2'] = $new_instance['child_of2'];
		$instance['taxonomy1'] = $new_instance['taxonomy1'];
		$instance['taxonomy2'] = $new_instance['taxonomy2'];

		return $instance;
	}
	
	/**
	* Creates the edit form for the widget.
	*
	*/
	function form($instance){
		//Defaults
		$instance = wp_parse_args( (array) $instance, array(
			'title'        => '', 
			'label1'       => 'Select a category', 
			'child_of1'    => '0' , 
			'label2'       => 'Select another',
			'child_of2'    => '0' , 
			'taxonomy1'    => 'category',
			'cat1selected' => 'selected',
			'att1selected' => '',
			'taxonomy2'    => 'category',
			'cat2selected' => 'selected',
			'att2selected' => '',
			) 
		);
		
		//translatable expressions
		$title_label = __('Title: ');
		$taxonomy_label = __('Taxonomy: ');
		$category1_label = __('Category 1: ');
		$category2_label = __('Category 2: ');
		$label_label = __('Label: ');
		$child_of_label = __('Child of: ');


		$title = htmlspecialchars($instance['title']);
		$label1 = htmlspecialchars($instance['label1']);
		$child_of1 = $instance['child_of1'];
		$label2 = htmlspecialchars($instance['label2']);
		$child_of2 = htmlspecialchars($instance['child_of2']);
		$show_option_all = 'All categories';
		$taxonomy1 = htmlspecialchars($instance['taxonomy1']);
		$taxonomy2 = htmlspecialchars($instance['taxonomy2']);
		$cat1selected = ( $taxonomy1 == 'category' ) ? ' selected="selected"' : '';
		$att1selected = ( $taxonomy1 == 'attribute' ) ? ' selected="selected"' : '';
		$cat2selected = ( $taxonomy2 == 'category' ) ? ' selected="selected"' : '';
		$att2selected = ( $taxonomy2 == 'attribute' ) ? ' selected="selected"' : '';
		$admin_dd_1 = wp_dropdown_categories( array(
			'id'              => $this->get_field_id('child_of1') ,
			'name'            => $this->get_field_name('child_of1') ,
			'echo'            => false,
			'hierarchical'    => 1 , 
			'selected'        => $child_of1 ,
			'show_option_all' => 'All categories' ,
			'taxonomy'        => $taxonomy1,
			)
		);
		$admin_dd_2 = wp_dropdown_categories( array(
			'id'              => $this->get_field_id('child_of2') ,
			'name'            => $this->get_field_name('child_of2') ,
			'echo'            => false,
			'hierarchical'    => 1 , 
			'selected'        => $child_of2 ,
			'show_option_all' => 'All categories' ,
			'taxonomy'        => $taxonomy1,
			)
		);


		// Output the options

		// Title
		echo <<<EOF
<div>
<label for="{$this->get_field_id('title')}">{$title_label}</label> 
<input id="{$this->get_field_id('title')}" name="{$this->get_field_name('title')}" type="text" value="{$title}" />
</div>

<div>
<label for="{$this->get_field_id('taxonomy1')}">{$taxonomy_label}</label>
<select id="{$this->get_field_id('taxonomy1')}" name="{$this->get_field_name('taxonomy1')}">
<option value="category"{$cat1selected}>Category</option>
<option value="attribute"{$att1selected}>Attribute</option>
</select>
</div>

<h3>{$category1_label}</h3>

<div>
<label for="{$this->get_field_id('label1')}">{$label_label}</label>
<input id="{$this->get_field_id('label1')}" name="{$this->get_field_name('label1')}" type="text" value="{$label1}" />
</div>

<div>
<label for="{$this->get_field_name('child_of1')}">{$child_of_label}</label>   
{$admin_dd_1}
</div>

<h3>{$category2_label}</h3>

<div>
<label for="{$this->get_field_id('label2')}">{$label_label}</label>
<input id="{$this->get_field_id('label2')}" name="{$this->get_field_name('label2')}" type="text" value="{$label2}" />
</div>

<div>
<label for="{$this->get_field_name('child_of2')}">{$child_of_label}</label>   
{$admin_dd_2}
</div>
EOF;

	}

}// END class


//register crossCategorizer widget
function crossCategorizerInit() {
	register_widget( 'crossCategorizerWidget' );
}
add_action( 'widgets_init' , 'crossCategorizerInit' );
?>
