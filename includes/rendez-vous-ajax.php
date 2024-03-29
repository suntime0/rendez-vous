<?php
/**
 * Rendez Vous Ajax.
 *
 * Ajax functions
 *
 * @package Rendez Vous
 * @subpackage Ajax
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns the available members
 *
 * @package Rendez Vous
 * @subpackage Ajax
 * 
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_ajax_get_users() {

	check_ajax_referer( 'rendez-vous-editor' );

	$query_args = isset( $_REQUEST['query'] ) ? (array) $_REQUEST['query'] : array();
	
	$args = wp_parse_args( $query_args, array( 
			'user_id'      => false,
			'type'         => 'alphabetical',
			'per_page'     => 20,
			'page'         => 1,
			'search_terms' => false,
			'exclude'      => array( bp_loggedin_user_id() ), // we don't want the organizer to be included in the attendees
		)
	);
	
	$query = new BP_User_Query( $args );

	$response = new stdClass();

	$response->meta = array( 'total_page' => 0, 'current_page' => 0 );

	if( empty( $query->results ) )
		wp_send_json_error( $response );
	
	$users = array_map( 'rendez_vous_prepare_user_for_js', array_values( $query->results ) );
	$users = array_filter( $users );

	if( !empty( $args['per_page'] ) ) {
		$response->meta = array( 
			'total_page' => ceil( (int) $query->total_users / (int) $args['per_page'] ), 
			'current_page' => (int) $args['page']  
		);
	}
		
	$response->items = $users;

	wp_send_json_success( $response );
}
add_action( 'wp_ajax_rendez_vous_get_users', 'rendez_vous_ajax_get_users' );

/**
 * Create a rendez vous in draft mode
 *
 * @package Rendez Vous
 * @subpackage Ajax
 * 
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_ajax_create() {
	
	check_ajax_referer( 'rendez-vous-editor', 'nonce' );

	if ( ! bp_current_user_can( 'publish_rendez_vouss' ) )
		wp_send_json_error( __( 'You cannot create a rendez-vous.', 'rendez-vous' ) );

	// Init the create arguments
	$args = array(
		'title'       => '',
		'venue'       => '',
		'description' => '',
		'duration'    => '',
		'days'        => array(),
		'attendees'   => array()
	);

	// First attendees
	$attendees = array_map( 'absint', $_POST['attendees'] );

	if ( empty( $attendees ) )
		wp_send_json_error( __( 'No users were selected.', 'rendez-vous' ) );

	// Add to create arguments
	$args['attendees'] = $attendees;

	// Then fields
	$fields = $_POST['desc'];

	if ( empty( $fields ) || ! is_array( $fields ) )
		wp_send_json_error( __( 'Please describe your rendez-vous using the What tab.', 'rendez-vous' ) );

	$required_fields_missing = array();

	foreach ( $fields as $field ) {
		if ( 'required' == $field['class'] && empty( $field['value'] ) ) {
			$required_fields_missing[] = $field['label'];
		}

		// Add to create arguments
		$args[ $field['id'] ] = $field['value'];
	}

	// Then dates
	$dates = $_POST['maydates'];

	if ( empty( $dates ) || ! is_array( $dates ) )
		wp_send_json_error( __( 'Please define dates for your rendez-vous using the When tab.', 'rendez-vous' ) );

	$maydates = array();
	$maydates_errors = array();
	foreach ( $dates as $date ) {
		$timestamp= false;

		if ( ! empty( $date['hour1'] ) ) {

			if( ! preg_match( '/^[0-2]?[0-9]:[0-5][0-9]$/', $date['hour1'] ) ) {
				$maydates_errors[] = $date['hour1'];
				continue;
			}

			$timestamp = strtotime( $date['mysql'] . ' ' . $date['hour1'] );
			$maydates[ $timestamp ] = array();
		}

		if ( ! empty( $date['hour2'] ) ) {

			if( ! preg_match( '/^[0-2]?[0-9]:[0-5][0-9]$/', $date['hour2'] ) ) {
				$maydates_errors[] = $date['hour2'];
				continue;
			}

			$timestamp = strtotime( $date['mysql'] . ' ' . $date['hour2'] );
			$maydates[ $timestamp ] = array();
		}

		if ( ! empty( $date['hour3'] ) ) {

			if( ! preg_match( '/^[0-2]?[0-9]:[0-5][0-9]$/', $date['hour3'] ) ) {
				$maydates_errors[] = $date['hour3'];
				continue;
			}

			$timestamp = strtotime( $date['mysql'] . ' ' . $date['hour3'] );
			$maydates[ $timestamp ] = array();
		}
			
	}

	if ( ! empty( $maydates_errors ) )
		wp_send_json_error( __( 'Please make sure to respect the format HH:MM when defining time.', 'rendez-vous' ) );

	if ( ! empty( $maydates ) )
		$args['days'] = $maydates;
	
	$rendez_vous_id = rendez_vous_save( $args );

	if ( empty( $rendez_vous_id ) ) {
		wp_send_json_error( __( 'The rendez-vous was not created due to an error.', 'rendez-vous' ) );
	} else {
		// url to edit rendez-vous screen
		wp_send_json_success( rendez_vous_get_edit_link( $rendez_vous_id, bp_loggedin_user_id() ) );
	}
}
add_action( 'wp_ajax_create_rendez_vous', 'rendez_vous_ajax_create' );
