<?php
/**
 * The main template file
 */

	get_header();

	echo '<div class="financity-content-container financity-container">';
	echo '<div class="financity-sidebar-style-none" >'; // for max width

	get_template_part('content/archive', 'default');

	echo '</div>'; // financity-content-area
	echo '</div>'; // financity-content-container

	get_footer(); 
