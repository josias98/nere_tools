<?php
	/*
	Plugin Name: Goodlayers Twitter
	Plugin URI: 
	Description: A plugin that help you display the twitter feed ( need to use together with "Goodlayers Core" plugin ). 
	Version: 1.0.3
	Author: Goodlayers
	Author URI: http://www.goodlayers.com
	License: 
	*/

	include(dirname(__FILE__) . '/twitteroauth/autoload.php');
	use Abraham\TwitterOAuth\TwitterOAuth;

	include(dirname(__FILE__) . '/pb-element-twitter.php');
	include(dirname(__FILE__) . '/twitter-widget.php');

	if( !function_exists('gdlr_core_get_tweets') ){
		function gdlr_core_get_tweets( $username, $api_key = array(), $num_fetch = 5, $cache_time = 1 ){

			// get the cache value if exists
			$transient_key = 'gdlr_core_twitter_' . $username . '_' . $num_fetch . '_' . $cache_time;
			if( !empty($cache_time) ){
				$cache_data = get_transient($transient_key);
				if( $cache_data !== false ){ return $cache_data; }
			}

			// init connection to twitter
			$connection = new TwitterOAuth(
				$api_key['consumer-key'],
				$api_key['consumer-secret'], 
				$api_key['access-token'], 
				$api_key['access-token-secret']
			);
			$tweets = $connection->get('statuses/user_timeline', array('screen_name' => $username, 'count' => $num_fetch));

			// if success
			if( $connection->getLastHttpCode() == 200 ){

				$tweets_data = array();
				if( !empty($tweets) ){
					foreach($tweets as $tweet){

						$date_link  = '<a class="gdlr-core-twitter-date" href="http://twitter.com/' . esc_attr($username) . '/statuses/' . esc_attr($tweet->id_str) . '" target="_blank">';
						$date_link .= gdlr_core_get_tweets_time($tweet->created_at);
						$date_link .= '</a>';

						$tweets_data[] = array(
							'text' => gdlr_core_tweets_link_convert($tweet->text),
							'date' => $date_link
						);
					}
				}

				// save settings to cache
				if( !empty($cache_time) ){
					set_transient($transient_key, $tweets_data, (intval($cache_time) * 60 * 60));
				}

			   	return $tweets_data;

			// connection failed
			}else{

				if( !empty($tweets->errors[0]->message) ){
					echo $tweets->errors[0]->message;
				}else{
					echo esc_html__('Error :', 'goodlayers-core-twitter') . ' ' . $connection->getLastHttpCode() . '<br>';
					echo esc_html__('Cannot retrieve tweets, please verify your twitter infomation', 'goodlayers-core-twitter');
				}
			   	
			   	return array();
			}
		} // gdlr_core_get_tweets
	} // function_exists

	// convert links to clickable format
	if( !function_exists('gdlr_core_tweets_link_convert') ){ 
		function gdlr_core_tweets_link_convert($status){

			// convert urls to <a> links
			$status = preg_replace("/([\w]+\:\/\/[\w\-?&;#~=\.\/\@]+…)/", "", $status);
			$status = preg_replace("/([\w]+\:\/\/[\w\-?&;#~=\.\/\@]+[\w\/])/", "<a target=\"_blank\" href=\"$1\">$1</a>", $status);

			// convert # to twitter searches in <a> links
			$status = preg_replace("/#(.+?)(?=[\s.,:,\(\)]|$)/", "<a target=\"_blank\" href=\"http://twitter.com/search?q=$1\">#$1</a>", $status);

			// convert @ to twitter profiles in <a> links
			$status = preg_replace("/@(.+?)(?=[\s.,:,\(\)]|$)/", "<a href=\"http://www.twitter.com/$1\">@$1</a>", $status);

			return $status;
		}
	}

	// convert dates to readable format
	if( !function_exists('gdlr_core_get_tweets_time') ){		
		function gdlr_core_get_tweets_time($a){

			// get current timestampt
			$b = strtotime("now"); 

			// get timestamp when tweet created
			$c = strtotime($a);

			// get difference
			$d = $b - $c;

			// calculate different time values
			$minute = 60;
			$hour = $minute * 60;
			$day = $hour * 24;
			$week = $day * 7;

			if( is_numeric($d) && $d > 0 ){

				// if less then 3 seconds
				if($d < 3) return  ' ' . __("right now", 'goodlayers-core-twitter');

				// if less then minute
				if($d < $minute) return ' ' . floor($d) . ' ' . __("seconds ago", 'goodlayers-core-twitter');

				// if less then 2 minutes
				if($d < $minute * 2) return  ' ' . __("about 1 minute ago", 'goodlayers-core-twitter');

				// if less then hour
				if($d < $hour) return ' ' .  floor($d / $minute) . ' ' . __("minutes ago", 'goodlayers-core-twitter');

				// if less then 2 hours
				if($d < $hour * 2) return ' ' . __("about 1 hour ago", 'goodlayers-core-twitter');

				// if less then day
				if($d < $day) return ' ' . floor($d / $hour) . ' ' . __("hours ago", 'goodlayers-core-twitter');

				// if more then day, but less then 2 days
				if($d > $day && $d < $day * 2) return ' ' . __("yesterday", 'goodlayers-core-twitter');

				// if less then year
				if($d < $day * 365) return ' ' . floor($d / $day) . ' ' . __("days ago", 'goodlayers-core-twitter');

				// else return more than a year
				return ' ' . __("over a year ago", 'goodlayers-core-twitter');
			}
		}
	}

	add_action('plugins_loaded', 'gdlr_core_twitter_load_textdomain');
	if( !function_exists('gdlr_core_twitter_load_textdomain') ){
		function gdlr_core_twitter_load_textdomain() {
		  load_plugin_textdomain('goodlayers-core-twitter', false, plugin_basename(dirname(__FILE__)) . '/languages'); 
		}
	}