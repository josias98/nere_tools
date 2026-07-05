<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class: WPGMP_Model_Route
 *
 * Handles CRUD operations for the Route entity.
 *
 * @package Maps
 * @version 3.0.0
 */

if ( ! class_exists( 'WPGMP_Model_Route' ) ) {

	class WPGMP_Model_Route extends FlipperCode_Model_Base {

		protected $validations;

		public function __construct() {
			$this->table  = TBL_ROUTES;
			$this->unique = 'route_id';
			$this->load_columns();
			$this->validations = array(
				'route_title' => array(
					'req'     => esc_html__( 'Please enter route title.', 'wp-google-map-plugin' ),
					'max=255' => esc_html__( 'Route title cannot contain more than 255 characters.', 'wp-google-map-plugin' ),
				),
			);
		}

		public function navigation() {
			return apply_filters('wpgmp_route_navigation', array(
				'wpgmp_form_route'   => esc_html__( 'Add Route', 'wp-google-map-plugin' )			));
		}

		public function install() {
			global $wpdb;
			$charset = $wpdb->get_charset_collate();
			return "CREATE TABLE {$wpdb->prefix}map_routes (
				route_id int(11) NOT NULL AUTO_INCREMENT,
				route_title varchar(255),
				route_stroke_color varchar(255),
				route_stroke_opacity varchar(255),
				route_stroke_weight int(11),
				route_travel_mode varchar(255),
				route_unit_system varchar(255),
				route_marker_draggable varchar(255),
				route_optimize_waypoints varchar(255),
				route_start_location int(11),
				route_end_location int(11),
				route_way_points text,
				extensions_fields text,
				PRIMARY KEY  (route_id)
			) $charset;";
		}

		public function fetch( $where = array() ) {
			
		}

		public function save() {
			
		}

		public function delete() {
			
		}
	}
}