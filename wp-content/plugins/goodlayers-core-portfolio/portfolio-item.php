<?php
	/*	
	*	Goodlayers Portfolio Item
	*/
	
	if( !class_exists('gdlr_core_portfolio_item') ){
		class gdlr_core_portfolio_item{
			
			var $settings = '';
			
			// init the variable
			function __construct( $settings = array() ){
				
				$this->settings = wp_parse_args($settings, array(
					'category' => '', 
					'tag' => '', 
					'num-fetch' => '9', 
					'layout' => 'fitrows',
					'thumbnail-size' => 'full', 
					'orderby' => 'date', 
					'order' => 'desc',
					'portfolio-style' => 'modern', 
					'hover' => 'title-icon', 
					'hover-info' => array('title','icon'), 
					'has-column' => 'no',
					'no-space' => 'no',
					'excerpt' => 'specify-number', 
					'excerpt-number' => 55, 
					'column-size' => 20,
					'filterer' => 'none',
					'filterer-align' => 'center',
					'pagination' => 'none'
				));

				$this->settings['lightbox-group'] = gdlr_core_image_group_id();
				if( $this->settings['orderby'] == 'rand' ){
					$this->settings['orderby'] = 'RAND(' . rand(1, 1000) . ')';
				} 
				
			}
			
			// get the content of the portfolio item
			function get_content(){
				
				if( !empty($this->settings['column-size']) ){
					gdlr_core_set_container_multiplier(intval($this->settings['column-size']) / 60, false);
				}

				$ret = '';
				if( !empty($this->settings['query']) ){
					$query = $this->settings['query'];
				}else{
					$query = $this->get_portfolio_query();
				}
				
				// carousel style
				if( $this->settings['layout'] == 'carousel' ){
					$slides = array();
					$column_no = 60 / intval($this->settings['column-size']);

					$flex_atts = array(
						'carousel' => true,
						'margin' => empty($this->settings['carousel-item-margin'])? '': $this->settings['carousel-item-margin'],
						'overflow' => empty($this->settings['overflow-overflow'])? '': $this->settings['carousel-overflow'],
						'column' => $column_no,
						'start-at' => empty($this->settings['carousel-start-at'])? '': $this->settings['carousel-start-at'],
						'nav-type' => empty($this->settings['flexslider-nav-type'])? '': $this->settings['flexslider-nav-type'],
						'move' => empty($this->settings['carousel-scrolling-item-amount'])? '': $this->settings['carousel-scrolling-item-amount'],
						'navigation' => empty($this->settings['carousel-navigation'])? 'navigation': $this->settings['carousel-navigation'],
						'navigation-on-hover' => empty($this->settings['carousel-navigation-show-on-hover'])? 'disable': $this->settings['carousel-navigation-show-on-hover'],
						'navigation-align' => empty($this->settings['carousel-navigation-align'])? '': $this->settings['carousel-navigation-align'],
						'navigation-size' => empty($this->settings['carousel-navigation-size'])? '': $this->settings['carousel-navigation-size'],
						'navigation-icon-color' => empty($this->settings['carousel-navigation-icon-color'])? '': $this->settings['carousel-navigation-icon-color'],
						'navigation-icon-background' => empty($this->settings['carousel-navigation-icon-bg'])? '': $this->settings['carousel-navigation-icon-bg'],
						'navigation-icon-padding' => empty($this->settings['carousel-navigation-icon-padding'])? '': $this->settings['carousel-navigation-icon-padding'],
						'navigation-icon-radius' => empty($this->settings['carousel-navigation-icon-radius'])? '': $this->settings['carousel-navigation-icon-radius'],
						'navigation-margin' => empty($this->settings['carousel-navigation-margin'])? '': $this->settings['carousel-navigation-margin'],
						'navigation-icon-margin' => empty($this->settings['carousel-navigation-icon-margin'])? '': $this->settings['carousel-navigation-icon-margin'],
						'navigation-left-icon' => empty($this->settings['carousel-navigation-left-icon'])? '': $this->settings['carousel-navigation-left-icon'],
						'navigation-right-icon' => empty($this->settings['carousel-navigation-right-icon'])? '': $this->settings['carousel-navigation-right-icon'],
						'bullet-style' => empty($this->settings['carousel-bullet-style'])? '': $this->settings['carousel-bullet-style'],
						'controls-top-margin' => empty($this->settings['carousel-bullet-top-margin'])? '': $this->settings['carousel-bullet-top-margin'],						
						'nav-parent' => 'gdlr-core-portfolio-item',
						'disable-autoslide' => (empty($this->settings['carousel-autoslide']) || $this->settings['carousel-autoslide'] == 'enable')? '': true,
						'mglr' => ($this->settings['no-space'] == 'yes'? false: true)
					);

					gdlr_core_setup_admin_postdata();

					$portfolio_style = new gdlr_core_portfolio_style();
					while($query->have_posts()){ $query->the_post();
						$slides[] = $portfolio_style->get_content( $this->settings );
					} // while

					$ret .= gdlr_core_get_flexslider($slides, $flex_atts);

					wp_reset_postdata();
					gdlr_core_reset_admin_postdata();

				// fitrows style
				}else{
					
					// filterer
					if( !empty($this->settings['filterer']) && $this->settings['filterer'] != 'none' ){

						$filter_style = $this->settings['filterer'];
						if( $this->settings['filterer'] == 'text-slide' ){
							$filter_style = 'text';
							$this->settings['filterer-slide-bar'] = true;
						}else if( $this->settings['filterer'] == 'text-slide2' ){
							$filter_style = 'text gdlr-core-round-slide-bar';
							$this->settings['filterer-slide-bar'] = true;
						}
						
						$extra_class  = ' gdlr-core-style-' . $filter_style;
						$extra_class .= ($this->settings['no-space'] == 'yes')? '': ' gdlr-core-item-pdlr';
						$extra_class .= empty($this->settings['filterer-align'])? '': ' gdlr-core-' . $this->settings['filterer-align'] . '-align';
						$extra_class .= apply_filters('gdlr_core_portfolio_filterer_class', '', $this->settings);

						$filterer_atts = array(
							'font-size' => empty($this->settings['filter-font-size'])? '': $this->settings['filter-font-size'],
							'font-weight' => empty($this->settings['filter-font-weight'])? '': $this->settings['filter-font-weight'],
							'letter-spacing' => empty($this->settings['filter-letter-spacing'])? '': $this->settings['filter-letter-spacing'],
							'text-transform' => (empty($this->settings['filter-text-transform']) || $this->settings['filter-text-transform'] == 'uppercase')? '': $this->settings['filter-text-transform'],
						);

						$ret .= gdlr_core_get_ajax_filterer('portfolio', 'portfolio_category', $this->settings, 'gdlr-core-portfolio-item-holder', $extra_class, $filterer_atts);
					}

					gdlr_core_setup_admin_postdata();
					
					// portfolio item
					$ret .= '<div class="gdlr-core-portfolio-item-holder gdlr-core-js-2 clearfix" data-layout="' . $this->settings['layout'] . '" >';
					if( $this->settings['portfolio-style'] == 'fixed-metro' ){
						$ret .= $this->get_portfolio_fixed_metro($query);
					}else{
						$ret .= $this->get_portfolio_grid_content($query);
					}
					$ret .= '</div>';

					wp_reset_postdata();
					gdlr_core_reset_admin_postdata();

					// pagination
					if( $this->settings['pagination'] != 'none' ){
						$extra_class = ($this->settings['no-space'] == 'yes')? '': 'gdlr-core-item-pdlr';

						if( $this->settings['pagination'] == 'page' ){
							if( !empty($this->settings['filterer']) && $this->settings['filterer'] != 'none' ){
								$ret .= gdlr_core_get_ajax_pagination('portfolio', $this->settings, $query->max_num_pages, 'gdlr-core-portfolio-item-holder', $extra_class);
							}else{
								$ret .= gdlr_core_get_pagination($query->max_num_pages, $this->settings, $extra_class);
							}
						}else if( $this->settings['pagination'] == 'load-more' ){
							$paged = empty($query->query['paged'])? 2: intval($query->query['paged']) + 1;
							$ret .= gdlr_core_get_ajax_load_more('portfolio', $this->settings, $paged, $query->max_num_pages, 'gdlr-core-portfolio-item-holder', $extra_class);
						}
					}
				}

				gdlr_core_set_container_multiplier(1, false);

				return $ret;
			}

			// get content of non carousel portfolio item
			function get_portfolio_grid_content( $query ){

				$ret = '';
				$column_sum = 0;
				$portfolio_style = new gdlr_core_portfolio_style();
				while($query->have_posts()){ $query->the_post();

					$args = $this->settings;

					if( $this->settings['has-column'] == 'yes' ){
						$additional_class  = ($this->settings['no-space'] == 'yes')? '': ' gdlr-core-item-pdlr';
						$additional_class .= in_array($this->settings['portfolio-style'], array('modern', 'modern-desc', 'metro', 'grid2'))? ' gdlr-core-item-mgb': '';
						if( in_array($this->settings['portfolio-style'], array('metro', 'metro-no-space')) ){
							$portfolio_info = get_post_meta(get_the_ID(), 'gdlr-core-page-option', true);
							
							if( empty($portfolio_info['metro-column-size']) || $portfolio_info['metro-column-size'] == 'default' ){
								if( !empty($this->settings['column-size']) ){
									$additional_class .= ' gdlr-core-column-' . $this->settings['column-size'];	
								}
							}else{
								$additional_class .= ' gdlr-core-column-' . $portfolio_info['metro-column-size'];
							}

							if( !empty($portfolio_info['metro-thumbnail-size']) && $portfolio_info['metro-thumbnail-size'] != 'default' ){
								$args['thumbnail-size'] = $portfolio_info['metro-thumbnail-size'];
							}
							
						}else if( !empty($this->settings['column-size']) ){
							$additional_class .= ' gdlr-core-column-' . $this->settings['column-size'];
						}

						if( $column_sum == 0 || $column_sum + intval($this->settings['column-size']) > 60 ){
							$column_sum = intval($this->settings['column-size']);
							$additional_class .= ' gdlr-core-column-first';
						}else{
							$column_sum += intval($this->settings['column-size']);
						}

						$ret .= '<div class="gdlr-core-item-list ' . esc_attr($additional_class) . '" >';
					}

					$ret .= $portfolio_style->get_content( $args );

					if( $this->settings['has-column'] == 'yes' ){
						$ret .= '</div>';
					}
				} // while
				
				return $ret;
			}
			function get_portfolio_fixed_metro( $query ){

				$ret = '';
				$count = 0;
				$portfolio_style = new gdlr_core_portfolio_style();
				while($query->have_posts()){ $query->the_post(); 
					$count = ($count % 8) + 1;

					$column_class = 'gdlr-core-portfolio-fixed-metro-item';
					if( in_array($count, array(1, 2, 3, 6, 7, 8)) ){
						$column_class .= ' gdlr-core-column-30';
					}else{ 
						$column_class .= ' gdlr-core-column-60';
					}
					if( in_array($count, array(1, 2, 4, 6)) ){
						$column_class .= ' gdlr-core-column-first';
					}
					if( in_array($count, array(4, 5)) ){
						$column_class .= ' gdlr-core-half-height';
					}
					if( in_array($count, array(1, 8)) ){
						$column_class .= ' gdlr-core-item-list';
					}

					if( $count == 2 ){
						$ret .= '<div class="gdlr-core-column-30 gdlr-core-item-list" >';
					}else if( $count == 5 ){
						$ret .= '<div class="gdlr-core-column-30 gdlr-core-column-first gdlr-core-item-list" >';
					}

					$ret .= '<div class="' . esc_attr($column_class) . '" >';
					$ret .= $portfolio_style->get_content($this->settings);
					$ret .= '</div>';

					if( in_array($count, array(4, 7)) ){
						$ret .= '</div>';
					}

				} // while

				if( in_array($count, array(2, 3, 5, 6)) ){
					$ret .= '</div>';
				}
				
				return $ret;
			}
			
			// query the post
			function get_portfolio_query(){
				
				$args = array( 'post_type' => 'portfolio', 'post_status' => 'publish', 'suppress_filters' => false );
				
				// category - tag selection
				if( !empty($this->settings['category']) || !empty($this->settings['tag']) ){
					$args['tax_query'] = array(
						'relation' => empty($this->settings['relation'])? 'OR': $this->settings['relation']
					);
					
					if( !empty($this->settings['category']) ){
						array_push($args['tax_query'], array('terms'=>$this->settings['category'], 'taxonomy'=>'portfolio_category', 'field'=>'slug'));
					}
					if( !empty($this->settings['tag']) ){
						array_push($args['tax_query'], array('terms'=>$this->settings['tag'], 'taxonomy'=>'portfolio_tag', 'field'=>'slug'));
					}
				}
				
				// pagination
				if( empty($this->settings['paged']) ){
					$args['paged'] = (get_query_var('paged'))? get_query_var('paged') : get_query_var('page');
					$args['paged'] = empty($args['paged'])? 1: $args['paged'];
				}else{
					$args['paged'] = $this->settings['paged'];
				}
				$this->settings['paged'] = $args['paged'];
				
				// variable
				$args['posts_per_page'] = $this->settings['num-fetch'];
				$args['orderby'] = $this->settings['orderby'];
				$args['order'] = $this->settings['order'];
				
				return new WP_Query( $args );
			}
			
		} // gdlr_core_portfolio_item
	} // class_exists
	
	add_action('wp_ajax_gdlr_core_portfolio_ajax', 'gdlr_core_portfolio_ajax');
	add_action('wp_ajax_nopriv_gdlr_core_portfolio_ajax', 'gdlr_core_portfolio_ajax');
	if( !function_exists('gdlr_core_portfolio_ajax') ){
		function gdlr_core_portfolio_ajax(){

			if( !empty($_POST['settings']) ){

				$settings = $_POST['settings'];
				if( !empty($_POST['option']['name']) && !empty($_POST['option']['value']) ){	
					if( in_array($_POST['option']['name'], array('paged', 'category')) ){ 
						$settings[$_POST['option']['name']] = $_POST['option']['value'];

						if( $_POST['option']['name'] == 'category' ){
							$settings['paged'] = 1;
							unset($settings['tag']);
						}
					}
				}else{
					$settings['paged'] = 1;
				}

				$portfolio_item = new gdlr_core_portfolio_item($settings);
				$query = $portfolio_item->get_portfolio_query();	

				if( $settings['portfolio-style'] == 'fixed-metro' ){
					$content = $portfolio_item->get_portfolio_fixed_metro($query);
				}else{
					$content = $portfolio_item->get_portfolio_grid_content($query);
				}

				$ret = array(
					'status'=> 'success',
					'content'=> $content
				);
				if( !empty($settings['pagination']) && $settings['pagination'] != 'none' ){
					$extra_class = ($settings['no-space'] == 'yes')? '': 'gdlr-core-item-pdlr';

					// always change the load more button
					if( $settings['pagination'] == 'load-more' ){
						$paged = empty($query->query['paged'])? 2: intval($query->query['paged']) + 1;
						$ret['load_more'] = gdlr_core_get_ajax_load_more('portfolio', $settings, $paged, $query->max_num_pages, 'gdlr-core-portfolio-item-holder', $extra_class);
						$ret['load_more'] = empty($ret['load_more'])? 'none': 2 . $ret['load_more'];

					// change pagination on category filter
					}else if( empty($_POST['option']['name']) || $_POST['option']['name'] == 'category' ){
						$ret['pagination'] = gdlr_core_get_ajax_pagination('portfolio', $settings, $query->max_num_pages, 'gdlr-core-portfolio-item-holder', $extra_class);
						$ret['pagination'] = empty($ret['pagination'])? 'none': $ret['pagination'];
					}
				} 

				die(json_encode($ret));
			}else{
				die(json_encode(array(
					'status'=> 'failed',
					'message'=> esc_html__('Settings variable is not defined.', 'goodlayers-core-portfolio')
				)));
			}

		} // gdlr_core_portfolio_ajax
	} // function_exists
	
	
	
	
	
	
	
	
	
	
	
	
	
	