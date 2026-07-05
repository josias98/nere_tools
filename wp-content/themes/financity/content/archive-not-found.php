<?php
/**
 * The template for displaying archive not found
 */

	echo '<div class="financity-not-found-wrap" id="financity-full-no-header-wrap" >';
	echo '<div class="financity-not-found-background" ></div>';
	echo '<div class="financity-not-found-container financity-container">';
	echo '<div class="financity-header-transparent-substitute" ></div>';
	
	echo '<div class="financity-not-found-content financity-item-pdlr">';
	echo '<h1 class="financity-not-found-head" >' . esc_html__('Not Found', 'financity') . '</h1>';
	echo '<div class="financity-not-found-caption" >' . esc_html__('Nothing matched your search criteria. Please try again with different keywords.', 'financity') . '</div>';

	echo '<form role="search" method="get" class="search-form" action="' . esc_url(home_url('/')) . '">';
	echo '<input type="text" class="search-field financity-title-font" placeholder="' . esc_html__('Type Keywords...', 'financity') . '" value="" name="s">';
	echo '<div class="financity-top-search-submit"><i class="fa fa-search" ></i></div>';
	echo '<input type="submit" class="search-submit" value="Search">';
	echo '</form>';
	echo '<div class="financity-not-found-back-to-home" ><a href="' . esc_url(home_url('/')) . '" >' . esc_html__('Or Back To Homepage', 'financity') . '</a></div>';
	echo '</div>'; // financity-not-found-content

	echo '</div>'; // financity-not-found-container
	echo '</div>'; // financity-not-found-wrap