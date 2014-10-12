<?php
/**
 * Rendez Vous Functions.
 *
 * Plugin functions
 *
 * @package Rendez Vous
 * @subpackage Functions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get a rendez-vous
 *
 * @package Rendez Vous
 * @subpackage Functions
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_get_item( $id = 0 ) {
	if ( empty( $id ) )
		return false;

	$rendez_vous = new Rendez_Vous_Item( $id );

	return apply_filters( 'rendez_vous_get_item', $rendez_vous );
}

/**
 * Get rendez-vouss
 *
 * @package Rendez Vous
 * @subpackage Functions
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_get_items( $args = array() ) {
	$defaults = array(
		'attendees'       => array(), // one or more user ids who may attend to the rendez vous
		'organizer'	      => false,   // the author id of the rendez vous
		'per_page'	      => 20,
		'page'		      => 1,
		'search'          => false,
		'exclude'		  => false,   // comma separated list or array of rendez vous ids.
		'orderby' 		  => 'modified',
		'order'           => 'DESC',
		'group_id'        => false,
	);

	$r = bp_parse_args( $args, $defaults, 'rendez_vous_get_items_args' );

	$rendez_vouss = wp_cache_get( 'rendez_vous_rendez_vouss', 'bp' );

	if ( empty( $rendez_vouss ) ) {
		$rendez_vouss = Rendez_Vous_Item::get( array(
			'attendees'       => (array) $r['attendees'],
			'organizer'	      => (int) $r['organizer'],
			'per_page'	      => $r['per_page'],
			'page'		      => $r['page'],
			'search'          => $r['search'],
			'exclude'		  => $r['exclude'],
			'orderby' 		  => $r['orderby'],
			'order'           => $r['order'],
			'group_id'        => $r['group_id'],
		) );

		wp_cache_set( 'rendez_vous_rendez_vouss', $rendez_vouss, 'bp' );
	}

	return apply_filters_ref_array( 'rendez_vous_get_items', array( &$rendez_vouss, &$r ) );
}

/**
 * Launch the Rendez Vous Editor
 *
 * @package Rendez Vous
 * @subpackage Functions
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_editor( $editor_id, $settings = array() ) {
	Rendez_Vous_Editor::editor( $editor_id, $settings );
}

/**
 * Prepare the user for js
 *
 * @package Rendez Vous
 * @subpackage Functions
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_prepare_user_for_js( $users ) {

	$response = array(
		'id'           => intval( $users->ID ),
		'name'         => $users->display_name,
		'avatar'       => htmlspecialchars_decode( bp_core_fetch_avatar( array(
				'item_id' => $users->ID,
				'object'  => 'user',
				'type'    => 'full',
				'width'   => 150,
				'height'  => 150,
				'html'    => false
			)
		) ),
	);

	return apply_filters( 'rendez_vous_prepare_user_for_js', $response, $users );
}

/**
 * Save a Rendez Vous
 *
 * @package Rendez Vous
 * @subpackage Functions
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_save( $args = array() ) {

	$r = bp_parse_args( $args, array(
		'id'          => false,
		'organizer'   => bp_loggedin_user_id(),
		'title'       => '',
		'venue'       => '',
		'description' => '',
		'duration'    => '',
		'privacy'     => '',
		'status'      => 'draft',
		'days'        => array(),   // array( 'timestamp' => array( attendees id ) )
		'attendees'   => array(),	// Attendees id
		'def_date'    => 0, 	    // timestamp
		'report'      => '',
		'group_id'    => false,
	), 'rendez_vous_save_args' );

	if ( empty( $r['title'] ) || empty( $r['organizer'] ) ) {
		return false;
	}

	// Using rendez_vous
	$rendez_vous = new Rendez_Vous_Item( $r['id'] );

	$rendez_vous->organizer   = $r['organizer'];
	$rendez_vous->title       = $r['title'];
	$rendez_vous->venue       = $r['venue'];
	$rendez_vous->description = $r['description'];
	$rendez_vous->duration    = $r['duration'];
	$rendez_vous->privacy     = $r['privacy'];
	$rendez_vous->status      = $r['status'];
	$rendez_vous->attendees   = $r['attendees'];
	$rendez_vous->def_date    = $r['def_date'];
	$rendez_vous->report      = $r['report'];
	$rendez_vous->group_id    = $r['group_id'];

	// Allow attendees to not attend !
	if ( 'draft' == $r['status'] && ! in_array( 'none', array_keys( $r['days'] ) ) ) {
		$r['days']['none'] = array();

		// Saving days the first time only
		$rendez_vous->days    = $r['days'];
	}

	do_action( 'rendez_vous_before_saved', $rendez_vous, $r );

	$id = $rendez_vous->save();

	do_action( 'rendez_vous_after_saved', $rendez_vous, $r );

	return $id;
}

/**
 * Delete a rendez-vous
 *
 * @package Rendez Vous
 * @subpackage Functions
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_delete_item( $id = 0 ) {
	if ( empty( $id ) )
		return false;

	do_action( 'rendez_vous_before_delete', $id );

	$deleted = Rendez_Vous_Item::delete( $id );

	if ( ! empty( $deleted ) ) {
		do_action( 'rendez_vous_after_delete', $id, $deleted );
		return true;
	} else {
		return false;
	}
}

/**
 * Set caps
 *
 * @package Rendez Vous
 * @subpackage Functions
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_get_caps() {
	return apply_filters( 'rendez_vous_get_caps', array (
		'edit_posts'          => 'edit_rendez_vouss',
		'edit_others_posts'   => 'edit_others_rendez_vouss',
		'publish_posts'       => 'publish_rendez_vouss',
		'read_private_posts'  => 'read_private_rendez_vouss',
		'delete_posts'        => 'delete_rendez_vouss',
		'delete_others_posts' => 'delete_others_rendez_vouss'
	) );
}

/**
 * Display link
 *
 * @package Rendez Vous
 * @subpackage Functions
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_get_single_link( $id = 0, $organizer_id = 0 ) {
	if ( empty( $id ) || empty( $organizer_id ) )
		return false;

	$link = trailingslashit( bp_core_get_user_domain( $organizer_id ) . buddypress()->rendez_vous->slug );
	$link = add_query_arg( array( 'rdv' => $id ), $link );

	return apply_filters( 'rendez_vous_get_single_link', $link, $id, $organizer_id );
}

/**
 * Edit link
 *
 * @package Rendez Vous
 * @subpackage Functions
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_get_edit_link( $id = 0, $organizer_id = 0 ) {
	if ( empty( $id ) || empty( $organizer_id ) )
		return false;

	$link = trailingslashit( bp_core_get_user_domain( $organizer_id ) . buddypress()->rendez_vous->slug );
	$link = add_query_arg( array( 'rdv' => $id, 'action' => 'edit' ), $link );

	return apply_filters( 'rendez_vous_get_edit_link', $link, $id, $organizer_id );
}

/**
 * Delete link
 *
 * @package Rendez Vous
 * @subpackage Functions
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_get_delete_link( $id = 0, $organizer_id = 0 ) {
	if ( empty( $id ) || empty( $organizer_id ) ) {
		return false;
	}

	$link = trailingslashit( bp_core_get_user_domain( $organizer_id ) . buddypress()->rendez_vous->slug );
	$link = add_query_arg( array( 'rdv' => $id, 'action' => 'delete' ), $link );
	$link = wp_nonce_url( $link, 'rendez_vous_delete' );

	return apply_filters( 'rendez_vous_get_delete_link', $link, $id, $organizer_id );
}

/**
 * iCal Link
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since Rendez Vous (1.1.0)
 *
 * @param  int $id           the id of the rendez-vous
 * @param  int $organizer_id the author id of the rendez-vous
 * @return string            the iCal link
 */
function rendez_vous_get_ical_link( $id = 0, $organizer_id = 0 ) {
	if ( empty( $id ) || empty( $organizer_id ) ) {
		return false;
	}

	$link = trailingslashit( bp_core_get_user_domain( $organizer_id ) . buddypress()->rendez_vous->slug . '/schedule/ical/' . $id );
	$link = wp_nonce_url( $link, 'rendez_vous_ical' );

	return apply_filters( 'rendez_vous_get_ical_link', $link, $id, $organizer_id );
}

/**
 * Maybe run upgrate routines
 *
 * @package Rendez Vous
 * @subpackage Functions
 *
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_maybe_upgrade() {
	if ( get_current_blog_id() == bp_get_root_blog_id() ) {

		$db_version = bp_get_option( 'rendez-vous-version', 0 );

		if ( version_compare( rendez_vous()->version, $db_version, '>' ) ) {
			// run some routines..
			do_action( 'rendez_vous_upgrade' );

			// Update db version
			bp_update_option( 'rendez-vous-version', rendez_vous()->version );
		}
	}
}

/**
 * Handle rendez-vous actions (group/member contexts)
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since Rendez Vous (1.1.0)
 *
 * @return string the rendez-vous screen id
 */
function rendez_vous_handle_actions() {
	$action = isset( $_GET['action'] ) ? $_GET['action'] : false;
	$screen = '';

	// Edit template
	if ( ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] && ! empty( $_GET['rdv'] ) ) {

		$redirect = remove_query_arg( array( 'rdv', 'action', 'n' ), wp_get_referer() );

		$rendez_vous_id = absint( $_GET['rdv'] );

		$rendez_vous = rendez_vous_get_item( $rendez_vous_id );

		if ( empty( $rendez_vous ) || ! current_user_can( 'edit_rendez_vous', $rendez_vous_id ) ) {
			bp_core_add_message( __( 'Rendez-vous could not be found', 'rendez-vous' ), 'error' );
			bp_core_redirect( $redirect );
		}

		if ( 'draft' == $rendez_vous->status ){
			bp_core_add_message( __( 'Your rendez-vous is in draft mode, check informations and publish!', 'rendez-vous' ) );
		}

		rendez_vous()->item = $rendez_vous;

		$screen = 'edit';

		do_action( 'rendez_vous_edit_screen' );
	}

	// Display single
	if ( ! empty( $_GET['rdv'] ) && ( empty( $action ) || ! in_array( $action, array( 'edit', 'delete' ) ) ) ) {

		$redirect = remove_query_arg( array( 'rdv', 'n', 'action' ), wp_get_referer() );

		$rendez_vous_id = absint( $_GET['rdv'] );

		$rendez_vous = rendez_vous_get_item( $rendez_vous_id );

		if ( is_null( $rendez_vous->organizer ) ) {
			bp_core_add_message( __( 'The rendez-vous was not found', 'rendez-vous' ), 'error' );
			bp_core_redirect( $redirect );
		}

		// Public rendez-vous can be seen by anybody
		$has_access = true;

		if ( 'private' == $rendez_vous->status ) {
			$has_access = current_user_can( 'read_private_rendez_vouss', $rendez_vous_id );
		}

		if ( empty( $rendez_vous ) || empty( $has_access ) || 'draft' == $rendez_vous->status ) {
			bp_core_add_message( __( 'You do not have access to this rendez-vous', 'rendez-vous' ), 'error' );
			bp_core_redirect( $redirect );
		}

		rendez_vous()->item = $rendez_vous;

		$screen = 'single';

		do_action( 'rendez_vous_single_screen' );
	}

	// Publish & Updates.
	if ( ! empty( $_POST['_rendez_vous_edit'] ) && ! empty( $_POST['_rendez_vous_edit']['id'] ) ) {

		check_admin_referer( 'rendez_vous_update' );

		$redirect = remove_query_arg( array( 'rdv', 'n', 'action' ), wp_get_referer() );

		if ( ! current_user_can( 'edit_rendez_vous', absint( $_POST['_rendez_vous_edit']['id'] ) ) ) {
			bp_core_add_message( __( 'Editing this rendez-vous is not allowed.', 'rendez-vous' ), 'error' );
			bp_core_redirect( $redirect );
		}

		$args = array();
		$action = sanitize_key( $_POST['_rendez_vous_edit']['action'] );

		$args = array_diff_key( $_POST['_rendez_vous_edit'], array(
			'action'           => 0,
			'submit'           => 0
		) );

		$args['status'] = 'publish';

		// Make sure the organizer doesn't change if rendez-vous is edited by someone else
		if ( ! bp_is_my_profile() ) {
			$args['organizer'] = apply_filters( 'rendez_vous_edit_action_organizer_id', bp_displayed_user_id(), $args );
		}


		$notify   = ! empty( $_POST['_rendez_vous_edit']['notify'] ) ? 1 : 0;
		$activity = ! empty( $_POST['_rendez_vous_edit']['activity'] ) && empty( $args['privacy'] ) ? 1 : 0;

		do_action( "rendez_vous_before_{$action}", $args, $notify, $activity );

		$id = rendez_vous_save( $args );

		if ( empty( $id ) ) {
			bp_core_add_message( __( 'Editing this rendez-vous failed.', 'rendez-vous' ), 'error' );
		} else {
			bp_core_add_message( __( 'Rendez-vous successfully edited.', 'rendez-vous' ) );
			$redirect = add_query_arg( 'rdv', $id, $redirect );

			// Rendez-vous is edited or published, let's handle notifications & activity
			do_action( "rendez_vous_after_{$action}", $id, $args, $notify, $activity );
		}

		// finally redirect !
		bp_core_redirect( $redirect );
	}

	// Set user preferences.
	if ( ! empty( $_POST['_rendez_vous_prefs'] ) && ! empty( $_POST['_rendez_vous_prefs']['id'] ) ) {

		check_admin_referer( 'rendez_vous_prefs' );

		$redirect = remove_query_arg( array( 'n', 'action' ), wp_get_referer() );

		$rendez_vous_id = absint( $_POST['_rendez_vous_prefs']['id'] );
		$rendez_vous = rendez_vous_get_item( $rendez_vous_id );

		$attendee_id = bp_loggedin_user_id();

		$has_access = $attendee_id;

		if ( ! empty( $has_access ) && 'private' == $rendez_vous->status )
			$has_access = current_user_can( 'read_private_rendez_vouss', $rendez_vous_id );

		if ( empty( $has_access ) ) {
			bp_core_add_message( __( 'You do not have access to this rendez-vous', 'rendez-vous' ), 'error' );
			bp_core_redirect( $redirect );
		}

		$args = $_POST['_rendez_vous_prefs'];

		// Get days
		if ( ! empty( $args['days'][ $attendee_id ] ) )
			$args['days'] = $args['days'][ $attendee_id ];
		else
			$args['days'] = array();

		do_action( "rendez_vous_before_attendee_prefs", $args );

		if ( ! Rendez_Vous_Item::attendees_pref( $rendez_vous_id, $attendee_id, $args['days'] ) ) {
			bp_core_add_message( __( 'Saving your preferences failed.', 'rendez-vous' ), 'error' );
		} else {
			bp_core_add_message( __( 'Preferences successfully saved.', 'rendez-vous' ) );

			// let's handle notifications to the organizer
			do_action( "rendez_vous_after_attendee_prefs", $args, $attendee_id, $rendez_vous );
		}

		// finally redirect !
		bp_core_redirect( $redirect );
	}

	// Delete
	if ( ! empty( $_GET['action'] ) && 'delete' == $_GET['action'] && ! empty( $_GET['rdv'] ) ) {

		check_admin_referer( 'rendez_vous_delete' );

		$redirect = remove_query_arg( array( 'rdv', 'action', 'n' ), wp_get_referer() );

		$rendez_vous_id = absint( $_GET['rdv'] );

		if ( empty( $rendez_vous_id ) || ! current_user_can( 'delete_rendez_vous', $rendez_vous_id ) ) {
			bp_core_add_message( __( 'Rendez-vous could not be found', 'rendez-vous' ), 'error' );
			bp_core_redirect( $redirect );
		}

		$deleted = rendez_vous_delete_item( $rendez_vous_id );

		if ( ! empty( $deleted ) ) {
			bp_core_add_message( __( 'Rendez-vous successfully cancelled.', 'rendez-vous' ) );
		} else {
			bp_core_add_message( __( 'Rendez-vous could not be cancelled', 'rendez-vous' ), 'error' );
		}

		// finally redirect !
		bp_core_redirect( $redirect );
	}

	return $screen;
}

/**
 * Generates an iCal file using the rendez-vous datas
 *
 * @package Rendez Vous
 * @subpackage Filters
 *
 * @since Rendez Vous (1.1.0)
 *
 * @return string calendar file
 */
function rendez_vous_download_ical() {
	$ical_page = array(
		'is'  => (bool) bp_is_current_action( 'schedule' ) && 'ical' == bp_action_variable( 0 ),
		'rdv' => (int)  bp_action_variable( 1 ),
	);

	apply_filters( 'rendez_vous_download_ical', (array) $ical_page );

	if ( empty( $ical_page['is'] ) ) {
		return;
	}

	check_admin_referer( 'rendez_vous_ical' );

	$redirect = wp_get_referer();

	if ( empty( $ical_page['rdv'] ) ) {
		bp_core_add_message( __( 'the Rendez-vous was not found.', 'rendez-vous' ), 'error' );
		bp_core_redirect( $redirect );
	}

	$rendez_vous = rendez_vous_get_item( $ical_page['rdv'] );

	if ( $rendez_vous->organizer != bp_loggedin_user_id() && ! in_array( bp_loggedin_user_id(), $rendez_vous->attendees ) ) {
		bp_core_add_message( __( 'You are not attending this rendez-vous.', 'rendez-vous' ), 'error' );
		bp_core_redirect( $redirect );
	}

	if ( empty( $rendez_vous->def_date ) ) {
		bp_core_add_message( __( 'the Rendez-vous is not set yet.', 'rendez-vous' ), 'error' );
		bp_core_redirect( $redirect );
	}

	$hourminutes = explode( ':', $rendez_vous->duration );

	if ( ! is_array( $hourminutes ) && count( $hourminutes ) < 2 ) {
		bp_core_add_message( __( 'the duration is not set the right way.', 'rendez-vous' ), 'error' );
		bp_core_redirect( $redirect );
	}

	$minutes = intval( $hourminutes[1] ) + ( intval( $hourminutes[0] ) * 60 );
	$end_date = strtotime( '+' . $minutes . ' minutes', $rendez_vous->def_date );

	// Dates are stored as UTC althought values are local, we need to reconvert
	$date_start = date_i18n( 'Y-m-d H:i:s', $rendez_vous->def_date, true );
	$date_end   = date_i18n( 'Y-m-d H:i:s', $end_date, true );

	date_default_timezone_set( get_option( 'timezone_string' ) );

	status_header( 200 );
	header( 'Cache-Control: cache, must-revalidate' );
	header( 'Pragma: public' );
	header( 'Content-Description: File Transfer' );
	header( 'Content-Disposition: attachment; filename=rendez_vous_' . $rendez_vous->id . '.ics' );
	header( 'Content-Type: text/calendar' );
    ?>
BEGIN:VCALENDAR<?php echo "\n"; ?>
VERSION:2.0<?php echo "\n"; ?>
PRODID:-//hacksw/handcal//NONSGML v1.0//EN<?php echo "\n"; ?>
CALSCALE:GREGORIAN<?php echo "\n"; ?>
BEGIN:VEVENT<?php echo "\n"; ?>
DTEND:<?php echo gmdate('Ymd\THis\Z', strtotime( $date_end ) ); ?><?php echo "\n"; ?>
UID:<?php echo uniqid(); ?><?php echo "\n"; ?>
DTSTAMP:<?php echo gmdate( 'Ymd\THis\Z', time() ); ?><?php echo "\n"; ?>
LOCATION:<?php echo esc_html( preg_replace('/([\,;])/','\\\$1', $rendez_vous->venue ) ); ?><?php echo "\n"; ?>
DESCRIPTION:<?php echo esc_html( preg_replace('/([\,;])/','\\\$1', $rendez_vous->description ) ); ?><?php echo "\n"; ?>
URL;VALUE=URI:<?php echo esc_url( rendez_vous_get_single_link( $rendez_vous->id, $rendez_vous->organizer ) ); ?><?php echo "\n"; ?>
SUMMARY:<?php echo esc_html( preg_replace('/([\,;])/','\\\$1', $rendez_vous->title ) ); ?><?php echo "\n"; ?>
DTSTART:<?php echo gmdate('Ymd\THis\Z', strtotime( $date_start ) ); ?><?php echo "\n"; ?>
END:VEVENT<?php echo "\n"; ?>
END:VCALENDAR<?php echo "\n"; ?>
	<?php
	exit();
}
add_action( 'bp_actions', 'rendez_vous_download_ical' );
