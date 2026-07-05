<?php
	/**
	 * A widget that show recent twitter feed.
	 */

	add_action('widgets_init', 'gdlr_core_twitter_widget');
	if( !function_exists('gdlr_core_twitter_widget') ){
		function gdlr_core_twitter_widget() {
			register_widget( 'Goodlayers_Core_Twitter_Widget' );
		}
	}

	if( !class_exists('Goodlayers_Core_Twitter_Widget') ){
		class Goodlayers_Core_Twitter_Widget extends WP_Widget{

			// Initialize the widget
			function __construct() {

				parent::__construct(
					'gdlr-core-twitter-widget', 
					esc_html__('Twitter Widget ( Goodlayers )', 'goodlayers-core-twitter'), 
					array('description' => esc_html__('A widget that show latest twitter feed', 'goodlayers-core-twitter'))
				);  

			}

			// Output of the widget
			function widget( $args, $instance ) {
	
				$title = empty($instance['title'])? '': apply_filters('widget_title', $instance['title']);
				$username = empty($instance['username'])? '': trim($instance['username']);
				$num_fetch = empty($instance['num-fetch'])? '': $instance['num-fetch'];
				$cache_time = isset($instance['cache-time'])? $instance['cache-time']: 1;

				$api_key = array(
					'consumer-key' => empty($instance['consumer-key'])? '': trim($instance['consumer-key']),
					'consumer-secret' => empty($instance['consumer-secret'])? '': trim($instance['consumer-secret']),
					'access-token' => empty($instance['access-token'])? '': trim($instance['access-token']),
					'access-token-secret' => empty($instance['access-token-secret'])? '': trim($instance['access-token-secret'])
				);

				// Opening of widget
				echo gdlr_core_escape_content($args['before_widget']);
				
				// Open of title tag
				if( !empty($title) ){ 
					echo gdlr_core_escape_content($args['before_title'] . $title . $args['after_title']); 
				}
					
				$tweets = gdlr_core_get_tweets($username, $api_key, $num_fetch, $cache_time);

				echo '<ul class="gdlr-core-twitter-widget-wrap">';
				foreach( $tweets as $tweet ){
					echo '<li>';
					echo '<div class="gdlr-core-twitter-widget" >';
					echo '<span class="gdlr-core-twitter-widget-content" >' . gdlr_core_escape_content($tweet['text']) . '</span>';
					echo '<span class="gdlr-core-twitter-widget-date" >' . gdlr_core_escape_content($tweet['date']) . '</span>';
					echo '</div>';
					echo '</li>';
				}
				echo '</ul>'; // gdlr-core-twitter-widget-wrap

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
							'title' => esc_html__('Title', 'goodlayers-core-twitter'),
							'value' => (isset($instance['title'])? $instance['title']: '')
						),
						'username' => array(
							'type' => 'text',
							'id' => $this->get_field_id('username'),
							'name' => $this->get_field_name('username'),
							'title' => esc_html__('Twitter Username', 'goodlayers-core-twitter'),
							'value' => (isset($instance['username'])? $instance['username']: '')
						),
						'consumer-key' => array(
							'type' => 'text',
							'id' => $this->get_field_id('consumer-key'),
							'name' => $this->get_field_name('consumer-key'),
							'title' => esc_html__('Consumer Key', 'goodlayers-core-twitter'),
							'value' => (isset($instance['consumer-key'])? $instance['consumer-key']: '')
						),
						'consumer-secret' => array(
							'type' => 'text',
							'id' => $this->get_field_id('consumer-secret'),
							'name' => $this->get_field_name('consumer-secret'),
							'title' => esc_html__('Consumer Secret', 'goodlayers-core-twitter'),
							'value' => (isset($instance['consumer-secret'])? $instance['consumer-secret']: '')
						),
						'access-token' => array(
							'type' => 'text',
							'id' => $this->get_field_id('access-token'),
							'name' => $this->get_field_name('access-token'),
							'title' => esc_html__('Access Token', 'goodlayers-core-twitter'),
							'value' => (isset($instance['access-token'])? $instance['access-token']: '')
						),
						'access-token-secret' => array(
							'type' => 'text',
							'id' => $this->get_field_id('access-token-secret'),
							'name' => $this->get_field_name('access-token-secret'),
							'title' => esc_html__('Access Token Secret', 'goodlayers-core-twitter'),
							'value' => (isset($instance['access-token-secret'])? $instance['access-token-secret']: '')
						),
						'num-fetch' => array(
							'type' => 'text',
							'id' => $this->get_field_id('num-fetch'),
							'name' => $this->get_field_name('num-fetch'),
							'title' => esc_html__('Display Number', 'goodlayers-core-twitter'), 
							'value' => (isset($instance['num-fetch'])? $instance['num-fetch']: '3')
						),
						'cache-time' => array(
							'type' => 'text',
							'id' => $this->get_field_id('cache-time'),
							'name' => $this->get_field_name('cache-time'),
							'title' => esc_html__('Cache Time (Hours)', 'goodlayers-core-twitter'),
							'value' => (isset($instance['cache-time'])? $instance['cache-time']: '1')
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