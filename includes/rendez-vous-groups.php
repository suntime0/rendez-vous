<?php
/**
 * Rendez Vous Groups
 *
 * Groups component
 *
 * @package Rendez Vous
 * @subpackage Groups
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Rendez_Vous_Group' ) && class_exists( 'BP_Group_Extension' ) ) :
/**
 * Rendez Vous group class
 *
 * @package Rendez Vous
 * @subpackage Groups
 *
 * @since Rendez Vous (1.1.0)
 */
class Rendez_Vous_Group extends BP_Group_Extension {

	public $screen  = null;

	/**
	 * Constructor
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 */
	public function __construct() {
		/**
		 * Init the Group Extension vars
		 */
		$this->init_vars();

		/**
		 * Add actions and filters to extend Rendez-vous
		 */
		$this->setup_hooks();
	}

	/** Group extension methods ***************************************************/

	/**
	 * Registers the Rendez-vous group extension and sets some globals
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @uses buddypress() to get the BuddyPress instance
	 * @uses Rendez_Vous_Group->enable_nav_item() to display or not the Rendez-vous nav item for the group
	 * @uses BP_Group_Extension::init()
	 */
	public function init_vars() {
		$bp = buddypress();

		$args = array(
			'slug'              => $bp->rendez_vous->slug,
			'name'              => $bp->rendez_vous->name,
			'visibility'        => 'public',
			'nav_item_position' => 80,
			'enable_nav_item'   => $this->enable_nav_item(),
			'screens'           => array(
				'admin' => array(
					'enabled'          => true,
					'metabox_context'  => 'side',
					'metabox_priority' => 'core'
				),
				'create' => array(
					'position' => 80,
					'enabled'  => true,
				),
				'edit' => array(
					'position' => 80,
					'enabled'  => true,
				),
			)
		);

        parent::init( $args );
	}

	/**
	 * Loads Rendez-vous navigation if the group activated the extension
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @uses   bp_get_current_group_id() to get the group id
	 * @uses   Rendez_Vous_Group::group_get_option() to check if extension is active for the group.
	 * @return bool true if the extension is active for the group, false otherwise
	 */
	public function enable_nav_item() {
		$group_id = bp_get_current_group_id();

		if ( empty( $group_id ) ){
			return false;
		}

		return (bool) self::group_get_option( $group_id, '_rendez_vous_group_activate', false );
	}

	/**
	 * The create screen method
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  int $group_id the group ID
	 * @uses   bp_is_group_creation_step() to make sure it's the extension create step
	 * @uses   bp_get_new_group_id() to get the just created group ID
	 * @uses   Rendez_Vous_Group->edit_screen() to display the group extension settings form
	 */
	public function create_screen( $group_id = null ) {
		// Bail if not looking at this screen
		if ( ! bp_is_group_creation_step( $this->slug ) ) {
			return false;
		}

		// Check for possibly empty group_id
		if ( empty( $group_id ) ) {
			$group_id = bp_get_new_group_id();
		}

		return $this->edit_screen( $group_id );
	}

	/**
	 * The create screen save method
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  int $group_id the group ID
	 * @uses   bp_get_new_group_id() to get the just created group ID
	 * @uses   Rendez_Vous_Group->edit_screen_save() to save the group extension settings
	 */
	public function create_screen_save( $group_id = null ) {
		// Check for possibly empty group_id
		if ( empty( $group_id ) ) {
			$group_id = bp_get_new_group_id();
		}

		return $this->edit_screen_save( $group_id );
	}

	/**
	 * Group extension settings form
	 *
	 * Used in Group Administration, Edit and Create screens
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  int $group_id the group ID
	 * @uses   is_admin() to check if we're in WP Administration
	 * @uses   checked() to add a checked attribute to checkbox if needed
	 * @uses   Rendez_Vous_Group::group_get_option() to get the needed group metas.
	 * @uses   bp_is_group_admin_page() to check if the group edit screen is displayed
	 * @uses   wp_nonce_field() to add a security token to check upon once submitted
	 * @return string html output
	 */
	public function edit_screen( $group_id = null ) {
		if ( empty( $group_id ) ) {
			$group_id = bp_get_current_group_id();
		}

		$is_admin = is_admin();

		if ( ! $is_admin ) : ?>

			<h4><?php printf( esc_html__( '%s group settings', 'rendez-vous' ), $this->name ); ?></h4>

		<?php endif; ?>

		<fieldset>

			<?php if ( $is_admin ) : ?>

				<legend class="screen-reader-text"><?php printf( esc_html__( '%s group settings', 'rendez-vous' ), $this->name ); ?></legend>

			<?php endif; ?>

			<div class="field-group">
				<div class="checkbox">
					<label>
						<label for="_rendez_vous_group_activate">
							<input type="checkbox" id="_rendez_vous_group_activate" name="_rendez_vous_group_activate" value="1" <?php checked( self::group_get_option( $group_id, '_rendez_vous_group_activate', false ) )?>>
								<?php printf( __( 'Activate %s.', 'rendez-vous' ), $this->name );?>
							</input>
						</label>
				</div>
			</div>

			<?php if ( bp_is_group_admin_page() ) : ?>
				<input type="submit" name="save" value="<?php _e( 'Save', 'rendez-vous' );?>" />
			<?php endif; ?>

		</fieldset>

		<?php
		wp_nonce_field( 'groups_settings_save_' . $this->slug, 'rendez_vous_group_admin' );
	}


	/**
	 * Save the settings for the current the group
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param int $group_id the group id we save settings for
	 * @uses  check_admin_referer() to check the request was made on the site
	 * @uses  bp_get_current_group_id() to get the group id
	 * @uses  wp_parse_args() to merge args with defaults
	 * @uses  groups_update_groupmeta() to set the extension option
	 * @uses  bp_is_group_admin_page() to check the group edit screen is displayed
	 * @uses  bp_core_add_message() to give a feedback to the user
	 * @uses  bp_core_redirect() to safely redirect the user
	 * @uses  bp_get_group_permalink() to build the group permalink
	 */
	public function edit_screen_save( $group_id = null ) {

		if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
			return false;
		}

		check_admin_referer( 'groups_settings_save_' . $this->slug, 'rendez_vous_group_admin' );

		if ( empty( $group_id ) ) {
			$group_id = bp_get_current_group_id();
		}

		/* Insert your edit screen save code here */
		$settings = array(
			'_rendez_vous_group_activate' => 0,
		);

		if ( ! empty( $_POST['_rendez_vous_group_activate'] ) ) {
			$s = wp_parse_args( $_POST, $settings );

			$settings = array_intersect_key(
				array_map( 'absint', $s ),
				$settings
			);
		}

		// Save group settings
		foreach ( $settings as $meta_key => $meta_value ) {
			groups_update_groupmeta( $group_id, $meta_key, $meta_value );
		}

		if ( bp_is_group_admin_page() || is_admin() ) {

			// Only redirect on Manage screen
			if ( bp_is_group_admin_page() ) {
				bp_core_add_message( __( 'Settings saved successfully', 'wp-idea-stream' ) );
				bp_core_redirect( bp_get_group_permalink( buddypress()->groups->current_group ) . 'admin/' . $this->slug );
			}
		}
	}

	/**
	 * Adds a Meta Box in Group's Administration screen
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  int $group_id  the group id
	 * @uses   Rendez_Vous_Group->edit_screen() to display the group extension settings form
	 */
	public function admin_screen( $group_id = null ) {
		$this->edit_screen( $group_id );
	}

	/**
	 * Saves the group settings (set in the Meta Box of the Group's Administration screen)
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  int $group_id  the group id
	 * @uses   Rendez_Vous_Group->edit_screen_save() to save the group extension settings
	 */
	public function admin_screen_save( $group_id = null ) {
		$this->edit_screen_save( $group_id );
	}

	public function group_handle_screens() {
		if ( bp_is_group() && bp_is_current_action( $this->slug ) ) {
			$this->screen = rendez_vous_handle_actions();
			rendez_vous()->screens->screen = $this->screen;
		}
	}

	/**
	 * Loads needed Rendez-vous template parts
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @return string html output
	 */
	public function display() {
		if ( ! empty( $this->screen ) )  {
			if ( 'edit' == $this->screen ) {
				?>
				<h1><?php rendez_vous_edit_title();?></h1>
				<?php rendez_vous_edit_content();
			} else if ( 'single' ==  $this->screen ) {
				?>
				<h1><?php rendez_vous_single_title();?></h1>
				<?php rendez_vous_single_content();
			}
		} else {
			?>
			<h1><?php rendez_vous_editor( 'new-rendez-vous', array( 'group_id' => bp_get_current_group_id() ) ); ?></h1>
			<?php rendez_vous_loop();
		}
	}

	/**
	 * We do not use group widgets
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @return boolean false
	 */
	public function widget_display() {
		return false;
	}

	/**
	 * Gets the group meta, use default if meta value is not set
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  int     $group_id the group ID
	 * @param  string  $option   meta key
	 * @param  mixed   $default  the default value to fallback with
	 * @uses   groups_get_groupmeta() to get the meta value
	 * @uses   apply_filters() call "rendez_vous_groups_option{$option}" to override the group meta value
	 * @return mixed             the meta value
	 */
	public static function group_get_option( $group_id = 0, $option = '', $default = '' ) {
		if ( empty( $group_id ) || empty( $option ) ) {
			return false;
		}

		$group_option = groups_get_groupmeta( $group_id, $option );

		if ( '' === $group_option ) {
			$group_option = $default;
		}

		/**
		 * @param   mixed $group_option the meta value
		 * @param   int   $group_id     the group ID
		 */
		return apply_filters( "rendez_vous_groups_option{$option}", $group_option, $group_id );
	}

	/**
	 * [is_rendez_vous description]
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  boolean $retval [description]
	 * @return boolean         [description]
	 */
	public function is_rendez_vous( $retval = false ) {
		if ( bp_is_group() && bp_is_current_action( $this->slug ) ) {
			$retval = true;
		}

		return $retval;
	}

	/**
	 * [map_meta_caps description]
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  array   $caps    [description]
	 * @param  string  $cap     [description]
	 * @param  integer $user_id [description]
	 * @param  array   $args    [description]
	 * @return [type]           [description]
	 */
	public function map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {
		if ( ! bp_is_group() ) {
			return $caps;
		}

		$group = groups_get_current_group();

		switch ( $cap ) {
			case 'publish_rendez_vouss' :
				if ( ! empty( $group->id ) && groups_is_user_member( $user_id, $group->id ) ) {
					$caps = array( 'exist' );
				}
				break;
		}

		return $caps;
	}

	/**
	 * [append_group_id description]
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  array  $args [description]
	 * @return [type]       [description]
	 */
	public function append_group_id( $args = array() ) {
		if ( ! bp_is_group() ) {
			return $args;
		}

		$args['group_id'] = bp_get_current_group_id();

		return $args;
	}

	/**
	 * [group_current_action description]
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  string $action [description]
	 * @return [type]         [description]
	 */
	public function group_current_action( $action = '' ) {
		if ( ! bp_is_group() ) {
			return $action;
		}

		if ( empty( $_GET ) ) {
			$action = 'schedule';
		}

		return $action;
	}

	public function group_edit_rendez_vous( $organizer_id = 0, $args = array() ) {
		if ( ! bp_is_group() || empty( $args['id'] ) ) {
			return $organizer_id;
		}

		$rendez_vous_id = intval( $args['id'] );
		$author = get_post_field( 'post_author', $rendez_vous_id );

		if ( empty( $author ) ) {
			return $organizer_id;
		}

		return $author;
	}

	/**
	 * [group_rendez_vous_link description]
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  integer $id [description]
	 * @return [type]      [description]
	 */
	public function group_rendez_vous_link( $id = 0 ) {
		$link = false;

		if ( empty( $id ) ) {
			return $link;
		}

		$group_id = get_post_meta( $id, '_rendez_vous_group_id', true );

		if ( empty( $group_id ) ) {
			return $link;
		}

		$group = groups_get_current_group();

		if ( empty( $group->id ) || $group_id == $group->id ) {
			$group = groups_get_group( array( 'group_id' => $group_id, 'populate_extras' => false ) );

			$link = trailingslashit( bp_get_group_permalink( $group ) . $this->slug );
		}

		return $link;
	}

	/**
	 * [group_edit_link description]
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  string  $link      [description]
	 * @param  integer $id        [description]
	 * @param  integer $organizer [description]
	 * @return [type]             [description]
	 */
	public function group_edit_link( $link = '', $id = 0, $organizer = 0 ) {
		if ( empty( $id ) ) {
			return $link;
		}

		$group_link = $this->group_rendez_vous_link( $id );

		if ( empty( $group_link ) ) {
			return $link;
		}

		$link = add_query_arg(
			array( 'rdv' => $id, 'action' => 'edit' ),
			$group_link
		);

		return $link;
	}

	/**
	 * [group_view_link description]
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  string  $link      [description]
	 * @param  integer $id        [description]
	 * @param  integer $organizer [description]
	 * @return [type]             [description]
	 */
	public function group_view_link( $link = '', $id = 0, $organizer = 0 ) {
		if ( empty( $id ) ) {
			return $link;
		}

		$group_link = $this->group_rendez_vous_link( $id );

		if ( empty( $group_link ) ) {
			return $link;
		}

		$link = add_query_arg(
			array( 'rdv' => $id ),
			$group_link
		);

		return $link;
	}

	/**
	 * [group_delete_link description]
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  string  $link      [description]
	 * @param  integer $id        [description]
	 * @param  integer $organizer [description]
	 * @return [type]             [description]
	 */
	public function group_delete_link( $link = '', $id = 0, $organizer = 0 ) {
		if ( empty( $id ) ) {
			return $link;
		}

		$group_link = $this->group_rendez_vous_link( $id );

		if ( empty( $group_link ) ) {
			return $link;
		}

		$link = add_query_arg( array( 'rdv' => $id, 'action' => 'delete' ), $group_link );
		$link = wp_nonce_url( $link, 'rendez_vous_delete' );

		return $link;
	}

	public function group_form_action( $action = '', $rendez_vous_id = 0 ) {
		if ( ! bp_is_group() ) {
			return $action;
		}

		$group = groups_get_current_group();

		return trailingslashit( bp_get_group_permalink( $group ) . $this->slug );
	}

	public function group_activity_save_args( $args = array() ) {
		if ( ! bp_is_group() || empty( $args['action'] ) ) {
			return $args;
		}

		$group = groups_get_current_group();

		$group_link = '<a href="' . esc_url( bp_get_group_permalink( $group ) ) . '">' . esc_html( $group->name ) . '</a>';

		$action         = $args['action'] . ' ' . sprintf( __( 'in %s', 'rendez-vous' ), $group_link );
		$rendez_vous_id = $args['item_id'];

		$args = array_merge( $args, array(
			'action'            => $action,
			'component'         => buddypress()->groups->id,
			'item_id'           => $group->id,
			'secondary_item_id' => $rendez_vous_id
		) );

		return $args;
	}

	public function group_activity_delete_args( $args = array() ) {
		if ( ! bp_is_group() || empty( $args['item_id'] ) ) {
			return $args;
		}

		$group = groups_get_current_group();
		$rendez_vous_id = $args['item_id'];

		$args = array(
			'item_id'           => $group->id,
			'secondary_item_id' => $rendez_vous_id,
			'component'         => buddypress()->groups->id,
		);

		return $args;
	}

	/**
	 * [format_activity_action description]
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @param  [type] $action   [description]
	 * @param  [type] $activity [description]
	 * @return [type]           [description]
	 */
	public function format_activity_action( $action, $activity ) {
		// Bail if not a rendez vous activity posted in a group
		if ( buddypress()->groups->id != $activity->component || empty( $action ) ) {
			return $action;
		}

		$group = groups_get_group( array(
			'group_id'        => $activity->item_id,
			'populate_extras' => false,
		) );

		if ( empty( $group ) ) {
			return $action;
		}

		$group_link = '<a href="' . esc_url( bp_get_group_permalink( $group ) ) . '">' . esc_html( $group->name ) . '</a>';

		$action .= ' ' . sprintf( __( 'in %s', 'rendez-vous' ), $group_link );
		return $action;
	}

	/**
	 * [setup_hooks description]
	 *
	 * @package Rendez Vous
	 * @subpackage Groups
	 *
	 * @since Rendez Vous (1.1.0)
	 *
	 * @return [type] [description]
	 */
	public function setup_hooks() {
		add_action( 'bp_screens',                                 array( $this, 'group_handle_screens' ),       20    );
		add_filter( 'rendez_vous_load_scripts',                   array( $this, 'is_rendez_vous' ),             10, 1 );
		add_filter( 'rendez_vous_load_editor',                    array( $this, 'is_rendez_vous' ),             10, 1 );
		add_filter( 'rendez_vous_map_meta_caps',                  array( $this, 'map_meta_caps' ),              10, 4 );
		add_filter( 'rendez_vous_current_action',                 array( $this, 'group_current_action' ),       10, 1 );
		add_filter( 'rendez_vous_edit_action_organizer_id',       array( $this, 'group_edit_rendez_vous' ),     10, 2 );
		add_filter( 'bp_before_rendez_vouss_has_args_parse_args', array( $this, 'append_group_id' ),            10, 1 );
		add_filter( 'rendez_vous_get_edit_link',                  array( $this, 'group_edit_link' ),            10, 3 );
		add_filter( 'rendez_vous_get_single_link',                array( $this, 'group_view_link' ),            10, 3 );
		add_filter( 'rendez_vous_get_delete_link',                array( $this, 'group_delete_link' ),          10, 3 );
		add_filter( 'rendez_vous_single_the_form_action',         array( $this, 'group_form_action' ),          10, 2 );
		add_filter( 'rendez_vous_published_activity_args',        array( $this, 'group_activity_save_args' ),   10, 1 );
		add_filter( 'rendez_vous_updated_activity_args',          array( $this, 'group_activity_save_args' ),   10, 1 );
		add_filter( 'rendez_vous_delete_item_activities_args',    array( $this, 'group_activity_delete_args' ), 10, 1 );
		add_filter( 'rendez_vous_format_activity_action',         array( $this, 'format_activity_action' ),     10, 3 );
	}
}

endif ;

/**
 * [rendez_vous_register_group_extension description]
 *
 * @package Rendez Vous
 * @subpackage Groups
 *
 * @since Rendez Vous (1.1.0)
 *
 * @return [type] [description]
 */
function rendez_vous_register_group_extension() {
	bp_register_group_extension( 'Rendez_Vous_Group' );
}
add_action( 'bp_init', 'rendez_vous_register_group_extension' );

/**
 * [rendez_vous_groups_activity_actions description]
 *
 * @package Rendez Vous
 * @subpackage Groups
 *
 * @since Rendez Vous (1.1.0)
 */
function rendez_vous_groups_activity_actions() {
	$bp = buddypress();

	// Bail if activity is not active
	if ( ! bp_is_active( 'activity' ) ) {
		return false;
	}

	bp_activity_set_action(
		$bp->groups->id,
		'new_rendez_vous',
		__( 'New rendez-vous in a group', 'rendez-vous' ),
		'rendez_vous_format_activity_action',
		__( 'New rendez-vous', 'rendez-vous' ),
		array( 'group', 'member' )
	);

	bp_activity_set_action(
		$bp->groups->id,
		'updated_rendez_vous',
		__( 'Updated a rendez-vous in a group', 'rendez-vous' ),
		'rendez_vous_format_activity_action',
		__( 'Updated a rendez-vous', 'rendez-vous' ),
		array( 'group', 'member_groups' )
	);
}
add_action( 'rendez_vous_register_activity_actions', 'rendez_vous_groups_activity_actions', 20 );
