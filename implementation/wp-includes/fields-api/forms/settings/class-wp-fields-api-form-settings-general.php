<?php
/**
 * This is an implementation for Fields API for the General Settings form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Settings_General
 */
class WP_Fields_API_Form_Settings_General extends WP_Fields_API_Form_Settings {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		////////////////////////////
		// Core: General Settings //
		////////////////////////////

		$section_id   = $this->id . '-options-general';
		$section_args = array(
			'label'         => __( 'General Settings' ),
			'display_label' => false,
			'controls'      => array(),
		);

		// Control: Site Title
		$section_args['controls']['blogname'] = array(
			'type'        => 'text',
			'label'       => __( 'Site Title' ),
			'input_attrs' => array(
				'class' => 'regular-text',
				'id'    => 'blogname',
				'name'  => 'blogname',
			),
			'internal'    => true,
		);

		// Control: Tagline
		$section_args['controls']['blogdescription'] = array(
			'type'        => 'text',
			'label'       => __( 'Tagline' ),
			'description' => __( 'In a few words, explain what this site is about.' ),
			'input_attrs' => array(
				'class' => 'regular-text',
				'id'    => 'blogdescription',
				'name'  => 'blogdescription',
			),
			'internal'    => true,
		);

		// Control: WordPress Address (URL)
		$section_args['controls']['siteurl'] = array(
			'type'                  => 'text',
			'label'                 => __( 'WordPress Address (URL)' ),
			'input_attrs'           => array(
				'class' => 'regular-text code',
				'id'    => 'siteurl',
				'name'  => 'siteurl',
			),
			'capabilities_callback' => array( $this, 'capability_is_not_multisite' ),
			'internal'              => true,
		);

		// Control: Site Address (URL)
		$section_args['controls']['home'] = array(
			'type'                  => 'text',
			'label'                 => __( 'Site Address (URL)' ),
			'input_attrs'           => array(
				'class' => 'regular-text code',
				'id'    => 'home',
				'name'  => 'home',
			),
			'description'           => sprintf( __( 'Enter the address here if you <a href="%s">want your site home page to be different from your WordPress installation directory</a>.' ), 'https://codex.wordpress.org/Giving_WordPress_Its_Own_Directory' ),
			'capabilities_callback' => array( $this, 'capability_is_not_multisite' ),
			'internal'              => true,
		);

		// Control: Email Address
		$section_args['controls']['admin_email'] = array(
			'type'                  => 'text',
			'label'                 => __( 'Email Address' ),
			'input_attrs'           => array(
				'class' => 'regular-text ltr',
				'id'    => 'admin_email',
				'name'  => 'admin_email',
			),
			'description'           => __( 'This address is used for admin purposes, like new user notification.' ),
			'capabilities_callback' => array( $this, 'capability_is_not_multisite' ),
			'internal'              => true,
		);

		// Control: Email Address
		$section_args['controls']['admin_email'] = array(
			'type'                  => 'email',
			'label'                 => __( 'Email Address' ),
			'input_attrs'           => array(
				'class' => 'regular-text ltr',
				'id'    => 'admin_email',
				'name'  => 'admin_email',
			),
			'description'           => __( 'This address is used for admin purposes, like new user notification.' ),
			'capabilities_callback' => array( $this, 'capability_is_not_multisite' ),
			'internal'              => true,
		);

		// Control: (New) Email Address (multisite-only)
		$section_args['controls']['new_admin_email'] = array(
			'type'                  => 'email',
			'label'                 => __( 'Email Address' ),
			'input_attrs'           => array(
				'class' => 'regular-text ltr',
				'id'    => 'admin_email',
				'name'  => 'admin_email',
			),
			'description'           => __( 'This address is used for admin purposes. If you change this we will send you an email at your new address to confirm it. <strong>The new address will not become active until confirmed.</strong>' ),
			'capabilities_callback' => array( $this, 'capability_is_multisite' ),
			'internal'              => true,
			// @todo Custom render
		);

		// Control: Membership
		$section_args['controls']['users_can_register'] = array(
			'type'                  => 'checkbox',
			'label'                 => __( 'Membership' ),
			'input_attrs'           => array(
				'id'   => 'users_can_register',
				'name' => 'users_can_register',
			),
			'checkbox_label'        => __( 'Anyone can register' ),
			'capabilities_callback' => array( $this, 'capability_is_not_multisite' ),
			'internal'              => true,
		);

		// Control: New User Default Role
		$section_args['controls']['default_role'] = array(
			'type'                  => 'user-role',
			'label'                 => __( 'New User Default Role' ),
			'input_attrs'           => array(
				'id'   => 'default_role',
				'name' => 'default_role',
			),
			'capabilities_callback' => array( $this, 'capability_is_not_multisite' ),
			'internal'              => true,
		);

		// Control: Timezone
		// @todo: Custom control for Timezones

		$current_time = time();

		// Control: Date Format
		$section_args['controls']['date_format'] = array(
			'type'        => 'radio',
			'label'       => __( 'Date Format' ),
			'input_attrs' => array(
				'id'   => 'date_format',
				'name' => 'date_format',
			),
			'internal'    => true,
			'choices'     => array(
				date_i18n( 'F j, Y', $current_time ),
				date_i18n( 'Y-m-d', $current_time ),
				date_i18n( 'm/d/y', $current_time ),
				date_i18n( 'd/m/y', $current_time ),
			),
			// @todo Custom control type because of nested fields
		);

		// Control: Time Format
		$section_args['controls']['time_format'] = array(
			'type'        => 'radio',
			'label'       => __( 'Time Format' ),
			'description' => sprintf( '<a href="%s">' . __( 'Documentation on date and time formatting.' ) . '</a>', 'https://codex.wordpress.org/Formatting_Date_and_Time' ),
			'input_attrs' => array(
				'id'   => 'time_format',
				'name' => 'time_format',
			),
			'internal'    => true,
			'choices'     => array(
				date( 'g:i a', $current_time ),
				date( 'g:i A', $current_time ),
				date( 'H:i', $current_time ),
			),
			// @todo Custom control type because of nested fields
		);

		// Control: Week Starts On
		$section_args['controls']['start_of_week'] = array(
			'type'        => 'select',
			'label'       => __( 'Week Starts On' ),
			'internal'    => true,
			'input_attrs' => array(
				'id'   => 'start_of_week',
				'name' => 'start_of_week',
			),
			'choices'     => array(),
		);

		/**
		 * @global WP_Locale $wp_locale
		 */
		global $wp_locale;

		// @todo Move into choices callback
		for ( $day_index = 0; $day_index <= 6; $day_index++ ) {
			$section_args['controls']['start_of_week']['choices'][ (string) $day_index ] = $wp_locale->get_weekday( $day_index );
		}

		// @todo Implement languages dropdown control

		$this->add_section( $section_id, $section_args );

		// Add example fields (maybe)
		parent::register_fields( $wp_fields );

	}

	/**
	 * Control only visible if WordPress is not multisite.
	 *
	 * @param WP_Fields_API_Control $control Control object
	 *
	 * @return bool
	 */
	public function capability_is_not_multisite( $control ) {

		return ! is_multisite();

	}

	/**
	 * Control only visible if WordPress is multisite.
	 *
	 * @param WP_Fields_API_Control $control Control object
	 *
	 * @return bool
	 */
	public function capability_is_multisite( $control ) {

		return is_multisite();

	}

}