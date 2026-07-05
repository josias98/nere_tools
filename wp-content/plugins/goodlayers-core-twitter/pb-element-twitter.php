<?php
	/*	
	*	Goodlayers Item For Page Builder
	*/
	
	add_action('plugins_loaded', 'goodlayers_core_twitter_add_pb_element');
	if( !function_exists('goodlayers_core_twitter_add_pb_element') ){
		function goodlayers_core_twitter_add_pb_element(){
			if( class_exists('gdlr_core_page_builder_element') ){
				gdlr_core_page_builder_element::add_element('twitter', 'gdlr_core_pb_element_twitter');
			}
		}
	}
	
	if( !class_exists('gdlr_core_pb_element_twitter') ){
		class gdlr_core_pb_element_twitter{
			
			// get the element settings
			static function get_settings(){
				return array(
					'icon' => 'fa-twitter',
					'title' => esc_html__('Twitter', 'goodlayers-core-twitter')
				);
			}
			
			// return the element options
			static function get_options(){
				global $gdlr_core_item_pdb;
				
				return array(
					'general' => array(
						'title' => esc_html__('General', 'goodlayers-core-twitter'),
						'options' => array(
							'style' => array(
								'title' => esc_html__('Twitter Style', 'goodlayers-core-twitter'),
								'type' => 'combobox',
								'options' => array(
									'list' => esc_html__('List', 'goodlayers-core-twitter'),
									'carousel' => esc_html__('Carousel', 'goodlayers-core-twitter'),
								)
							),
							'username' => array(
								'title' => esc_html__('Twitter Username', 'goodlayers-core-twitter'),
								'type' => 'text',
								'description' => esc_html__('You can see how to obtain the twitter api key here.') . ' ' .
									'<a href="http://support.goodlayers.com/document/obtain-twitter-authentication-info/" target="_blank" >http://support.goodlayers.com/document/obtain-twitter-authentication-info/</a>'
							),
							'consumer-key' => array(
								'title' => esc_html__('Consumer Key', 'goodlayers-core-twitter'),
								'type' => 'text',
							),
							'consumer-secret' => array(
								'title' => esc_html__('Consumer Secret', 'goodlayers-core-twitter'),
								'type' => 'text',
							),
							'access-token' => array(
								'title' => esc_html__('Access Token', 'goodlayers-core-twitter'),
								'type' => 'text',
							),
							'access-token-secret' => array(
								'title' => esc_html__('Access Token Secret', 'goodlayers-core-twitter'),
								'type' => 'text',
							),
							'num-fetch' => array(
								'title' => esc_html__('Display Number', 'goodlayers-core-twitter'), 
								'type' => 'text',
								'default' => 3
							),
							'cache-time' => array(
								'title' => esc_html__('Cache Time (Hours)', 'goodlayers-core-twitter'),
								'type' => 'text',
								'default' => 1
							),
							'carousel-autoslide' => array(
								'title' => esc_html__('Autoslide Carousel', 'goodlayers-core'),
								'type' => 'checkbox',
								'default' => 'enable',
							),
						)
					),
					'typography' => array(
						'title' => esc_html('Typography', 'goodlayers-core-twitter'),
						'options' => array(
							'font-size' => array(
								'title' => esc_html__('Font Size', 'goodlayers-core-twitter'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'default' => '',
								'description' => esc_html__('Leaving this field blank will display the default font size from theme options', 'goodlayers-core-twitter'),
							)
						)
					),
					'spacing' => array(
						'title' => esc_html('Spacing', 'goodlayers-core-twitter'),
						'options' => array(
							'padding-bottom' => array(
								'title' => esc_html__('Padding Bottom ( Item )', 'goodlayers-core-twitter'),
								'type' => 'text',
								'data-input-type' => 'pixel',
								'default' => $gdlr_core_item_pdb
							)
						)
					),
					'item-title' => array(
						'title' => esc_html('Item Title', 'goodlayers-core-twitter'),
						'options' => gdlr_core_block_item_option()
					)
				);
			}
			
			// get the preview for page builder
			static function get_preview( $settings = array() ){
				$content  = self::get_content($settings, true);
				$id = mt_rand(0, 9999);
				
				ob_start();
?><script type="text/javascript" id="gdlr-core-preview-twitter-<?php echo esc_attr($id); ?>" >
jQuery(document).ready(function(){
	jQuery('#gdlr-core-preview-twitter-<?php echo esc_attr($id); ?>').parent().gdlr_core_content_script().gdlr_core_flexslider();
});
</script><?php	
				$content .= ob_get_contents();
				ob_end_clean();
				
				return $content;
			}			
			
			// get the content from settings
			static function get_content( $settings = array(), $preview = false ){
				global $gdlr_core_item_pdb;
				
				// default variable
				if( empty($settings) ){
					$settings = array(
						'padding-bottom' => $gdlr_core_item_pdb
					);
				}
				
				// start printing item
				$extra_class = empty($settings['class'])? '': ' ' . $settings['class'];
				$ret  = '<div class="gdlr-core-twitter-item gdlr-core-item-pdlr gdlr-core-item-pdb ' . esc_attr($extra_class) . '" ';
				if( !empty($settings['padding-bottom']) && $settings['padding-bottom'] != $gdlr_core_item_pdb ){
					$ret .= gdlr_core_esc_style(array('padding-bottom'=>$settings['padding-bottom']));
				}
				if( !empty($settings['id']) ){
					$ret .= ' id="' . esc_attr($settings['id']) . '" ';
				}
				$ret .= ' >';

				// title section
				$title_settings = $settings;
				$title_settings['pdlr'] = false;
				$title_settings['carousel'] = (!empty($settings['style']) && $settings['style'] == 'carousel')? 'enable': 'disable';
				$ret .= gdlr_core_block_item_title($title_settings);

				// twitter content
				if( !empty($settings['username']) && !empty($settings['consumer-key']) && !empty($settings['consumer-secret']) && 
					!empty($settings['access-token']) && !empty($settings['access-token-secret']) ){

					$api_key = array(
						'consumer-key' => trim($settings['consumer-key']),
						'consumer-secret' => trim($settings['consumer-secret']),
						'access-token' => trim($settings['access-token']),
						'access-token-secret' => trim($settings['access-token-secret'])
					);
					$settings['num-fetch'] = empty($settings['num-fetch'])? '3': $settings['num-fetch'];
					$settings['cache-time'] = empty($settings['cache-time'])? '1': $settings['cache-time'];

					$ret .= '<div class="gdlr-core-twitter-content" ' . gdlr_core_esc_style(array(
						'font-size' => empty($settings['font-size'])? '': $settings['font-size']
					)) . ' >';
					$tweets = gdlr_core_get_tweets($settings['username'], $api_key, $settings['num-fetch'], $settings['cache-time']);

					if( !empty($tweets) ){
						if( empty($settings['style']) || $settings['style'] == 'list' ){
							$ret .= '<ul class="gdlr-core-twitter-content-list" >';
							foreach( $tweets as $tweet ){
								$ret .= '<li>';
								$ret .= '<div class="gdlr-core-twitter-item-list" >';
								$ret .= '<span class="gdlr-core-twitter-item-list-content" >' . gdlr_core_escape_content($tweet['text']) . '</span>';
								$ret .= '<span class="gdlr-core-twitter-item-list-date gdlr-core-skin-caption" >' . gdlr_core_escape_content($tweet['date']) . '</span>';
								$ret .= '</div>';
								$ret .= '</li>';
							}
							$ret .= '</ul>';
						}else if( $settings['style'] == 'carousel' ){

							$flex_atts = array(
								'carousel' => true,
								'column' => 1,
								'navigation' => 'navigation',
								'nav-parent' => 'gdlr-core-twitter-item',
								'disable-autoslide' => (empty($settings['carousel-autoslide']) || $settings['carousel-autoslide'] == 'enable')? '': true,
							);

							$slides = array();
							foreach( $tweets as $tweet ){
								$slide  = '<div class="gdlr-core-twitter-item-list" >';
								$slide .= '<span class="gdlr-core-twitter-item-list-content" >' . gdlr_core_escape_content($tweet['text']) . '</span>';
								$slide .= '<span class="gdlr-core-twitter-item-list-date gdlr-core-skin-caption" >' . gdlr_core_escape_content($tweet['date']) . '</span>';
								$slide .= '</div>';

								$slides[] = $slide;
							}

							$ret .= gdlr_core_get_flexslider($slides, $flex_atts);
						}
					}else{
						$ret .= '<div class="gdlr-core-external-plugin-message">' . esc_html__('Cannot retrieve tweets data, please check your twitter access info and try this again.', 'goodlayers-core-twitter') . '</div>';
					}

					$ret .= '</div>'; // gdlr-core-twitter-content
				
				}else{
					$ret .= '<div class="gdlr-core-external-plugin-message">' . esc_html__('Cannot retrieve tweets data, please fill in all twitter access informations.', 'goodlayers-core-twitter') . '</div>';
				}

				$ret .= '</div>'; // gdlr-core-twitter-item
				
				return $ret;
			}
			
		} // gdlr_core_pb_element_text_script
	} // class_exists	