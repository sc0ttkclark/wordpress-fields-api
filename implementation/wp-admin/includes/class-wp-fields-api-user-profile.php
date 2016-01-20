<?php
/**
 * This is an implementation for Fields API for the User Profile screen in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_User_Profile
 */
class WP_Fields_API_User_Profile {

	public function __construct() {

		$this->register_controls();

		add_action( 'profile_update', array( $this, 'save_fields' ), 10, 2 );

	}

	/**
	 * Handle saving of user profile fields
	 *
	 * @param int   $user_id
	 * @param array $old_user_data
	 */
	public function save_fields( $user_id, $old_user_data ) {

		if ( ! empty( $_REQUEST['wp_fields_api_fields_save'] ) && false !== wp_verify_nonce( $_REQUEST['wp_fields_api_fields_save'], 'wp_fields_api_user_profile' ) ) {
			/**
			 * @var $wp_fields WP_Fields_API
			 */
			global $wp_fields;

			$controls = $wp_fields->get_controls( 'user' );

			foreach ( $controls as $control ) {
				if ( empty( $control->field ) ) {
					continue;
				}

				$field = $control->field;

				// Get value from $_POST
				$value = null;

				if ( ! empty( $_POST[ 'field_' . $control->id ] ) ) {
					$value = $_POST[ 'field_' . $control->id ];
				}

				// Sanitize
				$value = $field->sanitize( $value );

				// Save value
				$field->save( $value, $user_id );
			}
		}

	}

	/**
	 * Register controls for User Profiles
	 *
	 * @todo Move out of wp-admin implementation
	 */
	public function register_controls() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		// Register control types
		$wp_fields->register_control_type( 'user-color-scheme', 'WP_Fields_API_Color_Scheme_Control' );
		$wp_fields->register_control_type( 'user-role', 'WP_Fields_API_User_Role_Control' );
		$wp_fields->register_control_type( 'user-super-admin', 'WP_Fields_API_User_Super_Admin_Control' );
		$wp_fields->register_control_type( 'user-display-name', 'WP_Fields_API_User_Display_Name_Control' );
		$wp_fields->register_control_type( 'user-email', 'WP_Fields_API_User_Email_Control' );
		$wp_fields->register_control_type( 'user-password', 'WP_Fields_API_User_Password_Control' );
		$wp_fields->register_control_type( 'user-sessions', 'WP_Fields_API_User_Sessions_Control' );
		$wp_fields->register_control_type( 'user-capabilities', 'WP_Fields_API_User_Capabilities_Control' );

		// Add Edit Profile screen
		$wp_fields->add_screen( 'user', 'edit-profile' );

		////////////////////////////
		// Core: Personal Options //
		////////////////////////////

		$wp_fields->add_section( 'user', 'personal-options', 'edit-profile', array(
			'title' => __( 'Personal Options' ),
			// @todo Needs action compatibility for personal_options( $profileuser )
			// @todo Needs action compatibility for profile_personal_options( $profileuser ) if IS_PROFILE_PAGE
		) );

		$field_args = array(
			'sanitize_callback' => array( $this, 'sanitize_rich_editing' ),
			'control'                   => array(
				'type'                  => 'checkbox',
				'section'               => 'personal-options',
				'label'                 => __( 'Visual Editor' ),
				'description'           => __( 'Disable the visual editor when writing' ),
				'capabilities_callback' => array( $this, 'capability_is_subscriber_editing_profile' ),
				'checkbox_value'        => 'false',
			),
		);

		$wp_fields->add_field( 'user', 'rich_editing', 'edit-profile', $field_args );

		$field_args = array(
			'control' => array(
				'type'                  => 'user-color-scheme',
				'section'               => 'personal-options',
				'label'                 => __( 'Admin Color Scheme' ),
				'description'           => __( 'Disable the visual editor when writing' ),
				'capabilities_callback' => array( $this, 'capability_has_color_scheme_control' ),
			),
		);

		$wp_fields->add_field( 'user', 'admin_color', 'edit-profile', $field_args );

		$field_args = array(
			'sanitize_callback' => array( $this, 'sanitize_comment_shortcuts' ),
			'control'                   => array(
				'type'                  => 'checkbox',
				'section'               => 'personal-options',
				'label'                 => __( 'Keyboard Shortcuts' ),
				'description'           => __( 'Enable keyboard shortcuts for comment moderation.' ) . ' ' . __( '<a href="https://codex.wordpress.org/Keyboard_Shortcuts" target="_blank">More information</a>' ),
				'capabilities_callback' => array( $this, 'capability_is_subscriber_editing_profile' ),
				'checkbox_value'        => 'true',
			),
		);

		$wp_fields->add_field( 'user', 'comment_shortcuts', 'edit-profile', $field_args );

		$field_args = array(
			'sanitize_callback' => array( $this, 'sanitize_admin_bar_front' ),
			'control'                   => array(
				'type'           => 'checkbox',
				'section'        => 'personal-options',
				'label'          => __( 'Toolbar' ),
				'description'    => __( 'Show Toolbar when viewing site' ),
				'checkbox_value' => 'true',
			),
		);

		$wp_fields->add_field( 'user', 'admin_bar_front', 'edit-profile', $field_args );

		////////////////
		// Core: Name //
		////////////////

		$wp_fields->add_section( 'user', 'name', 'edit-profile', array(
			'title' => __( 'Name' ),
		) );

		$field_args = array(
			// @todo Needs validation callback
			'control' => array(
				'type'        => 'text',
				'section'     => 'name',
				'label'       => __( 'Username' ),
				'description' => __( 'Usernames cannot be changed.' ),
				'input_attrs' => array(
					'disabled' => 'disabled',
				),
			),
		);

		$wp_fields->add_field( 'user', 'user_login', 'edit-profile', $field_args );

		$field_args = array(
			'control' => array(
				'type'                  => 'user-role',
				'section'               => 'name',
				'label'                 => __( 'Role' ),
				'capabilities_callback' => array( $this, 'capability_show_roles' ),
			),
		);

		$wp_fields->add_field( 'user', 'role', 'edit-profile', $field_args );

		$field_args = array(
			'value_callback'        => array( $this, 'value_is_super_admin' ),
			'update_value_callback' => array( $this, 'update_value_is_super_admin' ),
			'control'               => array(
				'type'                  => 'user-super-admin',
				'section'               => 'name',
				'label'                 => __( 'Super Admin' ),
				'description'           => __( 'Grant this user super admin privileges for the Network.' ),
				'capabilities_callback' => array( $this, 'capability_can_grant_super_admin' ),
			),
		);

		$wp_fields->add_field( 'user', 'super_admin', 'edit-profile', $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'text',
				'section' => 'name',
				'label'   => __( 'First Name' ),
			),
		);

		$wp_fields->add_field( 'user', 'first_name', 'edit-profile', $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'text',
				'section' => 'name',
				'label'   => __( 'Last Name' ),
			),
		);

		$wp_fields->add_field( 'user', 'last_name', 'edit-profile', $field_args );

		$field_args = array(
			'control' => array(
				'type'        => 'text',
				'section'     => 'name',
				'label'       => __( 'Nickname' ),
				'description' => __( '(required)' ),
			),
		);

		$wp_fields->add_field( 'user', 'user_nickname', 'edit-profile', $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'user-display-name',
				'section' => 'name',
				'label'   => __( 'Display name publicly as' ),
			),
		);

		$wp_fields->add_field( 'user', 'display_name', 'edit-profile', $field_args );

		////////////////////////
		// Core: Contact Info //
		////////////////////////

		$wp_fields->add_section( 'user', 'contact-info', 'edit-profile', array(
			'title' => __( 'Contact Info' ),
		) );

		$field_args = array(
			// @todo Needs validation callback
			'control' => array(
				'type'        => 'user-email',
				'section'     => 'contact-info',
				'label'       => __( 'E-mail' ),
				'description' => __( '(required)' ),
			),
		);

		$wp_fields->add_field( 'user', 'user_email', 'edit-profile', $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'text',
				'section' => 'contact-info',
				'label'   => __( 'Website' ),
			),
		);

		$wp_fields->add_field( 'user', 'user_url', 'edit-profile', $field_args );

		$contact_methods = wp_get_user_contact_methods();

		foreach ( $contact_methods as $method => $label ) {
			/**
			 * Filter a user contactmethod label.
			 *
			 * The dynamic portion of the filter hook, `$name`, refers to
			 * each of the keys in the contactmethods array.
			 *
			 * @since 2.9.0
			 *
			 * @param string $label The translatable label for the contactmethod.
			 */
			$label = apply_filters( "user_{$method}_label", $label );

			$field_args = array(
				'control' => array(
					'type'    => 'text',
					'section' => 'contact-info',
					'label'   => $label,
				),
			);

			$wp_fields->add_field( 'user', $method, 'edit-profile', $field_args );
		}

		/////////////////
		// Core: About //
		/////////////////

		$about_title = __( 'About the user' );

		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			$about_title = __( 'About Yourself' );
		}

		$wp_fields->add_section( 'user', 'about', 'edit-profile', array(
			'title' => $about_title,
		) );

		$field_args = array(
			'control' => array(
				'type'        => 'text',
				'section'     => 'about',
				'label'       => __( 'Biographical Info' ),
				'description' => __( 'Share a little biographical information to fill out your profile. This may be shown publicly.' ),
			),
		);

		$wp_fields->add_field( 'user', 'description', 'edit-profile', $field_args );

		//////////////////////////////
		// Core: Account Management //
		//////////////////////////////

		$wp_fields->add_section( 'user', 'account-management', 'edit-profile', array(
			'title'                 => __( 'Account Management' ),
			'capabilities_callback' => array( $this, 'capability_show_password_fields' ),
		) );

		$field_args = array(
			'control' => array(
				'type'    => 'user-password',
				'section' => 'account-management',
				'label'   => __( 'Password' ),
			),
		);

		$wp_fields->add_field( 'user', 'user_pass', 'edit-profile', $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'user-sessions',
				'section' => 'account-management',
				'label'   => __( 'Sessions' ),
			),
		);

		// If password fields not shown, show Sessions under About
		// @todo Change which section this control is in if password fields not shown
		/*if ( ! $show_password_fields ) {
			$field_args['control']['section'] = 'about';
		}*/

		$wp_fields->add_field( 'user', 'sessions', 'edit-profile', $field_args );

		// @todo Figure out how best to run actions after section
		//if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
		/**
		 * Fires after the 'About Yourself' settings table on the 'Your Profile' editing screen.
		 *
		 * The action only fires if the current user is editing their own profile.
		 *
		 * @since 2.0.0
		 *
		 * @param WP_User $profileuser The current WP_User object.
		 */
		//do_action( 'show_user_profile', $profileuser );
		//} else {
		/**
		 * Fires after the 'About the User' settings table on the 'Edit User' screen.
		 *
		 * @since 2.0.0
		 *
		 * @param WP_User $profileuser The current WP_User object.
		 */
		//do_action( 'edit_user_profile', $profileuser );
		//}

		///////////////////////////////////
		// Core: Additional Capabilities //
		///////////////////////////////////

		$wp_fields->add_section( 'user', 'additional-capabilities', 'edit-profile', array(
			'title'                 => __( 'Additional Capabilities' ),
			'capabilities_callback' => array( $this, 'capability_show_capabilities' ),
		) );

		$field_args = array(
			'control' => array(
				'type'    => 'user-capabilities',
				'section' => 'additional-capabilities',
				'label'   => __( 'Capabilities' ),
			),
		);

		$wp_fields->add_field( 'user', 'capabilities', 'edit-profile', $field_args );

		//////////////
		// Examples //
		//////////////

		// Section
		$wp_fields->add_section( 'user', 'example-my-fields', 'edit-profile', array(
			'title' => __( 'Fields API Example - My Fields' ),
		) );

		// Add example for each control type
		$control_types = array(
			'text',
			'checkbox',
			'multi-checkbox',
			'radio',
			'select',
			'dropdown-pages',
			'color',
			'media',
			'upload',
			'image',
		);

		foreach ( $control_types as $control_type ) {
			$id    = 'example_my_' . $control_type . '_field';
			$label = sprintf( __( '%s Field' ), ucwords( str_replace( '-', ' ', $control_type ) ) );

			$field_args = array(
				// Add a control to the field at the same time
				'control' => array(
					'type'    => $control_type,
					'section' => 'example-my-fields',
					'label'   => $label,
				),
			);

			$wp_fields->add_field( 'user', $id, 'edit-profile', $field_args );
		}

	}

	/**
	 * Controls hidden if subscriber is editing their profile logic
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_is_subscriber_editing_profile( $control ) {

		$has_access = true;

		if ( is_admin() ) {
			$is_user_an_editor = ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' );

			if ( $is_user_an_editor && defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
				$has_access = false;
			}
		}

		return $has_access;

	}

	/**
	 * Control hidden if no admin css colors AND color scheme picker set.
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_has_color_scheme_control( $control ) {

		/**
		 * @var $_wp_admin_css_colors object[]
		 */
		global $_wp_admin_css_colors;

		$has_access = false;

		if ( 1 < count( $_wp_admin_css_colors ) && has_action( 'admin_color_scheme_picker' ) ) {
			$has_access = true;
		}

		return $has_access;

	}

	/**
	 * Control only visible if editing another user outside of the network admin.
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_show_roles( $control ) {

		$has_access = false;

		if ( ( defined( 'IS_PROFILE_PAGE' ) && ! IS_PROFILE_PAGE ) && ! is_network_admin() ) {
			$has_access = true;
		}

		return $has_access;

	}

	/**
	 * Control only visible if in network admin and can manage network options, as long as super_admins is not being overridden.
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_can_grant_super_admin( $control ) {

		// @todo Setup $profileuser correctly
		$profileuser = new stdClass;

		/**
		 * @var $super_admins string[]
		 */
		global $super_admins;

		$has_access = false;

		if ( is_multisite() && is_network_admin() && ( defined( 'IS_PROFILE_PAGE' ) && ! IS_PROFILE_PAGE ) && current_user_can( 'manage_network_options' ) && ! isset( $super_admins ) && $profileuser->user_email != get_site_option( 'admin_email' ) ) {
			$has_access = true;
		}

		return $has_access;

	}

	/**
	 * Control only visible if password fields are shown.
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_show_password_fields( $control ) {

		// @todo Setup $profileuser correctly
		$profileuser = new stdClass;

		/** This filter is documented in wp-admin/user-new.php */
		$show_password_fields = apply_filters( 'show_password_fields', true, $profileuser );

		$has_access = false;

		if ( $show_password_fields ) {
			$has_access = true;
		}

		return $has_access;

	}

	/**
	 * Control only visible if additional capabilities can be shown and total number of capabilities are greater than total number of roles.
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_show_capabilities( $control ) {

		// @todo Setup $profileuser correctly
		$profileuser = new stdClass;

		$total_roles = count( $profileuser->roles );
		$total_caps = count( $profileuser->caps );

		/**
		 * Filter whether to display additional capabilities for the user.
		 *
		 * The 'Additional Capabilities' section will only be enabled if
		 * the number of the user's capabilities exceeds their number of
		 * of roles.
		 *
		 * @since 2.8.0
		 *
		 * @param bool    $enable      Whether to display the capabilities. Default true.
		 * @param WP_User $profileuser The current WP_User object.
		 */
		$display_capabilities = apply_filters( 'additional_capabilities_display', true, $profileuser );

		$has_access = false;

		if ( $total_roles < $total_caps && $display_capabilities ) {
			$has_access = true;
		}

		return $has_access;

	}

	/**
	 * Override the value of the field for whether a user is a super admin or not
	 *
	 * @param int                 $item_id
	 * @param WP_Fields_API_Field $field
	 *
	 * @return mixed
	 */
	public function value_is_super_admin( $item_id, $field ) {

		$value = 0;

		if ( is_multisite() && is_super_admin() ) {
			$value = 1;
		}

		return $value;

	}

	/**
	 * Override the value of the field being updated
	 *
	 * @param mixed               $value
	 * @param int                 $item_id
	 * @param WP_Fields_API_Field $field
	 */
	public function sanitize_rich_editing( $value, $item_id, $field ) {

		if ( ! empty( $value ) && 'false' == $value ) {
			$value = 'false';
		} else {
			$value = 'true';
		}

		return $value;

	}

	/**
	 * Override the value of the field being updated
	 *
	 * @param mixed               $value
	 * @param int                 $item_id
	 * @param WP_Fields_API_Field $field
	 */
	public function sanitize_admin_bar_front( $value, $item_id, $field ) {

		if ( ! empty( $value ) && 'true' == $value ) {
			$value = 'true';
		} else {
			$value = 'false';
		}

		return $value;

	}

	/**
	 * Override the value of the field being updated
	 *
	 * @param mixed               $value
	 * @param int                 $item_id
	 * @param WP_Fields_API_Field $field
	 */
	public function sanitize_comment_shortcuts( $value, $item_id, $field ) {

		if ( ! empty( $value ) && 'true' == $value ) {
			$value = 'true';
		} else {
			$value = '';
		}

		return $value;

	}

	/**
	 * Override the value update of the field for whether a user is to be a super admin or not
	 *
	 * @param mixed               $value
	 * @param int                 $item_id
	 * @param WP_Fields_API_Field $field
	 */
	public function update_value_is_super_admin( $value, $item_id, $field ) {

		$is_super_admin = is_super_admin( $item_id );

		if ( ! empty( $value ) && ! $is_super_admin ) {
			// Make super admin if not already a super admin
			grant_super_admin( $item_id );
		} elseif ( $is_super_admin ) {
			// Revoke super admin if currently a super admin
			revoke_super_admin( $item_id );
		}

	}

}

/**
 * Fields API Color Scheme Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Color_Scheme_Control extends WP_Fields_API_Radio_Control {

	/**
	 * Setup color scheme choices for use by control
	 */
	public function choices() {

		/**
		 * @var $_wp_admin_css_colors object[]
		 */
		global $_wp_admin_css_colors;

		$choices = array();

		ksort( $_wp_admin_css_colors );

		if ( isset( $_wp_admin_css_colors['fresh'] ) ) {
			// Set Default ('fresh') and Light should go first.
			$_wp_admin_css_colors = array_filter( array_merge( array(
				'fresh' => '',
				'light' => ''
			), $_wp_admin_css_colors ) );
		}

		foreach ( $_wp_admin_css_colors as $color => $color_info ) {
			$choices[ $color ] = $color_info->name;
		}

		return $choices;

	}

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

		// @todo Get $user_id properly
		$user_id = 0;

		/**
		 * Fires in the 'Admin Color Scheme' section of the user editing screen.
		 *
		 * The section is only enabled if a callback is hooked to the action,
		 * and if there is more than one defined color scheme for the admin.
		 *
		 * @since 3.0.0
		 * @since 3.8.1 Added `$user_id` parameter.
		 *
		 * @param int $user_id The user ID.
		 */
		do_action( 'admin_color_scheme_picker', $user_id );

	}

}

/**
 * Fields API User Role Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Role_Control extends WP_Fields_API_Select_Control {

	/**
	 * Setup color scheme choices for use by control
	 */
	public function choices() {

		$choices = array(
			'' => __( '&mdash; No role for this site &mdash;' ),
		);

		$editable_roles = array_reverse( get_editable_roles() );

		foreach ( $editable_roles as $role => $details ) {
			$name = translate_user_role( $details['name'] );

			$choices[ $role ] = $name;
		}

		return $choices;

	}

}

/**
 * Fields API User Super Admin Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Super_Admin_Control extends WP_Fields_API_Checkbox_Control {

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

		// @todo Setup $profileuser correctly
		$profileuser = new stdClass;

		if ( $profileuser->user_email == get_site_option( 'admin_email' ) && is_super_admin( $profileuser->ID ) ) {
			echo '<p>' . __( 'Super admin privileges cannot be removed because this user has the network admin email.' ) . '</p>';
		} else {
			parent::render_content();
		}

	}

}

/**
 * Fields API User Display Name Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Display_Name_Control extends WP_Fields_API_Select_Control {

	/**
	 * Setup color scheme choices for use by control
	 */
	public function choices() {

		// @todo Setup $profileuser correctly
		$profileuser = new stdClass;

		$choices = array();

		$choices['display_nickname'] = $profileuser->nickname;
		$choices['display_username'] = $profileuser->user_login;

		if ( ! empty( $profileuser->first_name ) ) {
			$choices['display_firstname'] = $profileuser->first_name;
		}

		if ( ! empty( $profileuser->last_name ) ) {
			$choices['display_lastname'] = $profileuser->last_name;
		}

		if ( ! empty( $profileuser->first_name ) && ! empty( $profileuser->last_name ) ) {
			$choices['display_firstlast'] = $profileuser->first_name . ' ' . $profileuser->last_name;
			$choices['display_lastfirst'] = $profileuser->last_name . ' ' . $profileuser->first_name;
		}

		// Only add this if it isn't duplicated elsewhere
		if ( ! in_array( $profileuser->display_name, $choices ) ) {
			$first_option = array(
				'display_displayname' => $profileuser->display_name,
			);

			$choices = array_merge( $first_option, $choices );
		}

		$choices = array_map( 'trim', $choices );
		$choices = array_filter( $choices );
		$choices = array_unique( $choices );

		return $choices;

	}

}

/**
 * Fields API User Email Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Email_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

		// @todo Setup $current_user correctly
		$current_user = new stdClass;

		// @todo Setup $profileuser correctly
		$profileuser = new stdClass;

		parent::render_content();

		$new_email = get_option( $current_user->ID . '_new_email' );

		if ( $new_email && $new_email['newemail'] != $current_user->user_email && $profileuser->ID == $current_user->ID ) {
			echo '<div class="updated inline"><p>';

			printf( __( 'There is a pending change of your e-mail to %1$s. <a href="%2$s">Cancel</a>' ), '<code>' . $new_email['newemail'] . '</code>', esc_url( self_admin_url( 'profile.php?dismiss=' . $current_user->ID . '_new_email' ) ) );

			echo '</p></div>';
		}

	}

}

/**
 * Fields API User Password Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Password_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

?>
	<table class="form-table">
		<tr id="password" class="user-pass1-wrap">
			<th><label for="pass1"><?php _e( 'New Password' ); ?></label></th>
			<td>
				<input class="hidden" value=" " /><!-- #24364 workaround -->
				<button type="button" class="button button-secondary wp-generate-pw hide-if-no-js"><?php _e( 'Generate Password' ); ?></button>
				<div class="wp-pwd hide-if-js">
					<span class="password-input-wrapper">
						<input type="password" name="pass1" id="pass1" class="regular-text" value="" autocomplete="off" data-pw="<?php echo esc_attr( wp_generate_password( 24 ) ); ?>" aria-describedby="pass-strength-result" />
					</span>
					<button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password' ); ?>">
						<span class="dashicons dashicons-hidden"></span>
						<span class="text"><?php _e( 'Hide' ); ?></span>
					</button>
					<button type="button" class="button button-secondary wp-cancel-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Cancel password change' ); ?>">
						<span class="text"><?php _e( 'Cancel' ); ?></span>
					</button>
					<div style="display:none" id="pass-strength-result" aria-live="polite"></div>
				</div>
			</td>
		</tr>
		<tr class="user-pass2-wrap hide-if-js">
			<th scope="row"><label for="pass2"><?php _e( 'Repeat New Password' ); ?></label></th>
			<td>
			<input name="pass2" type="password" id="pass2" class="regular-text" value="" autocomplete="off" />
			<p class="description"><?php _e( 'Type your new password again.' ); ?></p>
			</td>
		</tr>
		<tr class="pw-weak">
			<th><?php _e( 'Confirm Password' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="pw_weak" class="pw-checkbox" />
					<?php _e( 'Confirm use of weak password' ); ?>
				</label>
			</td>
		</tr>
	</table>

	<script type="text/javascript">
		if (window.location.hash == '#password') {
			document.getElementById('pass1').focus();
		}
	</script>
<?php

	}

}

/**
 * Fields API User Sessions Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Sessions_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

		// @todo Setup $profileuser correctly
		$profileuser = new stdClass;

		/**
		 * @var WP_User_Meta_Session_Tokens $sessions
		 */
		$sessions = WP_Session_Tokens::get_instance( $profileuser->ID );
?>
	<?php if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE && count( $sessions->get_all() ) === 1 ) : ?>
		<div aria-live="assertive">
			<div class="destroy-sessions"><button type="button" disabled class="button button-secondary"><?php _e( 'Log Out Everywhere Else' ); ?></button></div>
			<p class="description">
				<?php _e( 'You are only logged in at this location.' ); ?>
			</p>
		</div>
	<?php elseif ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE && count( $sessions->get_all() ) > 1 ) : ?>
		<div aria-live="assertive">
			<div class="destroy-sessions"><button type="button" class="button button-secondary" id="destroy-sessions"><?php _e( 'Log Out Everywhere Else' ); ?></button></div>
			<p class="description">
				<?php _e( 'Did you lose your phone or leave your account logged in at a public computer? You can log out everywhere else, and stay logged in here.' ); ?>
			</p>
		</div>
	<?php elseif ( defined( 'IS_PROFILE_PAGE' ) && ! IS_PROFILE_PAGE && $sessions->get_all() ) : ?>
		<p><button type="button" class="button button-secondary" id="destroy-sessions"><?php _e( 'Log Out Everywhere' ); ?></button></p>
		<p class="description">
			<?php
			/* translators: 1: User's display name. */
			printf( __( 'Log %s out of all locations.' ), $profileuser->display_name );
			?>
		</p>
	<?php endif; ?>
<?php

	}

}

/**
 * Fields API User Capabilities Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_User_Capabilities_Control extends WP_Fields_API_Control {

	/**
	 * {@inheritdoc}
	 */
	public function render_content() {

		// @todo Setup $profileuser correctly
		$profileuser = new stdClass;

		// @todo Setup $wp_roles correctly
		$wp_roles = new stdClass;

		$output = array();

		foreach ( $profileuser->caps as $cap => $value ) {
			if ( ! $wp_roles->is_role( $cap ) ) {
				if ( ! $value ) {
					$cap = sprintf( __( 'Denied: %s' ), $cap );
				}

				$output[] = $cap;
			}
		}

		$output = implode( ', ', $output );

		echo $output;

	}

}