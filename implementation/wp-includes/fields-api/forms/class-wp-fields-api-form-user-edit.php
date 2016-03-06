<?php
/**
 * This is an implementation for Fields API for the User Edit Profile form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_User_Edit
 */
class WP_Fields_API_Form_User_Edit extends WP_Fields_API_Form {

	/**
	 * {@inheritdoc}
	 */
	public function register_control_types( $wp_fields ) {

		$control_type_dir = WP_FIELDS_API_DIR . 'implementation/wp-includes/fields-api/control-types/user/';

		// Include control types
		require_once $control_type_dir . 'class-wp-fields-api-user-super-admin-control.php';
		require_once $control_type_dir . 'class-wp-fields-api-user-display-name-control.php';
		require_once $control_type_dir . 'class-wp-fields-api-user-email-control.php';
		require_once $control_type_dir . 'class-wp-fields-api-user-password-control.php';
		require_once $control_type_dir . 'class-wp-fields-api-user-sessions-control.php';
		require_once $control_type_dir . 'class-wp-fields-api-user-capabilities-control.php';

		// Register control types
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

		////////////////////////////
		// Core: Personal Options //
		////////////////////////////

		$section_id   = $this->id . '-personal-options';
		$section_args = array(
			'label'    => __( 'Personal Options' ),
			'form'     => $this->id,
			'controls' => array(),
		);

		// Control: Visual Editor
		$section_args['controls']['rich_editing'] = array(
			'type'                  => 'checkbox',
			'label'                 => __( 'Visual Editor' ),
			'description'           => __( 'Disable the visual editor when writing' ),
			'capabilities_callback' => array( $this, 'capability_is_subscriber_editing_profile' ),
			'checkbox_value'        => 'false',
			'field'                 => array(
				'sanitize_callback' => array( $this, 'sanitize_rich_editing' ),
			),
			'internal'              => true,
		);

		// Control: Admin Color Scheme
		$section_args['controls']['admin_color'] = array(
			'type'                  => 'radio',
			'datasource'            => 'admin-color-scheme',
			'label'                 => __( 'Admin Color Scheme' ),
			'description'           => __( 'Disable the visual editor when writing' ),
			'capabilities_callback' => array( $this, 'capability_has_color_scheme_control' ),
			'internal'              => true,
		);

		// Control: Keyboard Shortcuts
		$section_args['controls']['comment_shortcuts'] = array(
			'type'                  => 'checkbox',
			'label'                 => __( 'Keyboard Shortcuts' ),
			'description'           => __( 'Enable keyboard shortcuts for comment moderation.' ) . ' ' . __( '<a href="https://codex.wordpress.org/Keyboard_Shortcuts" target="_blank">More information</a>' ),
			'capabilities_callback' => array( $this, 'capability_is_subscriber_editing_profile' ),
			'checkbox_value'        => 'true',
			'field'                 => array(
				'sanitize_callback' => array( $this, 'sanitize_comment_shortcuts' ),
			),
			'internal'              => true,
		);

		// Control: Toolbar
		$section_args['controls']['admin_bar_front'] = array(
			'type'           => 'checkbox',
			'label'          => __( 'Toolbar' ),
			'description'    => __( 'Show Toolbar when viewing site' ),
			'checkbox_value' => 'true',
			'field'          => array(
				'sanitize_callback' => array( $this, 'sanitize_admin_bar_front' ),
			),
			'internal'       => true,
		);

		$this->add_section( $section_id, $section_args );

		// Back-compat
		add_action( "fields_after_render_section_controls_term_{$section_id}", array( $this, '_compat_section_controls_personal_options_hooks' ) );
		add_action( "fields_after_render_section_term_{$section_id}", array( $this, '_compat_section_personal_options_hooks' ) );

		////////////////
		// Core: Name //
		////////////////

		$section_id   = $this->id . '-name';
		$section_args = array(
			'label'    => __( 'Name' ),
			'form'     => $this->id,
			'controls' => array(),
		);

		// Control: Username
		$section_args['controls']['user_login'] = array(
			'type'        => 'text',
			'label'       => __( 'Username' ),
			'description' => __( 'Usernames cannot be changed.' ),
			'input_attrs' => array(
				'disabled' => 'disabled',
			),
			'internal'    => true,
		);

		// Control: Role
		$section_args['controls']['role'] = array(
			'type'                  => 'select',
			'label'                 => __( 'Role' ),
			'datasource'            => 'user-role',
			'placeholder_text'      => __( '&mdash; No role for this site &mdash;' ),
			'capabilities_callback' => array( $this, 'capability_show_roles' ),
			'internal'              => true,
		);

		// Control: Super Admin
		$section_args['controls']['super_admin'] = array(
			'type'                  => 'user-super-admin',
			'label'                 => __( 'Super Admin' ),
			'description'           => __( 'Grant this user super admin privileges for the Network.' ),
			'capabilities_callback' => array( $this, 'capability_can_grant_super_admin' ),
			'field'                 => array(
				'value_callback' => array( $this, 'value_is_super_admin' ),
				//'update_value_callback' => array( $this, 'update_value_is_super_admin' ), // Handled by admin page
			),
			'internal'              => true,
		);

		// Control: First Name
		$section_args['controls']['first_name'] = array(
			'type'     => 'text',
			'label'    => __( 'First Name' ),
			'internal' => true,
		);

		// Control: Last Name
		$section_args['controls']['last_name'] = array(
			'type'     => 'text',
			'label'    => __( 'Last Name' ),
			'internal' => true,
		);

		// Control: Nickname
		$section_args['controls']['user_nickname'] = array(
			'type'        => 'text',
			'label'       => __( 'Nickname' ),
			'description' => __( '(required)' ),
			'id'          => 'nickname', // Input ID override for back-compat
			'internal'    => true,
		);

		// Control: Display name
		$section_args['controls']['display_name'] = array(
			'type'     => 'user-display-name',
			'label'    => __( 'Display name publicly as' ),
			'internal' => true,
		);

		$this->add_section( $section_id, $section_args );

		////////////////////////
		// Core: Contact Info //
		////////////////////////

		$section_id   = $this->id . '-contact-info';
		$section_args = array(
			'label'    => __( 'Contact Info' ),
			'form'     => $this->id,
			'controls' => array(),
		);

		// Control: E-mail
		$section_args['controls']['user_email'] = array(
			'type'        => 'user-email',
			'label'       => __( 'E-mail' ),
			'description' => __( '(required)' ),
			'id'          => 'email', // Input ID override for back-compat
			'internal'    => true,
		);

		// Control: Website
		$section_args['controls']['user_url'] = array(
			'type'     => 'text',
			'label'    => __( 'Website' ),
			'internal' => true,
		);

		// Controls: Contact Info for back-compat
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

			$section_args['controls'][ $method ] = array(
				'type'     => 'text',
				'label'    => $label,
				'internal' => true,
			);
		}

		$this->add_section( $section_id, $section_args );

		/////////////////
		// Core: About //
		/////////////////

		$section_id   = $this->id . '-about';
		$section_args = array(
			'label'    => __( 'About the user' ),
			'form'     => $this->id,
			'controls' => array(),
		);

		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			$section_args['label'] = __( 'About Yourself' );
		}

		// Control: Biographical Info
		$section_args['controls']['description'] = array(
			'type'        => 'textarea',
			'label'       => __( 'Biographical Info' ),
			'description' => __( 'Share a little biographical information to fill out your profile. This may be shown publicly.' ),
			'internal'    => true,
		);

		$this->add_section( $section_id, $section_args );

		//////////////////////////////
		// Core: Account Management //
		//////////////////////////////

		$section_id   = $this->id . '-account-management';
		$section_args = array(
			'label'                 => __( 'Account Management' ),
			'form'                  => $this->id,
			'capabilities_callback' => array( $this, 'capability_show_password_fields' ),
			'controls'              => array(),
		);

		// Control: Password
		$section_args['controls']['user_pass'] = array(
			'type'     => 'user-password',
			'label'    => __( 'Password' ),
			'internal' => true,
		);

		// Control: Sessions
		$section_args['controls']['user_pass'] = array(
			'type'     => 'user-sessions',
			'label'    => __( 'Sessions' ),
			'internal' => true,
		);

		// If password fields not shown, show Sessions under About
		// @todo Change which section this control is in if password fields not shown
		/*if ( ! $show_password_fields ) { }*/

		// Back-compat
		add_action( "fields_after_render_section_term_{$section_id}", array( $this, '_compat_section_account_management_hooks' ) );

		$this->add_section( $section_id, $section_args );

		///////////////////////////////////
		// Core: Additional Capabilities //
		///////////////////////////////////

		$section_id   = $this->id . '-additional-capabilities';
		$section_args = array(
			'label'                 => __( 'Additional Capabilities' ),
			'form'                  => $this->id,
			'capabilities_callback' => array( $this, 'capability_show_capabilities' ),
			'controls'              => array(),
		);

		// Control: Capabilities
		$section_args['controls']['capabilities'] = array(
			'type'     => 'user-capabilities',
			'section'  => 'additional-capabilities',
			'label'    => __( 'Capabilities' ),
			'internal' => true,
		);

		$this->add_section( $section_id, $section_args );

		// Add example fields (maybe)
		parent::register_fields( $wp_fields );

	}

	/**
	 * {@inheritdoc}
	 */
	public function save_fields( $item_id = null, $object_name = null ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			/**
			 * Fires before the page loads on the 'Your Profile' editing form.
			 *
			 * The action only fires if the current user is editing their own profile.
			 *
			 * @since 2.0.0
			 *
			 * @param int $user_id The user ID.
			 */
			do_action( 'personal_options_update', $item_id );
		} else {
			/**
			 * Fires before the page loads on the 'Edit User' form.
			 *
			 * @since 2.7.0
			 *
			 * @param int $user_id The user ID.
			 */
			do_action( 'edit_user_profile_update', $item_id );
		}

		// Update the email address in signups, if present.
		if ( is_multisite() ) {
			$user = get_userdata( $item_id );

			if ( $user && $user->user_login && isset( $_POST['email'] ) && is_email( $_POST['email'] ) ) {
				$signup_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT signup_id FROM {$wpdb->signups} WHERE user_login = %s", $user->user_login ) );

				if ( 0 < $signup_id ) {
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->signups} SET user_email = %s WHERE signup_id = %d", $_POST['email'], $signup_id ) );
				}
			}
		}

		// Update the user.
		$errors = edit_user( $item_id );

		global $super_admins;

		// Grant or revoke super admin status if requested.
		if ( ! defined( 'IS_PROFILE_PAGE' ) || ! IS_PROFILE_PAGE ) {
			if ( is_multisite() && is_network_admin() && current_user_can( 'manage_network_options' ) && ! isset( $super_admins ) && empty( $_POST['super_admin'] ) == is_super_admin( $item_id ) ) {
				if ( empty( $_POST['super_admin'] ) ) {
					revoke_super_admin( $item_id );
				} else {
					grant_super_admin( $item_id );
				}
			}
		}

		// Return if not successful
		if ( is_wp_error( $errors ) ) {
			return $errors;
		}

		// Save additional fields
		return parent::save_fields( $item_id, $object_name );

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

		$profileuser = $this->get_item();

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

		$profileuser = get_userdata( $this->get_item_id() );

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

		$profileuser = get_userdata( $this->get_item_id() );

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
	 *
	 * @return string|WP_Error Sanitized value or error
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
	 *
	 * @return string|WP_Error Sanitized value or error
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
	 *
	 * @return string|WP_Error Sanitized value or error
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

	/**
	 * Personal Options compatibility hooks needed for just after <table> markup of section.
	 *
	 * @param WP_Fields_API_Section $section
	 */
	public function _compat_section_personal_options_hooks( $section ) {

		$profileuser = get_userdata( $this->get_item_id() );

		/**
		 * Fires at the end of the 'Personal Options' settings table on the user editing screen.
		 *
		 * @since 2.7.0
		 *
		 * @param WP_User $profileuser The current WP_User object.
		 */
		do_action( 'personal_options', $profileuser );

	}

	/**
	 * Personal Options compatibility hooks needed for within <table> markup of section.
	 *
	 * @param WP_Fields_API_Section $section
	 */
	public function _compat_section_controls_personal_options_hooks( $section ) {

		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			$profileuser = get_userdata( $this->get_item_id() );

			/**
			 * Fires after the 'Personal Options' settings table on the 'Your Profile' editing screen.
			 *
			 * The action only fires if the current user is editing their own profile.
			 *
			 * @since 2.0.0
			 *
			 * @param WP_User $profileuser The current WP_User object.
			 */
			do_action( 'profile_personal_options', $profileuser );
		}

	}

	/**
	 * Account Management compatibility hooks needed for just after <table> markup of section.
	 *
	 * @param WP_Fields_API_Section $section
	 */
	public function _compat_section_account_management_hooks( $section ) {

		$profileuser = get_userdata( $this->get_item_id() );

		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
			/**
			 * Fires after the 'About Yourself' settings table on the 'Your Profile' editing form.
			 *
			 * The action only fires if the current user is editing their own profile.
			 *
			 * @since 2.0.0
			 *
			 * @param WP_User $profileuser The current WP_User object.
			 */
			do_action( 'show_user_profile', $profileuser );
		} else {
			/**
			 * Fires after the 'About the User' settings table on the 'Edit User' form.
			 *
			 * @since 2.0.0
			 *
			 * @param WP_User $profileuser The current WP_User object.
			 */
			do_action( 'edit_user_profile', $profileuser );
		}

	}

}