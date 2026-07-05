<?php
	
	if( !function_exists('gdlr_core_portfolio_get_single_nav') ){
		function gdlr_core_portfolio_get_single_nav( $style = 'style-1' ){

			$same_term = apply_filters('gdlr_core_portfolio_single_nav_same_term', 'enable');
			$same_term = ($same_term == 'enable')? true: false;

			$middle_link = '';
			if( $style == 'style-2' ){
				$prev_icon = 'fa fa-long-arrow-left';
				$prev_text = esc_html__('Previous Post', 'goodlayers-core-portfolio');
				$next_icon = 'fa fa-long-arrow-right';
				$next_text = esc_html__('Next Post', 'goodlayers-core-portfolio');

				$middle_link_url = apply_filters('gdlr_core_portfolio_single_nav_middle_link', '');
				if( !empty($middle_link_url) ){
					$middle_link .= '<a class="gdlr-core-portfolio-single-nav-middle" href="' . esc_attr($middle_link_url) . '" >';
					$middle_link .= '<i class="fa fa-th" ></i>';
					$middle_link .= '</a>';
				} 
				
			}else{
				$prev_icon = 'arrow_left';
				$prev_text = esc_html__('Prev', 'goodlayers-core-portfolio');
				$next_icon = 'arrow_right';
				$next_text = esc_html__('Next', 'goodlayers-core-portfolio');
			}
			
			// prev - next portfolio navigation
			$prev_post = get_previous_post_link(
				'<span class="gdlr-core-portfolio-single-nav gdlr-core-portfolio-single-nav-left">%link</span>',
				'<i class="' . esc_attr($prev_icon) . '" ></i><span class="gdlr-core-portfolio-text" >' . $prev_text . '</span>',
				$same_term, '', 'portfolio_tag'
			);
			$next_post = get_next_post_link(
				'<span class="gdlr-core-portfolio-single-nav gdlr-core-portfolio-single-nav-right">%link</span>',
				'<span class="gdlr-core-portfolio-text" >' . $next_text . '</span><i class="' . esc_attr($next_icon) . '" ></i>',
				$same_term, '', 'portfolio_tag'
			);

			if( !empty($prev_post) || !empty($next_post) ){
				echo '<div class="gdlr-core-portfolio-single-nav-wrap gdlr-core-' . esc_attr($style) . '">';
				echo '<div class="gdlr-core-container">';
				echo '<div class="gdlr-core-portfolio-single-nav-area gdlr-core-item-pdlr clearfix" >' . $prev_post . $middle_link . $next_post . '</div>';
				echo '</div>';
				echo '</div>';
			}
		}
	}