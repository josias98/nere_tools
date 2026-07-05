<?php
/**
 * The template part for displaying default archive
 */

	echo '<div class="financity-content-area financity-item-pdlr" >';

	while( have_posts() ){ the_post();

		get_template_part('content/content', 'full');
		
	} // while

	the_posts_pagination(array(
		'prev_text'          => esc_html__('Previous page', 'financity'),
		'next_text'          => esc_html__('Next page', 'financity'),
		'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__('Page', 'financity') . ' </span>',
	));

	echo '</div>'; // financity-content-area