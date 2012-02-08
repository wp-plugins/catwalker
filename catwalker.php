<?php
/*
Plugin Name: catWalker
Plugin URI: http://wordpress.blogs.wesleyan.edu/plugins/catwalker/
Description: List categories, cross-categorizations or category posts in page or 
post contents. Let users search for the intersection of two categories. 
Version: 1.3.1
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
include('catwalker_list.class.php');

/**
 * Custom Taxonomy: Attribute
 *
 * registers a custom taxonomy called 'attribute' only if one
 * does not already exist and if the user has opted in on the 
 * Settings > Writing page in the Catwalker Options section
 *
 */

// If the taxonomy 'attribute' is not already registered
// and if the preference is selected
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

//If the custom taxonomy preference is selected
if ( ( get_option('catwalker_custom_taxonomy') == "true" ) ) {
	//hook into the init action to create the taxonomy
	add_action( 'init' , 'create_attributes_taxonomy' , 0 );
}

/**
 *
 * Append list of related posts/pages to the content
 *
 */

//function to add a list of related items based on the default taxonomy
function catwalker_list_related( $content ) {
	if ( get_option( 'catwalker_related' ) == "true" ) {

		//ensure that taxonomy is not empty
		$taxonomy = 'category';
		//but use the preference if it is set
		if ( get_option( 'catwalker_default_taxonomy' ) ) {
			$taxonomy = get_option( 'catwalker_default_taxonomy' );
		}
		
		//get the categories or attributes
		$terms = get_the_terms( $post->ID , $taxonomy );

		//if the post has assigned categories or attributes
		if ( $terms ) {

			//establish the current post as an anchor point
			global $post;
			$postID = $post->ID;
			
			//get the terms that have been listed for inclusion or exclusion
			$include_ids = get_option( 'catwalker_related_include_ids' );
			$include_children = get_option( 'catwalker_related_include_children' );
			$exclude_ids = get_option( 'catwalker_related_exclude_ids' );
			$exclude_children = get_option( 'catwalker_related_exclude_children' );

			//turn the comma separated strings into an array
			$include_ids_array = explode( ',' , $include_ids );
			$include_children_array = explode( ',' , $include_children );
			$exclude_ids_array = explode( ',' , $exclude_ids );
			$exclude_children_array = explode( ',' , $exclude_children );
			
			//start a div and an unordered list
			$related_list = "<div class='catwalker-related'><ul>\n";
			//build the list of related posts/pages for each term
			foreach ($terms as $term) {
				//if specific id's or parent-terms have been listed for inclusion
				if ( ( $include_ids && ( $include_ids != '' )) || 
					 ( $include_children && ( $include_children != '' ))) {
					//and if the current term is not in either list
					 if ( !in_array( $term->term_id , $include_ids_array ) &&
					      !in_array( $term->parent , $include_children_array )) {
						//jump to the next term
						continue;
					}	
				}
				//or if the current term is in a list of terms to exclude
				//worth noting that exclusions will trump conflicting inclusions
				if ( in_array( $term->term_id , $exclude_ids_array ) || 
				     in_array( $term->parent , $exclude_children_array )) {
					//jump to the next term
					continue;
				}
				$nested_list = new catwalker_list(
					$postID ,
					'ASC' ,
					'title' ,
					-1 ,
					'id' ,
					$taxonomy ,
					$term->term_id );
				$post_UL = $nested_list->post_list;
				if ( $nested_list->list_count > 1 ) {
					$related_list .= <<<EOF
<li>Also listed under '{$term->name}' ...
	$post_UL
	</li>
EOF;
				}
			}
			//close the unordered list and div
			$related_list .= "</ul></div>\n";
			//and add it to the content
			$content .= $related_list;
		}
	}
	return $content;
}

//hook the filter to the content
add_filter( 'the_content' , 'catwalker_list_related' );

/**
 *
 * Sort preferences for Category archive pages
 *
 */

//function to rewrite category links
function catwalker_custom_archive_sorter( $orderclause ) {

	//if ( is_tax() ) {
	//This conditional should work, but it is not so I use the below
	
	if ( is_category() ) {
		$orderby = get_option( 'catwalker_custom_archive_orderby' );
		$order = get_option( 'catwalker_custom_archive_order' );
		$orderclause = "post_{$orderby} {$order}";
	}
	return $orderclause;
}

//hook the filter
if ( get_option( 'catwalker_custom_archive_sort' ) == 'true' ) {
	add_filter( 'posts_orderby' , 'catwalker_custom_archive_sorter' ); 
}

/**
 *
 * Limit preference for Category archive pages
 *
 */

//function to limit the number of posts on a category archive page
function catwalker_set_custom_archive_limit( $limit ) {

	if ( get_option( 'catwalker_custom_archive_limit' ) && ( is_category() ) && ( !( in_the_loop() ) && !( is_admin() ) ) ) {
		$setting = get_option( 'catwalker_custom_archive_limit' );
		$limit = "LIMIT 0,${setting}";
	}
	return $limit;
}

//hook the filter
add_filter( 'post_limits' , 'catwalker_set_custom_archive_limit' );

/**
 *
 * Append post/page attributes to the content
 *
 */

//function to add attribute information to the end of each post or page
function catwalker_post_attributes_func( $content ) {
	if ( get_option( 'catwalker_post_attributes' ) == "true" ) {
		$attributes = get_the_terms( $post->ID , 'attribute' );
		if ( $attributes ) {
			$blogurl = get_bloginfo('url');
			$attribute_links = '';
			$separator = '';
			//build a comma separated list of Attribute links
			foreach ($attributes as $attribute) {
				$attribute_links .= "{$separator}<a href='{$blogurl}?attribute={$attribute->slug}'>{$attribute->name}</a>";
				$separator = ', ';
			}
			//use a custom class from the options settings
			$class = (get_option( 'catwalker_post_attributes_class' ));
			if ( $class != '' ) {
				$class = " " . $class;
			}
			$attribute_line = <<<EOF
<br /><div class="catwalker-post-attributes{$class}">Attributes: $attribute_links</div>\n
EOF;
			$content .= $attribute_line;
		}
	}
	return $content;
}

//add filter on the_content
add_filter( 'the_content' , 'catwalker_post_attributes_func' );

/**
 *
 * Options page, including:
 *
 * Option: to use or not use the custom taxonomy
 * Option: Choose a default taxonomy
 * Option: Append list of attributes to each post/page
 * Option: CSS class for appended list of attributes
 * Option: Custom sort for term archives
 * Option: Order by
 * Option: Order
 * Option: Limit number of posts on category archive pages
 * Option: Include a list of related posts
 * Option: Show related posts only for specific terms
 * Option: Show related posts for child-terms of specific terms
 * Option: Exclude related posts for specific terms
 * Option: Exclude related posts for child-terms of specific terms
 *
 */

/**
 *
 * Callbacks to validate submitted options
 *
 */

//validation callback for checkbox
function catwalker_sanitize_checkbox( $checkbox ) {
	if ( $checkbox != true ) {
		$checkbox = "false";
	}
	return $checkbox;
}

//validation callback for default taxonomy dropdown
function catwalker_sanitize_default_taxonomy( $choice ) {
	if ($choice != 'attribute') {
		$choice = 'category';
	}
	return $choice;
}

//validate a css class
function catwalker_sanitize_css_class( $class ) {
	$class = preg_replace('/[^A-Za-z0-9-_]/', '', $class);
	return $class;
}

//validate a comma-separated list of numbers
function catwalker_sanitize_commalist( $input ) {
	$input = preg_replace( '/[^0-9,]/' , '' , $input);
	return $input;
}

//validate a number
function catwalker_sanitize_number( $input ) {
	$input = preg_replace( '/[^0-9]/' , '' , $input);
	return $input;
}

//validate orderby dropdown
function catwalker_sanitize_orderby_dropdown( $orderby ) {
	if ( ( $orderby != 'date' ) && ( $orderby != 'title' ) ) {
		$orderby = 'date';
	}
	return $orderby;
}

//validate order dropdown
function catwalker_sanitize_order_dropdown( $order ) {
	if ( ( $order != 'DESC' ) && ( $order != 'ASC' ) ) {
		$order = 'DESC';
	}
	return $order;
}

/**
 *
 * Functions to generate the options form fields
 *
 */

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
<input type='checkbox' name='catwalker_custom_taxonomy' value='true'{$checked} /> Check the box to use a custom hierarchical taxonomy (called "Attributes") that can be set for both posts and pages.
EOF;
}

//function to generate dropdown for Catwalker Default Taxonomy
function catwalker_default_taxonomy_dropdown() {
	$attribute_selected = '';
	$category_selected = ' selected="selected"';
	if ( get_option('catwalker_default_taxonomy') == 'attribute' ) {
		$attribute_selected = ' selected="selected"';
		$category_selected = '';
	}
	echo <<<EOF
<select name='catwalker_default_taxonomy'>
<option name='category' value='category'$category_selected>Categories</option>
<option name='attribute' value='attribute'$attribute_selected>Attributes</option>
<select> Choose which taxonomy the CatWalker plugin will treat as a default.
EOF;
}

//function to generate checkbox for adding Attribute data to end of content
function catwalker_post_attributes_option() {
	$checked='';
	if ( get_option('catwalker_post_attributes') == "true" ) {
		$checked = ' checked="yes"';
	}
	echo <<<EOF
<input type='checkbox' name='catwalker_post_attributes' value='true'{$checked} /> Check the box to include a list of assigned attributes at the end of each page or post. 
EOF;
}

//function to allow users to designate a CSS class that may help the attribute
//list to harmonize with various theme styles.
function catwalker_post_attributes_style() {
	$value = get_option( 'catwalker_post_attributes_class' );
	echo <<<EOF
<input type='text' maxlength='40' name='catwalker_post_attributes_class' value='$value' /> Set this class to help style your attribute list to match your theme's tag and category lists. Mileage will vary!
EOF;
}

//function to generate checkbox for custom archive sorting
function catwalker_custom_archive_sort_option() {
	$checked = '';
	if ( get_option('catwalker_custom_archive_sort') == "true" ) {
		$checked = ' checked="yes"';
	}
	echo <<<EOF
<input type='checkbox' name='catwalker_custom_archive_sort' value='true'{$checked} /> Check the box to use a custom sorting principle for term archive pages
EOF;
}

//function to generate dropdown for custom archive orderby
function catwalker_custom_archive_orderby_dropdown() {
	$title_selected = '';
	$date_selected = ' selected="selected"';
	if ( get_option('catwalker_custom_archive_orderby') == 'title' ) {
		$title_selected = ' selected="selected"';
		$date_selected = '';
	}
	echo <<<EOF
<select name='catwalker_custom_archive_orderby'>
<option name='date' value='date'$date_selected>Date</option>
<option name='title' value='title'$title_selected>Title</option>
<select> Choose whether to sort posts by date or title
EOF;
}

//function to generate dropdown for custom archive order
function catwalker_custom_archive_order_dropdown() {
	$ASC_selected = '';
	$DESC_selected = ' selected="selected"';
	if ( get_option('catwalker_custom_archive_order') == 'ASC' ) {
		$ASC_selected = ' selected="selected"';
		$DESC_selected = '';
	}
	echo <<<EOF
<select name='catwalker_custom_archive_order'>
<option name='DESC' value='DESC'$DESC_selected>Descending order</option>
<option name='ASC' value='ASC'$ASC_selected>Ascending order</option>
<select> Choose whether to sort posts in descending or ascending order
EOF;
}

//function to create textbox for number of posts on category archive pages
function catwalker_custom_archive_limit_box() {
	$value = get_option( 'catwalker_custom_archive_limit' );
	echo <<<EOF
<input type='text' maxlength='5' name='catwalker_custom_archive_limit' value='$value' /> Set a custom number of posts to list on category archive pages. Leave blank to use the default for your site.
EOF;
}

//function to generate checkbox for including list of related posts/pages
function catwalker_related_option() {
	$checked = '';
	if ( get_option('catwalker_related') == "true" ) {
		$checked = ' checked="yes"';
	}
	echo <<<EOF
<input type='checkbox' name='catwalker_related' value='true'{$checked} /> Check the box to include a list of related posts/pages at the end of each entry.
EOF;
}

//function to generate input for terms to include in related posts
function catwalker_related_include_ids_input() {
	$value = get_option( 'catwalker_related_include_ids' );
	echo <<<EOF
<input type='text' name='catwalker_related_include_ids' value='$value' /> Comma-separated list of term id's for which to include a related-posts list. Leave blank to include all.
EOF;
}

//function to generate input for child-terms to include in related posts
function catwalker_related_include_children_input() {
	$value = get_option( 'catwalker_related_include_children' );
	echo <<<EOF
<input type='text' name='catwalker_related_include_children' value='$value' /> Comma-separated list of term id's whose child-terms should be included in a related-posts list. Leave blank to include all.
EOF;
}

//function to generate input for terms to exclude from related posts
function catwalker_related_exclude_ids_input() {
	$value = get_option( 'catwalker_related_exclude_ids' );
	echo <<<EOF
<input type='text' name='catwalker_related_exclude_ids' value='$value' /> Comma-separated list of term id's for which no related-posts list should be generated. 
EOF;
}

//function to generate input for child-terms to exclude from related posts
function catwalker_related_exclude_children_input() {
	$value = get_option( 'catwalker_related_exclude_children' );
	echo <<<EOF
<input type='text' name='catwalker_related_exclude_children' value='$value' /> Comma-separated list of term id's whose child-terms should not be included in a related-posts list.
EOF;
}

/**
 *
 * Adding the settings to the Settings > Writing page
 *
 */

//add the catwalker options to the writing preferences page
function catwalker_menu() {
	add_settings_section( 'catwalker-options' ,
		'CatWalker Options' ,
		'catwalker_options' ,
		'writing'
	);
	add_settings_field( 'catwalker-custom-taxonomy' , 
		'Use Custom Taxonomy' ,
		'catwalker_custom_taxonomy_option' ,
		'writing' ,
		'catwalker-options' 
	);
	add_settings_field( 'catwalker-default-taxonomy' ,
		'Choose Default Taxonomy' ,
		'catwalker_default_taxonomy_dropdown' ,
		'writing' ,
		'catwalker-options' 
	);
	add_settings_field( 'catwalker_post_attributes' ,
		'Append Post/Page Attributes' ,
		'catwalker_post_attributes_option' ,
		'writing' ,
		'catwalker-options'
	);
	add_settings_field( 'catwalker_post_attributes_class' ,
		'CSS class for post attributes list' ,
		'catwalker_post_attributes_style' ,
		'writing' ,
		'catwalker-options'
	);
	add_settings_field( 'catwalker_custom_archive_sort' ,
		'Custom sort for archive pages' ,
		'catwalker_custom_archive_sort_option' ,
		'writing' ,
		'catwalker-options'
	);
	add_settings_field( 'catwalker_custom_archive_orderby' ,
		'Order posts by' ,
		'catwalker_custom_archive_orderby_dropdown' ,
		'writing' ,
		'catwalker-options'
	);
	add_settings_field( 'catwalker_custom_archive_order' ,
		'Descending or Ascending' ,
		'catwalker_custom_archive_order_dropdown' ,
		'writing' ,
		'catwalker-options'
	);
	add_settings_field( 'catwalker_custom_archive_limit' ,
		'Number of posts' ,
		'catwalker_custom_archive_limit_box' ,
		'writing' ,
		'catwalker-options'
	);
	add_settings_field( 'catwalker_related' ,
		'Include list of related items' ,
		'catwalker_related_option' ,
		'writing' ,
		'catwalker-options'
	);
	add_settings_field( 'catwalker_related_include_ids' ,
		'Include related items only for specific terms' ,
		'catwalker_related_include_ids_input' ,
		'writing' ,
		'catwalker-options'
	);
	add_settings_field( 'catwalker_related_include_children' ,
		'Include related items only for children of' ,
		'catwalker_related_include_children_input' ,
		'writing' ,
		'catwalker-options'
	);
	add_settings_field( 'catwalker_related_exclude_ids' ,
		'Exclude related items for specific terms' ,
		'catwalker_related_exclude_ids_input' ,
		'writing' ,
		'catwalker-options'
	);
	add_settings_field( 'catwalker_related_exclude_children' ,
		'Exclude related items for children of' ,
		'catwalker_related_exclude_children_input' ,
		'writing' ,
		'catwalker-options'
	);

	//register the settings options
	register_setting( 'writing' , 'catwalker_custom_taxonomy' , 'catwalker_sanitize_checkbox' );
	register_setting( 'writing' , 'catwalker_default_taxonomy' , 'catwalker_sanitize_default_taxonomy' );
	register_setting( 'writing' , 'catwalker_post_attributes' , 'catwalker_sanitize_checkbox' );
	register_setting( 'writing' , 'catwalker_post_attributes_class' , 'catwalker_sanitize_css_class' );
	register_setting( 'writing' , 'catwalker_custom_archive_sort' , 'catwalker_sanitize_checkbox' );
	register_setting( 'writing' , 'catwalker_custom_archive_orderby' , 'catwalker_sanitize_orderby_dropdown' );
	register_setting( 'writing' , 'catwalker_custom_archive_order' , 'catwalker_sanitize_order_dropdown' );
	register_setting( 'writing' , 'catwalker_custom_archive_limit' , 'catwalker_sanitize_number' );
	register_setting( 'writing' , 'catwalker_related' , 'catwalker_sanitize_checkbox' );
	register_setting( 'writing' , 'catwalker_related_include_ids' , 'catwalker_sanitize_commalist' );
	register_setting( 'writing' , 'catwalker_related_include_children' , 'catwalker_sanitize_commalist' );
	register_setting( 'writing' , 'catwalker_related_exclude_ids' , 'catwalker_sanitize_commalist' );
	register_setting( 'writing' , 'catwalker_related_exclude_children' , 'catwalker_sanitize_commalist' );
}

//Hook to add the custom options 
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
	if ( esc_html( $_GET['type'] == 'crosscat' ) && !(in_the_loop()) ) {

		//grab some global variables 
		global $wp_rewrite;
		global $wp_query;

		// get the URL for the site
		$url = get_bloginfo('url');
		// $plus will add a "plus-sign" or not as needed
		$plus = '';
		// get the id's for the categories/attributes from the query string
		$cat1id = esc_html( $_GET['cat1'] );
		$cat2id = esc_html( $_GET['cat2'] );
		// get the taxonomy from the query string
		$taxonomy = esc_html( $_GET['tax'] );
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

		//if permalinks are being used
		if ( $wp_rewrite->using_permalinks() ) {
			if ( $taxonomy == 'category' ) {
				$permastruct = $wp_rewrite->get_category_permastruct();
			}
			else {
				$permastruct = $wp_rewrite->get_extra_permastruct('attribute');
			}
			$request = preg_replace( ':%[^%]*%:' , "{$cat1slug}{$plus}{$cat2slug}" , $permastruct );
			$crosscat_url = "{$url}{$request}";
			wp_redirect( $crosscat_url );
			exit;
		}

		//or if permalinks are not in use
		else {
			$ccat_query = new crosscat_query( $cat1id , $cat2id , $taxonomy );
			$wp_query->query_vars['tax_query'] = $ccat_query->cc_query['tax_query'];
		}
	}
}

// add the action hook for the redirect
add_action( 'pre_get_posts' , 'cc_form' );

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
				'taxonomy'           => get_option('catwalker_default_taxonomy'),
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
				'taxonomy'     => get_option('catwalker_default_taxonomy'),
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
				'field'          => 'term_id',
				'order'          => 'ASC',
				'orderby'        => 'title',
				'posts_per_page' => '-1', //shows all by default
				'taxonomy'       => get_option('catwalker_default_taxonomy'),
				'terms'          => '', //will find union of multiple
			),
			$atts
		)
	);

	//return the contents
	//create internal query
	global $post;
	$category_posts_query = new catwalker_list(
		$post->ID ,
		$order ,
		$orderby ,
		$posts_per_page ,
		$field ,
		$taxonomy ,
		$terms
	);
	$content = $category_posts_query->post_list;

	return $content;
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
		$dd_hide_empty = ( $instance['dd_hide_empty'] != 0 ) ? '1' : $instance['dd_hide_empty'];
		$child_of1 = empty($instance['child_of1']) ? '0' : $instance['child_of1'];
		$child_of2 = empty($instance['child_of2']) ? '0' : $instance['child_of2'];
		$taxonomy1 = empty($instance['taxonomy1']) ? get_option('catwalker_default_taxonomy') : $instance['taxonomy1'];
		$taxonomy2 = empty($instance['taxonomy2']) ? get_option('catwalker_default_taxonomy') : $instance['taxonomy2'];
		$dropdownOneArgs = array( 
			'id'               => 'crosscat-dd1',
			'name'             => 'cat1',
			'hierarchical'     => 1,
			'hide_empty'       => $dd_hide_empty,
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
			'hide_empty'       => $dd_hide_empty,
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
		echo "<input type='hidden' name='type' value='crosscat' />\n";
		echo "<input type='hidden' name='tax' value='{$taxonomy1}' />\n";
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
		$instance['dd_hide_empty'] = $new_instance['dd_hide_empty'];

		return $instance;
	}
	
	/**
	* Creates the edit form for the widget.
	*
	*/
	function form($instance){
		//Defaults
		$instance = wp_parse_args( (array) $instance, array(
			'title'         => '', 
			'dd_hide_empty' => '1',
			'label1'        => 'Select a category', 
			'child_of1'     => '0' , 
			'label2'        => 'Select another',
			'child_of2'     => '0' , 
			'taxonomy1'     => get_option('catwalker_default_taxonomy'),
			'cat1selected'  => 'selected',
			'att1selected'  => '',
			'taxonomy2'     => get_option('catwalker_default_taxonomy'),
			'cat2selected'  => 'selected',
			'att2selected'  => '',
			) 
		);
		
		//translatable expressions
		$title_label = __('Title: ');
		$taxonomy_label = __('Taxonomy: ');
		$category1_label = __('Category 1: ');
		$category2_label = __('Category 2: ');
		$label_label = __('Label: ');
		$child_of_label = __('Child of: ');
		$dd_hide_empty_label = __('Hide empty categories: ');


		$title = htmlspecialchars($instance['title']);
		$dd_hide_empty = ( $instance['dd_hide_empty'] == '1' ) ? '1' : '0' ;
		$dd_hide_empty_checked = ( $dd_hide_empty == 1 ) ? ' checked="checked"' : '';
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
			'hide_empty'      => $dd_hide_empty ,
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
			'hide_empty'      => $dd_hide_empty ,
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
<input type="hidden" name="{$this->get_field_name('dd_hide_empty')}" value="0" />
<label for="{$this->get_field_id('dd_hide_empty')}">{$dd_hide_empty_label}</label>
<input id="{$this->get_field_id('dd_hide_empty')}" name="{$this->get_field_name('dd_hide_empty')}" type="checkbox" value="1"{$dd_hide_empty_checked} />
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
