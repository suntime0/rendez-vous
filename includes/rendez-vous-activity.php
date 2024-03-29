<?php
/**
 * Rendez Vous Activity
 *
 * Activity functions
 *
 * @package Rendez Vous
 * @subpackage Activity
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Displays a checkbox to allow the user to generate an activity
 *
 * @package Rendez Vous
 * @subpackage Activity
 * 
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_activity_edit_form() {
	?>
	<p>
		<label for="rendez-vous-edit-activity" class="normal">
			<input type="checkbox" id="rendez-vous-edit-activity" name="_rendez_vous_edit[activity]" value="1" <?php disabled( 1, rendez_vous_single_get_privacy() );?>> <?php esc_html_e( 'Record a public activity for all members', 'rendez-vous' );?>
		</label>
	</p>
	<?php
}
add_action( 'rendez_vous_edit_form_after_dates', 'rendez_vous_activity_edit_form' );

/**
 * Register the activity actions
 *
 * @package Rendez Vous
 * @subpackage Activity
 * 
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_register_activity_actions() {
	$bp = buddypress();

	// Bail if activity is not active
	if ( ! bp_is_active( 'activity' ) ) {
		return false;
	}

	bp_activity_set_action(
		$bp->rendez_vous->id,
		'new_rendez_vous',
		__( 'New rendez-vous', 'rendez-vous' ),
		'rendez_vous_format_activity_action'
	);

	bp_activity_set_action(
		$bp->rendez_vous->id,
		'updated_rendez_vous',
		__( 'Updated rendez-vous', 'rendez-vous' ),
		'rendez_vous_format_activity_action'
	);

	do_action( 'rendez_vous_register_activity_actions' );
}
add_action( 'bp_register_activity_actions', 'rendez_vous_register_activity_actions' );

/**
 * format callback
 *
 * @package Rendez Vous
 * @subpackage Activity
 * 
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_format_activity_action( $action, $activity ) {
	// we could use user_id instead of secondary id as i will copy the user id in it
	// but let's anticipate a request about groups support and avoid any trouble..
	$rendez_vous_url = rendez_vous_get_single_link( $activity->item_id, $activity->secondary_item_id );

	$rendez_vous_title = bp_activity_get_meta( $activity->id, 'rendez_vous_title' );

	// Should only be empty at the time of rendez vous creation
	if ( empty( $rendez_vous_title ) ) {

		$rendez_vous = rendez_vous_get_item( $activity->item_id );
		if ( is_a( $rendez_vous, 'Rendez_Vous_Item' ) ) {
			$rendez_vous_title = $rendez_vous->title;
			bp_activity_update_meta( $activity->id, 'rendez_vous_title', $rendez_vous_title );
		}

	}

	$rendez_vous_link  = '<a href="' . $rendez_vous_url . '">' . $rendez_vous_title . '</a>';

	$user_link = bp_core_get_userlink( $activity->user_id );

	$action_part = __( 'scheduled a new', 'rendez-vous' );

	if ( 'updated_rendez_vous' == $activity->type ) {
		$action_part = __( 'updated a', 'rendez-vous' );
	}

	$action  = sprintf( __( '%1$s %2$s rendez-vous, %3$s', 'rendez-vous' ), $user_link, $action_part, $rendez_vous_link );

	return apply_filters( 'rendez_vous_format_activity_action', $action, $activity );
}

/**
 * Options in front end filters
 *
 * @package Rendez Vous
 * @subpackage Activity
 * 
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_activity_options() {

	$rendez_vous_actions = buddypress()->activity->actions->rendez_vous;

	foreach ( $rendez_vous_actions as $action ) {
		?>
		<option value="<?php echo esc_attr( $action['key'] ) ;?>"><?php echo esc_html( $action['value'] ) ;?></option>
		<?php
	}
}
add_action( 'bp_activity_filter_options',        'rendez_vous_activity_options' );
add_action( 'bp_member_activity_filter_options', 'rendez_vous_activity_options' );

/**
 * Publish!
 *
 * @package Rendez Vous
 * @subpackage Activity
 * 
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_published_activity( $id = 0 , $args = array(), $notify = false, $activity = false ) {
	if ( empty( $id ) || empty( $activity ) )
		return;

	$rendez_vous = rendez_vous_get_item( $id );
	$rendez_vous_url = rendez_vous_get_single_link( $id, $rendez_vous->organizer );

	$rendez_vous_link  = '<a href="' . $rendez_vous_url . '">' . $rendez_vous->title . '</a>';

	$user_link = bp_core_get_userlink( $rendez_vous->organizer );

	$action_part = __( 'scheduled a new', 'rendez-vous' );

	$action  = sprintf( __( '%1$s %2$s rendez-vous, %3$s', 'rendez-vous' ), $user_link, $action_part, $rendez_vous_link );

	$content = false;

	if ( ! empty( $rendez_vous->description ) )
		$content = bp_create_excerpt( $rendez_vous->description );

	$activity_id = bp_activity_add( array(
		'action'            => $action,
		'content'           => $content,
		'component'         => buddypress()->rendez_vous->id,
		'type'              => 'new_rendez_vous',
		'primary_link'      => $rendez_vous_url, // need to check why this is not passed..
		'user_id'           => bp_loggedin_user_id(),
		'item_id'           => $rendez_vous->id, 
		'secondary_item_id' => $rendez_vous->organizer
	) );

	if ( ! empty( $activity_id ) )
		bp_activity_update_meta( $activity_id, 'rendez_vous_title', $rendez_vous->title );

	return true;
}
add_action( 'rendez_vous_after_publish', 'rendez_vous_published_activity', 10, 4 );

/**
 * Updated!
 *
 * @package Rendez Vous
 * @subpackage Activity
 * 
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_updated_activity( $id = 0 , $args = array(), $notify = false, $activity = false ) {
	if ( empty( $id ) || empty( $activity ) )
		return;

	$rdv = rendez_vous();

	if ( empty( $rdv->item->id ) ) {
		$rendez_vous = rendez_vous_get_item( $id );
	} else {
		$rendez_vous = $rdv->item;
	}
	
	$rendez_vous_url = rendez_vous_get_single_link( $id, $rendez_vous->organizer );

	$rendez_vous_link  = '<a href="' . $rendez_vous_url . '">' . $rendez_vous->title . '</a>';

	$user_link = bp_core_get_userlink( $rendez_vous->organizer );

	$action_part = __( 'updated a', 'rendez-vous' );

	$action  = sprintf( __( '%1$s %2$s rendez-vous, %3$s', 'rendez-vous' ), $user_link, $action_part, $rendez_vous_link );

	$activity_id = bp_activity_add( array(
		'action'            => $action,
		'component'         => buddypress()->rendez_vous->id,
		'type'              => 'updated_rendez_vous',
		'primary_link'      => $rendez_vous_url,
		'user_id'           => bp_loggedin_user_id(),
		'item_id'           => $rendez_vous->id, 
		'secondary_item_id' => $rendez_vous->organizer
	) );

	if ( ! empty( $activity_id ) )
		bp_activity_update_meta( $activity_id, 'rendez_vous_title', $rendez_vous->title );

	return true;
}
add_action( 'rendez_vous_after_update', 'rendez_vous_updated_activity', 11, 4 );

/**
 * Deletes activities of a cancelled rendez-vous
 *
 * @package Rendez Vous
 * @subpackage Activity
 * 
 * @since Rendez Vous (1.0.0)
 */
function rendez_vous_delete_item_activities( $rendez_vous_id = 0, $rendez_vous = null ) {
	if ( empty( $rendez_vous_id ) )
		return;

	// No need to delete activities in case of drafts
	if ( ! empty( $rendez_vous ) && 'draft' == $rendez_vous->post_status )
		return;

	bp_activity_delete_by_item_id( array( 'item_id' => $rendez_vous_id ) );
}
add_action( 'rendez_vous_after_delete', 'rendez_vous_delete_item_activities', 10, 2 );
