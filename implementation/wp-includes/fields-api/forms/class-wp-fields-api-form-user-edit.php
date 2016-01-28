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

		$this->register_control_types( $wp_fields );

		////////////////////////////
		// Core: Personal Options //
		////////////////////////////

		$wp_fields->add_section( $this->object_type, $this->id . '-personal-options', null, array(
			'title'  => __( 'Personal Options' ),
			'form' => $this->id,
			// @todo Needs action compatibility for personal_options( $profileuser )
			// @todo Needs action compatibility for profile_personal_options( $profileuser ) if IS_PROFILE_PAGE
		) );

		$field_args = array(
			'sanitize_callback' => array( $this, 'sanitize_rich_editing' ),
			'control'           => array(
				'type'                  => 'checkbox',
				'section'               => $this->id . '-personal-options',
				'label'                 => __( 'Visual Editor' ),
				'description'           => __( 'Disable the visual editor when writing' ),
				'capabilities_callback' => array( $this, 'capability_is_subscriber_editing_profile' ),
				'checkbox_value'        => 'false',
				'internal'              => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'rich_editing', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'                  => 'user-color-scheme',
				'section'               => $this->id . '-personal-options',
				'label'                 => __( 'Admin Color Scheme' ),
				'description'           => __( 'Disable the visual editor when writing' ),
				'capabilities_callback' => array( $this, 'capability_has_color_scheme_control' ),
				'internal'              => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'admin_color', null, $field_args );

		$field_args = array(
			'sanitize_callback' => array( $this, 'sanitize_comment_shortcuts' ),
			'control'           => array(
				'type'                  => 'checkbox',
				'section'               => $this->id . '-personal-options',
				'label'                 => __( 'Keyboard Shortcuts' ),
				'description'           => __( 'Enable keyboard shortcuts for comment moderation.' ) . ' ' . __( '<a href="https://codex.wordpress.org/Keyboard_Shortcuts" target="_blank">More information</a>' ),
				'capabilities_callback' => array( $this, 'capability_is_subscriber_editing_profile' ),
				'checkbox_value'        => 'true',
				'internal'              => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'comment_shortcuts', null, $field_args );

		$field_args = array(
			'sanitize_callback' => array( $this, 'sanitize_admin_bar_front' ),
			'control'           => array(
				'type'           => 'checkbox',
				'section'        => $this->id . '-personal-options',
				'label'          => __( 'Toolbar' ),
				'description'    => __( 'Show Toolbar when viewing site' ),
				'checkbox_value' => 'true',
				'internal'       => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'admin_bar_front', null, $field_args );

		////////////////
		// Core: Name //
		////////////////

		$wp_fields->add_section( $this->object_type, $this->id . '-name', null, array(
			'title'  => __( 'Name' ),
			'form' => $this->id,
		) );

		$field_args = array(
			'control' => array(
				'type'        => 'text',
				'section'     => $this->id . '-name',
				'label'       => __( 'Username' ),
				'description' => __( 'Usernames cannot be changed.' ),
				'input_attrs' => array(
					'disabled' => 'disabled',
				),
				'internal'    => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'user_login', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'                  => 'user-role',
				'section'               => $this->id . '-name',
				'label'                 => __( 'Role' ),
				'capabilities_callback' => array( $this, 'capability_show_roles' ),
				'internal'              => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'role', null, $field_args );

		$field_args = array(
			'value_callback' => array( $this, 'value_is_super_admin' ),
			//'update_value_callback' => array( $this, 'update_value_is_super_admin' ),
			'control'        => array(
				'type'                  => 'user-super-admin',
				'section'               => $this->id . '-name',
				'label'                 => __( 'Super Admin' ),
				'description'           => __( 'Grant this user super admin privileges for the Network.' ),
				'capabilities_callback' => array( $this, 'capability_can_grant_super_admin' ),
				'internal'              => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'super_admin', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'     => 'text',
				'section'  => $this->id . '-name',
				'label'    => __( 'First Name' ),
				'internal' => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'first_name', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'     => 'text',
				'section'  => $this->id . '-name',
				'label'    => __( 'Last Name' ),
				'internal' => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'last_name', null, $field_args );

		$field_args = array(
			'control' => array(
				'id'          => 'nickname',
				'type'        => 'text',
				'section'     => $this->id . '-name',
				'label'       => __( 'Nickname' ),
				'description' => __( '(required)' ),
				'internal'    => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'user_nickname', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'     => 'user-display-name',
				'section'  => $this->id . '-name',
				'label'    => __( 'Display name publicly as' ),
				'internal' => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'display_name', null, $field_args );

		////////////////////////
		// Core: Contact Info //
		////////////////////////

		$wp_fields->add_section( $this->object_type, $this->id . '-contact-info', null, array(
			'title'  => __( 'Contact Info' ),
			'form' => $this->id,
		) );

		$field_args = array(
			'control' => array(
				'id'          => 'email',
				'type'        => 'user-email',
				'section'     => $this->id . '-contact-info',
				'label'       => __( 'E-mail' ),
				'description' => __( '(required)' ),
				'internal'    => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'user_email', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'     => 'text',
				'section'  => $this->id . '-contact-info',
				'label'    => __( 'Website' ),
				'internal' => true,
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
					'type'     => 'text',
					'section'  => $this->id . '-contact-info',
					'label'    => $label,
					'internal' => true,
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

		$wp_fields->add_section( $this->object_type, $this->id . '-about', null, array(
			'title'  => $about_title,
			'form' => $this->id,
		) );

		$field_args = array(
			'control' => array(
				'type'        => 'textarea',
				'section'     => $this->id . '-about',
				'label'       => __( 'Biographical Info' ),
				'description' => __( 'Share a little biographical information to fill out your profile. This may be shown publicly.' ),
				'internal'    => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'description', null, $field_args );

		//////////////////////////////
		// Core: Account Management //
		//////////////////////////////

		$wp_fields->add_section( $this->object_type, $this->id . '-account-management', null, array(
			'title'                 => __( 'Account Management' ),
			'form'                => $this->id,
			'capabilities_callback' => array( $this, 'capability_show_password_fields' ),
		) );

		$field_args = array(
			'control' => array(
				'type'     => 'user-password',
				'section'  => $this->id . '-account-management',
				'label'    => __( 'Password' ),
				'internal' => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'user_pass', null, $field_args );

		$field_args = array(
			'control' => array(
				'type'     => 'user-sessions',
				'section'  => $this->id . '-account-management',
				'label'    => __( 'Sessions' ),
				'internal' => true,
			),
		);

		// If password fields not shown, show Sessions under About
		// @todo Change which section this control is in if password fields not shown
		/*if ( ! $show_password_fields ) {
			$field_args['control']['section'] = $this->id . '-about';
		}*/

		$wp_fields->add_field( $this->object_type, 'sessions', null, $field_args );

		// @todo Figure out how best to run actions after section
		//if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE ) {
		/**
		 * Fires after the 'About Yourself' settings table on the 'Your Profile' editing form.
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
		 * Fires after the 'About the User' settings table on the 'Edit User' form.
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
			'form'                => $this->id,
			'capabilities_callback' => array( $this, 'capability_show_capabilities' ),
		) );

		$field_args = array(
			'control' => array(
				'type'     => 'user-capabilities',
				'section'  => 'additional-capabilities',
				'label'    => __( 'Capabilities' ),
				'internal' => true,
			),
		);

		$wp_fields->add_field( $this->object_type, 'capabilities', null, $field_args );

		// Add example fields
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

		$form_obj = $wp_fields->get_form( $section->object_type, $section->form, $section->object_name );

		$profileuser = get_userdata( $form_obj->item_id );

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