<?php
	/**
	 * A widget that show recent portfolio ( Specified by category ).
	 */

	add_action('widgets_init', 'gdlr_core_portfolio_slider');
	if( !function_exists('gdlr_core_portfolio_slider') ){
		function gdlr_core_portfolio_slider() {
			register_widget( 'Goodlayers_Core_Portfolio_Slider_Widget' );
		}
	}

	if( !class_exists('Goodlayers_Core_Portfolio_Slider_Widget') ){
		class Goodlayers_Core_Portfolio_Slider_Widget extends WP_Widget{

			// Initialize the widget
			function __construct() {

				parent::__construct(
					'gdlr-core-portfolio-slider-widget', 
					esc_html__('Portfolio Slider Widget ( Goodlayers )', 'goodlayers-core-portfolio'), 
					array('description' => esc_html__('A widget that show latest portfolio', 'goodlayers-core-portfolio'))
				);  

			}

			// Output of the widget
			function widget( $args, $instance ) {
	
				$title = empty($instance['title'])? '': apply_filters('widget_title', $instance['title']);
				$category = empty($instance['category'])? '': $instance['category'];
				$num_fetch = empty($instance['num-fetch'])? '': $instance['num-fetch'];
				$thumbnail_size = empty($instance['thumbnail-size'])? 'full': $instance['thumbnail-size'];

				// Opening of widget
				echo gdlr_core_escape_content($args['before_widget']);
				
				// Open of title tag
				if( !empty($title) ){ 
					$title_nav = '<div class="gdlr-core-flexslider-nav gdlr-core-plain-style gdlr-core-size-widget"></div>';
					$title_nav = apply_filters('gdlr_core_widget_title_nav', $title_nav);

					echo gdlr_core_escape_content($args['before_title'] . $title . $title_nav . $args['after_title']); 
				}
					
				// Widget Content
				$query_args = array(
					'post_type' => 'portfolio', 
					'suppress_filters' => false,
					'orderby' => 'post_date',
					'order' => 'desc',
					'paged' => 1,
					'ignore_sticky_posts' => 1,
					'posts_per_page' => $num_fetch,
					'portfolio_category' => $category,
					'post__not_in' => array(get_the_ID())
				);
				$query = new WP_Query( $query_args );
				
				if($query->have_posts()){

					$slides = array();
					$flex_atts = array(
						'navigation' => 'navigation',
						'nav-parent' => 'widget'
					);
					$port_style = new gdlr_core_portfolio_style();

					while($query->have_posts()){ $query->the_post();

						$slides[] = $port_style->get_content(array(
							'portfolio-style' => 'modern',
							'thumbnail-size' => $thumbnail_size,
							'hover' => 'icon-title',
							'hover-info' => array('icon', 'title'),
							'only-thumbnail' => true
						));
					}
					wp_reset_postdata();

					echo '<div class="gdlr-core-portfolio-slider-widget-wrap clearfix">';
					echo gdlr_core_get_flexslider($slides, $flex_atts);
					echo '</div>'; // gdlr-core-portfolio-slider-wrap
				}
				wp_reset_postdata();
						
				// Closing of widget
				echo gdlr_core_escape_content($args['after_widget']);

			}

			// Widget Form
			function form( $instance ) {

				if( class_exists('gdlr_core_widget_util') ){
					gdlr_core_widget_util::get_option(array(
						'title' => array(
							'type' => 'text',
							'id' => $this->get_field_id('title'),
							'name' => $this->get_field_name('title'),
							'title' => esc_html__('Title', 'goodlayers-core-portfolio'),
							'value' => (isset($instance['title'])? $instance['title']: '')
						),
						'category' => array(
							'type' => 'combobox',
							'id' => $this->get_field_id('category'),
							'name' => $this->get_field_name('category'),
							'title' => esc_html__('Category', 'goodlayers-core-portfolio'),
							'options' => gdlr_core_get_term_list('portfolio_category', '', true),
							'value' => (isset($instance['category'])? $instance['category']: '')
						),
						'num-fetch' => array(
							'type' => 'text',
							'id' => $this->get_field_id('num-fetch'),
							'name' => $this->get_field_name('num-fetch'),
							'title' => esc_html__('Display Number', 'goodlayers-core-portfolio'), 
							'value' => (isset($instance['num-fetch'])? $instance['num-fetch']: '3')
						),
						'thumbnail-size' => array(
							'type' => 'combobox',
							'id' => $this->get_field_id('thumbnail-size'),
							'name' => $this->get_field_name('thumbnail-size'),
							'title' => esc_html__('Thumbnail Size', 'goodlayers-core-portfolio'), 
							'options' => gdlr_core_get_thumbnail_list(),
							'value' => (isset($instance['thumbnail-size'])? $instance['thumbnail-size']: 'full')
						),
					));
				}

			}
			
			// Update the widget
			function update( $new_instance, $old_instance ) {

				if( class_exists('gdlr_core_widget_util') ){
					return gdlr_core_widget_util::get_option_update($new_instance);
				}

				return $new_instance;
			}	
		} // class
	} // class_exists
?>