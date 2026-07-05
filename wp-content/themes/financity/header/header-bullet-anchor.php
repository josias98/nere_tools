<?php
	/* a template for displaying the header social network */

	$post_option = financity_get_post_option(get_the_ID());
	if( !empty($post_option['bullet-anchor']) ){

		echo '<div class="financity-bullet-anchor" id="financity-bullet-anchor" >';
		echo '<a class="financity-bullet-anchor-link current-menu-item" href="' . get_permalink() . '" ></a>';
		foreach( $post_option['bullet-anchor'] as $anchor ){
			echo '<a class="financity-bullet-anchor-link" href="' . esc_url($anchor['title']) . '" ></a>';
		}
		echo '</div>';
	}