<?php
/**
 * This is an implementation for Fields API for the User Edit Profile screen in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Screen_User_Edit
 */
class WP_Fields_API_Screen_User_Edit extends WP_Fields_API_Screen {

	/**
	 * {@inheritdoc}
	 */
	public function register_control_types( $wp_fields ) {

		$control_type_dir = WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/control-types/user/';

		// Include control types
		require_once $control_type_dir . 'class-wp-fields-api-user-color-scheme-control.php';
		require_once $control_type_dir . 'class-wp-fields-api-user-role-control.php';
		require_once $control_type_dir . 'class-wp-fields-api-user-super-admin-control.php';
		require_once $control_type_dir . 'class-wp-fields-api-user-display-name-control.php';
		require_once $control_type_dir . 'class-wp-fields-api-user-email-control.php';
		require_once $control_type_dir . 'class-wp-fields-api-user-password-control.php';
		require_once $control_type_dir . 'class-wp-fields-api-user-sessions-control.php';
		require_once $control_type_dir . 'class-wp-fields-api-user-capabilities-control.php';

		// Register control types
		$wp_fields->register_control_type( 'user-color-scheme', 'WP_Fields_API_User_Color_Scheme_Control' );
		$wp_fields->register_control_type( 'user-role', 'WP_Fields_API_User_Role_Control' );
		$wp_fields->register_control_type( 'user-super-admin', 'WP_Fields_API_User_Super_Admin_Control' );
		$wp_fields->register_control_type( 'user-display-name', 'WP_Fields_API_User_Display_Name_Control' );
		$wp_fields->register_control_type( 'user-email', 'WP_Fields_API_User_Email_Control' );
		$wp_fields->register_control_type( 'user-password', 'WP_Fields_API_User_Password_Control' );
		$wp_fields->register_control_type( 'user-sessions', 'WP_Fields_API_User_Sessions_Control' );
		$wp_fields->register_control_type( 'user-capabilities', 'WP_Fields_API_User_Capabilities_Control' );

	}

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		// @todo Saving: Figure out compatibility with edit_user usage in user-edit.php
		// @todo Saving: Hook into personal_options_update on save
		// @todo Saving: Hook into edit_user_profile_update on save

		$this->register_control_types( $wp_fields );

		////////////////////////////
		// Core: Personal Options //
		////////////////////////////

		$wp_fields->add_section( $this->object_type, 'personal-options', null, array(
			'title'  => __( 'Personal Options' ),
			'screen' => $this->id,
			// @todo Needs action compatibility for personal_options( $profileuser )
			// @todo Needs action compatibility for profile_personal_options( $profileuser ) if IS_PROFILE_PAGE
		) );

		$field_args = array(
			'sanitize_callback' => array( $this, 'sanitize_rich_editing' ),
			'control'           => array(
				'type'                  => 'checkbox',
				'section'               => 'personal-options',
				'label'                 => __( 'Visual Editor' ),
				'description'           => __( 'Disable the visual editor when writing' ),
				'capabilities_callback' => array( $this, 'capability_is_subscriber_editing_profile' ),
				'checkbox_value'        => 'false',
			),
		);

		$wp_fields->add_field( $this->object_type, 'rich_editing', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'                  => 'user-color-scheme',
				'section'               => 'personal-options',
				'label'                 => __( 'Admin Color Scheme' ),
				'description'           => __( 'Disable the visual editor when writing' ),
				'capabilities_callback' => array( $this, 'capability_has_color_scheme_control' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'admin_color', null, $field_args );

		$field_args = array(
			'sanitize_callback' => array( $this, 'sanitize_comment_shortcuts' ),
			'control'           => array(
				'type'                  => 'checkbox',
				'section'               => 'personal-options',
				'label'                 => __( 'Keyboard Shortcuts' ),
				'description'           => __( 'Enable keyboard shortcuts for comment moderation.' ) . ' ' . __( '<a href="https://codex.wordpress.org/Keyboard_Shortcuts" target="_blank">More information</a>' ),
				'capabilities_callback' => array( $this, 'capability_is_subscriber_editing_profile' ),
				'checkbox_value'        => 'true',
			),
		);

		$wp_fields->add_field( $this->object_type, 'comment_shortcuts', null, $field_args );

		$field_args = array(
			'sanitize_callback' => array( $this, 'sanitize_admin_bar_front' ),
			'control'           => array(
				'type'           => 'checkbox',
				'section'        => 'personal-options',
				'label'          => __( 'Toolbar' ),
				'description'    => __( 'Show Toolbar when viewing site' ),
				'checkbox_value' => 'true',
			),
		);

		$wp_fields->add_field( $this->object_type, 'admin_bar_front', null, $field_args );

		////////////////
		// Core: Name //
		////////////////

		$wp_fields->add_section( $this->object_type, 'name', null, array(
			'title'  => __( 'Name' ),
			'screen' => $this->id,
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

		$wp_fields->add_field( $this->object_type, 'user_login', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'                  => 'user-role',
				'section'               => 'name',
				'label'                 => __( 'Role' ),
				'capabilities_callback' => array( $this, 'capability_show_roles' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'role', null, $field_args );

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

		$wp_fields->add_field( $this->object_type, 'super_admin', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'text',
				'section' => 'name',
				'label'   => __( 'First Name' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'first_name', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'text',
				'section' => 'name',
				'label'   => __( 'Last Name' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'last_name', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'        => 'text',
				'section'     => 'name',
				'label'       => __( 'Nickname' ),
				'description' => __( '(required)' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'user_nickname', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'user-display-name',
				'section' => 'name',
				'label'   => __( 'Display name publicly as' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'display_name', null, $field_args );

		////////////////////////
		// Core: Contact Info //
		////////////////////////

		$wp_fields->add_section( $this->object_type, 'contact-info', null, array(
			'title'  => __( 'Contact Info' ),
			'screen' => $this->id,
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

		$wp_fields->add_field( $this->object_type, 'user_email', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'    => 'text',
				'section' => 'contact-info',
				'label'   => __( 'Website' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'user_url', null, $field_args );

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

			$wp_fields->add_field( $this->object_type, $method, null, $field_args );
		}

		/////////////////
		// Core: About //
		/////////////////

		$about_title = __( 'About the user' );

		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			$about_title = __( 'About Yourself' );
		}

		$wp_fields->add_section( $this->object_type, 'about', null, array(
			'title'  => $about_title,
			'screen' => $this->id,
		) );

		$field_args = array(
			'control' => array(
				'type'        => 'textarea',
				'section'     => 'about',
				'label'       => __( 'Biographical Info' ),
				'description' => __( 'Share a little biographical information to fill out your profile. This may be shown publicly.' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'description', null, $field_args );

		//////////////////////////////
		// Core: Account Management //
		//////////////////////////////

		$wp_fields->add_section( $this->object_type, 'account-management', null, array(
			'title'                 => __( 'Account Management' ),
			'screen'                => $this->id,
			'capabilities_callback' => array( $this, 'capability_show_password_fields' ),
		) );

		$field_args = array(
			'control' => array(
				'type'    => 'user-password',
				'section' => 'account-management',
				'label'   => __( 'Password' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'user_pass', null, $field_args );

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

		$wp_fields->add_field( $this->object_type, 'sessions', null, $field_args );

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

		$wp_fields->add_section( $this->object_type, 'additional-capabilities', null, array(
			'title'                 => __( 'Additional Capabilities' ),
			'screen'                => $this->id,
			'capabilities_callback' => array( $this, 'capability_show_capabilities' ),
		) );

		$field_args = array(
			'control' => array(
				'type'    => 'user-capabilities',
				'section' => 'additional-capabilities',
				'label'   => __( 'Capabilities' ),
			),
		);

		$wp_fields->add_field( $this->object_type, 'capabilities', null, $field_args );

		// Add example fields
		parent::register_fields( $wp_fields );

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
	 * Control only visible if in network admin and can manage network options, as long as super_admins is not being
	 * overridden.
	 *
	 * @param WP_Fields_API_Control $control
	 *
	 * @return bool
	 */
	public function capability_can_grant_super_admin( $control ) {

		$profileuser = get_userdata( $control->item_id );

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

		$profileuser = get_userdata( $control->item_id );

		/** This filter is documented in wp-admin/user-new.php */
		$show_password_fields = apply_filters( 'show_password_fields', true, $profileuser );

		$has_access = false;

		if ( $show_password_fields ) {
			$has_access = true;
		}

		return $has_access;

	}

	/**
	 * Section only visible if additional capabilities can be shown and total number of capabilities are greater than
	 * total number of roles.
	 *
	 * @param WP_Fields_API_Section $section
	 *
	 * @return bool
	 */
	public function capability_show_capabilities( $section ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$screen_obj = $wp_fields->get_screen( $section->object_type, $section->screen, $section->object_name );

		$profileuser = get_userdata( $screen_obj->item_id );

		$total_roles = count( $profileuser->roles );
		$total_caps  = count( $profileuser->caps );

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