<?php
/**
 * The template part for displaying blog archive
 */

	global $wp_query;

	$settings = array(
		'query' => $wp_query,
		'blog-style' => financity_get_option('general', 'archive-blog-style', 'blog-full'),
		'blog-full-alignment' => financity_get_option('general', 'archive-blog-full-alignment', 'left'),
		'thumbnail-size' => financity_get_option('general', 'archive-thumbnail-size', 'full'),
		'show-thumbnail' => financity_get_option('general', 'archive-show-thumbnail', 'enable'),
		'column-size' => financity_get_option('general', 'archive-column-size', 20),
		'excerpt' => financity_get_option('general', 'archive-excerpt', 'specify-number'),
		'excerpt-number' => financity_get_option('general', 'archive-excerpt-number', 55),
		'blog-date-feature' => financity_get_option('general', 'archive-date-feature', 'enable'),
		'meta-option' => financity_get_option('general', 'archive-meta-option', array()),
		'show-read-more' => financity_get_option('general', 'archive-show-read-more', 'enable'),

		'paged' => (get_query_var('paged'))? get_query_var('paged') : 1,
		'pagination' => 'page',
		'pagination-style' => financity_get_option('general', 'pagination-style', 'round'),
		'pagination-align' => financity_get_option('general', 'pagination-align', 'right'),

	);

	echo '<div class="financity-content-area" >';
	if( is_category() ){
		$tax_description = category_description();
		if( !empty($tax_description) ){
			echo '<div class="financity-archive-taxonomy-description financity-item-pdlr" >' . $tax_description . '</div>';
		}
	}else if( is_tag() ){
		$tax_description = term_description(NULL, 'post_tag');
		if( !empty($tax_description) ){
			echo '<div class="financity-archive-taxonomy-description financity-item-pdlr" >' . $tax_description . '</div>';
		}
	}

	echo gdlr_core_pb_element_blog::get_content($settings);
	echo '</div>'; // financity-content-area