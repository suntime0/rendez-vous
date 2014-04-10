<?php
/**
 * Rendez Vous Screens.
 *
 * Manage screens
 *
 * @package Rendez Vous
 * @subpackage Screens
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Screen Class.
 *
 * @package Rendez Vous
 * @subpackage Screens
 *
 * @since Rendez Vous (1.0.0)
 */
class Rendez_Vous_Screens {

	/**
	 * The constructor
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_filters();
		$this->setup_actions();
	}

	/**
	 * Starts screen management
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public static function manage_screens() {
		$rdv = rendez_vous();

		if ( empty( $rdv->screens ) ) {
			$rdv->screens = new self;
		}

		return $rdv->screens;
	}

	/**
	 * Set some globals
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public function setup_globals() {

		$this->template      = '';
		$this->template_dir  = rendez_vous()->includes_dir . 'templates';
		$this->screen = '';
	}

	/**
	 * Set filters
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	private function setup_filters() {
		if ( bp_is_current_component( 'rendez_vous' ) ) {
			add_filter( 'bp_located_template',   array( $this, 'template_filter' ), 20, 2 );
			add_filter( 'bp_get_template_stack', array( $this, 'add_to_template_stack' ), 10, 1 );
		}
	}

	/**
	 * Set Actions
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	private function setup_actions() {
		add_action( 'rendez_vous_schedule', array( $this, 'schedule_actions' ) );
	}

	/**
	 * Filter the located template
	 * 
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public function template_filter( $found_template = '', $templates = array() ) {
		$bp = buddypress();

		// Bail if theme has it's own template for content.
		if ( ! empty( $found_template ) )
			return $found_template;

		// Current theme do use theme compat, no need to carry on
		if ( $bp->theme_compat->use_with_current_theme )
			return false;

		return apply_filters( 'rendez_vous_load_template_filter', $found_template );
	}

	/**
	 * Add template dir to stack (not used)
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public function add_to_template_stack( $templates = array() ) {
		// Adding the plugin's provided template to the end of the stack
		// So that the theme can override it.
		return array_merge( $templates, array( $this->template_dir ) );
	}

	/**
	 * Shedule Screen
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public static function schedule_screen() {

		do_action( 'rendez_vous_schedule' );

		self::load_template( '', 'schedule' );
	}

	/**
	 * Attend Screen
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public static function attend_screen() {

		do_action( 'rendez_vous_attend' );

		// We'll only use members/single/plugins
		self::load_template( '', 'attend' );
	}

	/**
	 * Load the templates
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public static function load_template( $template = '', $screen = '' ) {
		$rdv = rendez_vous();
		/****
		 * Displaying Content
		 */
		$rdv->screens->template = $template;

		if ( ! empty( $rdv->screens->screen ) )
			$screen = $rdv->screens->screen;
		
		if ( buddypress()->theme_compat->use_with_current_theme && ! empty( $template ) ) {
			add_filter( 'bp_get_template_part', array( __CLASS__, 'template_part' ), 10, 3 );
		} else {
			// You can only use this method for users profile pages
			if ( ! bp_is_directory() ) {
				
				$rdv->screens->template = 'members/single/plugins';
				add_action( 'bp_template_title',   "rendez_vous_{$screen}_title"   );
				add_action( 'bp_template_content', "rendez_vous_{$screen}_content" );
			}
		}

		/* This is going to look in wp-content/plugins/[plugin-name]/includes/templates/ first */
		bp_core_load_template( apply_filters( "rendez_vous_template_{$screen}", $rdv->screens->template ) );
	}

	/**
	 * Filter template part (not used)
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public static function template_part( $templates, $slug, $name ) {
		if ( $slug != 'members/single/plugins' ) {
	        return $templates;
		}
	    return array( rendez_vous()->screens->template . '.php' );
	}

	/**
	 * Set Actions
	 *
	 * @package Rendez Vous
 	 * @subpackage Screens
     *
     * @since Rendez Vous (1.0.0)
	 */
	public function schedule_actions() {
		$action = isset( $_GET['action'] ) ? $_GET['action'] : false;

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

			$this->screen = 'edit';

			do_action( 'rendez_vous_edit_screen' );
		}

		// Display single
		if ( ! empty( $_GET['rdv'] ) && ( empty( $action ) || ! in_array( $action, array( 'edit', 'delete' ) ) ) ) {

			$redirect = remove_query_arg( array( 'rdv', 'n', 'action' ), wp_get_referer() );

			$rendez_vous_id = absint( $_GET['rdv'] );

			$rendez_vous = rendez_vous_get_item( $rendez_vous_id );

			// Public rendez-vous can be seen by anybody
			$has_access = true;

			if ( 'private' == $rendez_vous->status )
				$has_access = current_user_can( 'read_private_rendez_vouss', $rendez_vous_id );

			if ( empty( $rendez_vous ) || empty( $has_access ) || 'draft' == $rendez_vous->status ) {
				bp_core_add_message( __( 'You do not have access to this rendez-vous', 'rendez-vous' ), 'error' );
				bp_core_redirect( $redirect );
			}		

			rendez_vous()->item = $rendez_vous;

			$this->screen = 'single';

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

			// Super Admins cannot "steal" the ownership of a rendez-vous
			// but they surely can edit it :)
			if ( bp_current_user_can( 'bp_moderate' ) && ! bp_is_my_profile() ) {
				$args['organizer'] = bp_displayed_user_id();
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
	}
}
add_action( 'bp_init', array( 'Rendez_Vous_Screens', 'manage_screens' ) );
