<?php
/**
 * The template part for displaying single post title
 */

	echo '<header class="financity-single-article-head clearfix" >';
	$blog_date = financity_get_option('general', 'blog-date-feature', '');

	if( empty($blog_date) || $blog_date == 'enable' ){
		echo '<div class="financity-single-article-date-wrapper">';
		echo '<div class="financity-single-article-date-day">' .  get_the_time('d') . '</div>';
		echo '<div class="financity-single-article-date-month">' . get_the_time('M') . '</div>';

		$blog_date_year = financity_get_option('general', 'blog-date-feature-year', '');
		if( !empty($blog_date_year) && $blog_date_year == 'enable' ){
			echo '<div class="financity-single-article-date-year">' . get_the_time('Y') . '</div>';
		} 
		echo '</div>';
	}

	echo '<div class="financity-single-article-head-right">';
	if( is_single() ){
		echo '<h1 class="financity-single-article-title">' . get_the_title() . '</h1>';
	}else{
		echo '<h3 class="financity-single-article-title"><a href="' . get_permalink() . '" >' . get_the_title() . '</a></h3>';
	}

	$single_blog_meta = financity_get_option('general', 'meta-option', '');
	if( empty($blog_date) && empty($single_blog_meta) ){
		$single_blog_meta = array('author', 'category', 'tag', 'comment-number');
	}
	echo financity_get_blog_info(array(
		'display' => $single_blog_meta,
		'wrapper' => true
	));
	echo '</div>';
	echo '</header>';