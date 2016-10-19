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
	public function setup() {

		////////////////////////////
		// Core: Writing Settings //
		////////////////////////////

		$section_id   = $this->id . '-options-writing';
		$section_args = array(
			'label'         => __( 'Writing Settings' ),
			'display_label' => false,
			'controls'      => array(),
		);

		// Control: Default Post Category
		$section_args['controls']['default_category'] = array(
			'type'             => 'select',
			'label'            => __( 'Default Post Category' ),
			'datasource'       => array(
				'type'     => 'term',
				'get_args' => array(
					'taxonomy' => 'category',
				),
			),
			'input_attrs'      => array(
				'class' => 'postform',
				'id'    => 'default_category',
				'name'  => 'default_category',
			),
			'placeholder_text' => null, // Don't show placeholder option
			'internal'         => true,
		);

		// Control: Default Post Format
		$section_args['controls']['default_post_format'] = array(
			'type'             => 'select',
			'label'            => __( 'Default Post Format' ),
			'datasource'       => 'post-format',
			// @todo Post format needs to be filtered to set 'standard' key as '0' key, see below
			'input_attrs'      => array(
				'id'   => 'default_post_format',
				'name' => 'default_post_format',
			),
			'placeholder_text' => null, // Don't show placeholder option
			'internal'         => true,
		);

		/**
		 * // @todo Post format filter to set 'standard' => '0' key
		 * // Make 'standard' be '0' and add to the front
		 * $choices = array_reverse( $choices, true );
		 * $choices['0'] = $choices['standard'];
		 * $choices = array_reverse( $choices, true );
		 *
		 * unset( $choices['standard'] );
		 */

		$this->add_child( $section_id, $section_args );

		/////////////////////////
		// Core: Post by Email //
		/////////////////////////

		$section_id   = $this->id . '-options-writing-post-by-email';
		$section_args = array(
			'label'         => __( 'Post by Email' ),
			'description'   => sprintf( __( 'To post to WordPress by email you must set up a secret email account with POP3 access. Any mail received at this address will be posted, so it’s a good idea to keep this address very secret. Here are three random strings you could use: <code>%1$s</code>, <code>%2$s</code>, <code>%3$s</code>.' ), wp_generate_password( 8, false ), wp_generate_password( 8, false ), wp_generate_password( 8, false ) ),
			'controls'      => array(),
		);

		// Control: Mail Server
		$section_args['controls']['mailserver_url'] = array(
			'type'        => 'text',
			'label'       => __( 'Mail Server' ),
			'input_attrs' => array(
				'id'    => 'mailserver_url',
				'name'  => 'mailserver_url',
				'class' => 'regular-text code'
			),
			'internal'    => true,
			// @todo Custom control type for nested fields, the mail server has a separate field for Port
		);

		// Control: Login Name
		$section_args['controls']['mailserver_login'] = array(
			'type'        => 'text',
			'label'       => __( 'Login Name' ),
			'input_attrs' => array(
				'id'    => 'mailserver_login',
				'name'  => 'mailserver_login',
				'class' => 'regular-text ltr'
			),
			'internal'    => true,
		);

		// Control: Password
		$section_args['controls']['mailserver_pass'] = array(
			'type'        => 'text',
			'label'       => __( 'Password' ),
			'input_attrs' => array(
				'id'    => 'mailserver_pass',
				'name'  => 'mailserver_pass',
				'class' => 'regular-text ltr'
			),
			'internal'    => true,
		);

		// Control: Default Mail Category
		$section_args['controls']['default_email_category'] = array(
			'type'             => 'select',
			'label'            => __( 'Default Mail Category' ),
			'datasource'       => array(
				'type'     => 'term',
				'get_args' => array(
					'taxonomy' => 'category',
				),
			),
			'input_attrs'      => array(
				'class' => 'postform',
				'id'    => 'default_email_category',
				'name'  => 'default_email_category',
			),
			'placeholder_text' => null, // Don't show placeholder option
			'internal'         => true,
		);

		$this->add_child( $section_id, $section_args );

		///////////////////////////
		// Core: Update Services //
		///////////////////////////

		$section_id   = $this->id . '-options-writing-update-services';
		$section_args = array(
			'label'         => __( 'Update Services' ),
			'description'   => sprintf( __( 'When you publish a new post, WordPress automatically notifies the following site update services. For more about this, see <a href="%s">Update Services</a> on the Codex. Separate multiple service URLs with line breaks.' ), esc_url( 'https://codex.wordpress.org/Update_Services' ) ),
			'controls'      => array(),
		);

		// Control: Update Services
		$section_args['controls']['ping_sites'] = array(
			'type'          => 'textarea',
			'label'         => __( 'Update Services' ),
			'display_label' => false,
			'input_attrs'   => array(
				'id'    => 'ping_sites',
				'name'  => 'ping_sites',
				'class' => 'large-text code',
				'rows'  => 3
			),
			'internal'      => true,
		);

		$this->add_child( $section_id, $section_args );

	}

}