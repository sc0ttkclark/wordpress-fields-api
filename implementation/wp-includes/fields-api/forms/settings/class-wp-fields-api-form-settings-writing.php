<?php
/**
 * This is an implementation for Fields API for the General Writing form in the WordPress Dashboard
 *
 * @package    WordPress
 * @subpackage Fields_API
 */

/**
 * Class WP_Fields_API_Form_Settings_Writing
 */
class WP_Fields_API_Form_Settings_Writing extends WP_Fields_API_Form_Settings {

	/**
	 * {@inheritdoc}
	 */
	public function register_fields( $wp_fields ) {

		// Sections
		$wp_fields->add_section( $this->object_type, $this->id . '-options-writing', null, array(
			'label'         => __( 'Writing Settings' ),
			'form'          => $this->id,
			'display_label' => false,
		) );

		$wp_fields->add_section( $this->object_type, $this->id . '-options-writing-post-by-email', null, array(
			'label'         => __( 'Post by Email' ),
			'form'          => $this->id,
			'description'   => sprintf( __( 'To post to WordPress by email you must set up a secret email account with POP3 access. Any mail received at this address will be posted, so it’s a good idea to keep this address very secret. Here are three random strings you could use: <code>%1$s</code>, <code>%2$s</code>, <code>%3$s</code>.' ), wp_generate_password( 8, false ), wp_generate_password( 8, false ), wp_generate_password( 8, false ) ),
			'display_label' => true,
		) );

		$wp_fields->add_section( $this->object_type, $this->id . '-options-writing-update-services', null, array(
			'label'         => __( 'Update Services' ),
			'form'          => $this->id,
			'description'   => sprintf( __( 'When you publish a new post, WordPress automatically notifies the following site update services. For more about this, see <a href="%s">Update Services</a> on the Codex. Separate multiple service URLs with line breaks.' ), esc_url( 'https://codex.wordpress.org/Update_Services' ) ),
			'display_label' => true,
		) );

		// Controls
		/**
		 * Default Post Category
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'dropdown-terms',
				'taxonomy'    => 'category',
				'section'     => $this->id . '-options-writing',
				'label'       => __( 'Default Post Category' ),
				'input_attrs' => array(
					'class' => 'postform',
					'id'    => 'default_category',
					'name'  => 'default_category',
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'default_category', null, $field_args );

		/**
		 * Default Post Format
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'dropdown-post-format',
				'section'     => $this->id . '-options-writing',
				'label'       => __( 'Default Post Format' ),
				'input_attrs' => array(
					'id'   => 'default_post_format',
					'name' => 'default_post_format',
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'default_post_format', null, $field_args );

		/**
		 * Mail Server
		 * @todo we need a new control type for nested fields. In this case, the mail server has a separate field for Port
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'text',
				'section'     => $this->id . '-options-writing-post-by-email',
				'label'       => __( 'Mail Server' ),
				'input_attrs' => array(
					'id'    => 'mailserver_url',
					'name'  => 'mailserver_url',
					'class' => 'regular-text code'
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'mailserver_url', null, $field_args );

		/**
		 * Mail Server Login Name
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'text',
				'section'     => $this->id . '-options-writing-post-by-email',
				'label'       => __( 'Login Name' ),
				'input_attrs' => array(
					'id'    => 'mailserver_login',
					'name'  => 'mailserver_login',
					'class' => 'regular-text ltr'
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'mailserver_login', null, $field_args );

		/**
		 * Mail Server Password
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'text',
				'section'     => $this->id . '-options-writing-post-by-email',
				'label'       => __( 'Password' ),
				'input_attrs' => array(
					'id'    => 'mailserver_pass',
					'name'  => 'mailserver_pass',
					'class' => 'regular-text ltr'
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'mailserver_pass', null, $field_args );

		/**
		 * Default Mail Category
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'dropdown-terms',
				'taxonomy'    => 'category',
				'section'     => $this->id . '-options-writing-post-by-email',
				'label'       => __( 'Default Mail Category' ),
				'input_attrs' => array(
					'class' => 'postform',
					'id'    => 'default_email_category',
					'name'  => 'default_email_category',
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'default_email_category', null, $field_args );

		/**
		 * Update Services
		 */
		$field_args = array(
			'control' => array(
				'type'        => 'textarea',
				'section'     => $this->id . '-options-writing-update-services',
				'label'       => __( 'Update Services' ),
				'input_attrs' => array(
					'id'    => 'ping_sites',
					'name'  => 'ping_sites',
					'class' => 'large-text code',
					'rows'  => 3
				),
				'internal'    => true,
			),
		);
		$wp_fields->add_field( $this->object_type, 'ping_sites', null, $field_args );
	}

}