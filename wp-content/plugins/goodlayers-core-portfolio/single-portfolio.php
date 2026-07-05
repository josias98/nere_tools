<?php
	/**
	 * The template for displaying all single portfolio posttype
	 */

get_header();
	
	while( have_posts() ){ the_post();

		global $post; 
		$post_option = get_post_meta(get_the_ID(), 'gdlr-core-page-option', true);
		$show_content = (empty($post_option['show-content']) || $post_option['show-content'] == 'enable')? true: false;

		if( ($show_content && trim($post->post_content) != "") || post_password_required() ){
			echo '<div class="gdlr-core-outer-content-wrap">';
			echo '<div class="gdlr-core-content-container gdlr-core-container">';
			echo '<div class="gdlr-core-content-area gdlr-core-item-pdlr" >';
			the_content();
			echo '</div>'; // gdlr-core-content-area
			echo '</div>';
			echo '</div>';
		}
		
		if( !post_password_required() ){
			do_action('gdlr_core_print_page_builder');
		}

		// portfolio nav
		$single_portfolio_nav = apply_filters('gdlr_core_portfolio_single_nav', 'enable');
		if( $single_portfolio_nav == 'style-2' ){
			echo gdlr_core_portfolio_get_single_nav('style-2');
		}

		// related portfolio
		$related_portfolio_args = apply_filters('gdlr_core_portfolio_single_related', array(
			'portfolio-style' => 'grid',
			'num-fetch' => 4,
			'thumbnail-size' => 'medium',
			'excerpt' => 'specify-number',
			'excerpt-number' => 20,
			'column-size' => 15,
			'lightbox-group' => 'single-related-portfolio'
		));

		if( !empty($related_portfolio_args) ){

			if( empty($related_portfolio_args['hover']) ){
				if( $related_portfolio_args['portfolio-style'] == 'grid' ){
					$related_portfolio_args['hover'] = 'icon-title';
				}else if( $related_portfolio_args['portfolio-style'] == 'modern' ){
					$related_portfolio_args['hover'] = 'icon';
				}
			}

			$related_portfolio_args['hover-info'] = empty($related_portfolio_args['hover'])? array(): explode('-', $related_portfolio_args['hover']);

			// query related portfolio
			$args = array('post_type' => 'portfolio', 'suppress_filters' => false);
			$args['posts_per_page'] = $related_portfolio_args['num-fetch'];
			$args['post__not_in'] = array(get_the_ID());

			$related_terms = get_the_terms(get_the_ID(), 'portfolio_tag');
			$related_tags = array();
			if( !empty($related_terms) ){
				foreach( $related_terms as $term ){
					$related_tags[] = $term->term_id;
				}
				$args['tax_query'] = array(array('terms'=>$related_tags, 'taxonomy'=>'portfolio_tag', 'field'=>'id'));
			} 
			$query = new WP_Query($args);

			// print item
			if( $query->have_posts() ){

				$portfolio_style = new gdlr_core_portfolio_style();

				echo '<div class="gdlr-core-portfolio-single-related gdlr-core-style-' . esc_attr($related_portfolio_args['portfolio-style']) . '">';
				echo '<div class="gdlr-core-container">';
				echo '<h3 class="gdlr-core-portfolio-single-related-head gdlr-core-item-pdlr">' . esc_html__('Related Projects', 'goodlayers-core-portfolio') . '</h3>';

				$column_sum = 0;
				echo '<div class="gdlr-core-portfolio-item-holder clearfix" >';
				while( $query->have_posts() ){ $query->the_post();

					$additional_class  = ' gdlr-core-item-pdlr';
					$additional_class .= ' gdlr-core-column-' . $related_portfolio_args['column-size'];
					if( $related_portfolio_args['portfolio-style'] == 'modern' ){
						$additional_class .= ' gdlr-core-item-mgb';
					}

					if( $column_sum == 0 || $column_sum + intval($related_portfolio_args['column-size']) > 60 ){
						$column_sum = intval($related_portfolio_args['column-size']);
						$additional_class .= ' gdlr-core-column-first';
					}else{
						$column_sum += intval($related_portfolio_args['column-size']);
					}
					echo '<div class="gdlr-core-item-list ' . esc_attr($additional_class) . '" >';
					echo $portfolio_style->get_content($related_portfolio_args);
					echo '</div>';
				}
				wp_reset_postdata();
				echo '</div>'; // gdlr-core-portfolio-item-holder

				echo '</div>'; // gdlr-core-container 
				echo '</div>'; // gdlr-core-portfolio-single-related
			}
		}

		// portfolio nav
		if( $single_portfolio_nav == 'enable' ){
			echo gdlr_core_portfolio_get_single_nav('style-1');
		}

	}

get_footer(); 

?>