<?php

class catwalker_list{

	/*** public properties ***/
	public $post_list;
	public $list_count;

	/*** the constructor ***/
	public function __construct(
		$postID , /* id of the current post */
	 	$order , /* ASC or DESC */
		$orderby , /* title, date, modified, rand */
		$posts_per_page , /* -1 for unlimited */
		$field , /* id or slug */
		$taxonomy , /* category or attribute */
		$terms /* id or slug of the current category or attribute */
	) {
		//make $terms into an array
		$terms_array = explode( ',' , $terms );

		$internal_query = new WP_Query(
			array(
				'order'          => $order,
				'orderby'        => $orderby,
				'posts_per_page' => $posts_per_page,
				'tax_query' => array(
					array(
						'field'    => $field,
						'taxonomy' => $taxonomy,
						'terms'    => $terms_array,
					)
				)
			)
		);

		$list = "<ul class='catwalker-post-list'>\n";
		while( $internal_query->have_posts() ) {
			$internal_query->the_post();
			$internal_postID = $internal_query->post->ID;
			//skip the global post unless it fills a posts-per-page setting
			if ( $postID == $internal_postID && !( $posts_per_page > 0 )) { 
				continue; 
			}
			//create the link
			$post_title = $internal_query->post->post_title;
			$post_permalink = get_permalink($internal_postID);
			$list .= "<li><a href='$post_permalink'>$post_title</a></li>\n";
		}
		$list .= "</ul>\n";

		wp_reset_postdata();

		$this->post_list = $list;
		$this->list_count = $internal_query->post_count;

	}

}

?>
