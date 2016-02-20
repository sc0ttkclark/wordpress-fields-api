<?php
/**
 * Fields API Datasource Class
 *
 * @package WordPress
 * @subpackage Fields_API
 */
class WP_Fields_API_Datasource {

	/**
	 * Datasource type
	 *
	 * @access public
	 * @var string
	 */
	public $type = '';

	/**
	 * Arguments to send to the datasource or callback
	 *
	 * @var array
	 */
	public $args = array();

	/**
	 * Choices callback
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Datasource::get_data()
	 *
	 * @var callable Callback is called with two arguments including the Datasource $args
	 *               and the instance of WP_Fields_API_Datasource. It returns an array of key=>value data to use.
	 */
	public $data_callback = null;

	/**
	 * Setup datasource properties
	 *
	 * @param string $type Datasource type.
	 * @param array  $args Datasource arguments.
	 */
	public function __construct( $type = null, $args = array() ) {

		// If source has a type, don't override it
		if ( $type && ! $this->type ) {
			$this->type = $type;
		}

		if ( $args ) {
			// If source has default args, merge them
			if ( $this->args ) {
				$args = array_merge( $this->args, $args );
			}

			$this->args = $args;
		}

	}

	/**
	 * Setup and return data from the datasource
	 *
	 * @param array $args (optional) Override datasource args values on-the-fly
	 *
	 * @return array|WP_Error An array of data, or a WP_Error if there was a problem
	 */
	public function get_data( $args = array() ) {

		// Allow overriding of $this->args values on-the-fly
		$args = array_merge( $this->args, $args );

		// Handle callback
		if ( $this->data_callback && is_callable( $this->data_callback ) ) {
			$data = call_user_func( $this->data_callback, $args, $this );
		} else {
			$data = $this->setup_data( $args );
		}

		// @todo Needs hook doc
		$data = apply_filters( 'fields_api_datasource_data', $data, $this->type, $args, $this );

		// @todo Needs hook doc
		$data = apply_filters( "fields_api_datasource_data_{$this->type}", $data, $this->type, $args, $this );

		return $data;

	}

	/**
	 * Get data from the datasource
	 *
	 * @param array $args Datasource args
	 *
	 * @return array|WP_Error An array of data, or a WP_Error if there was a problem
	 */
	public function setup_data( $args ) {

		$data = array();

		// Handle built-in types
		switch( $this->type ) {

			case 'post-format':
				$data = get_post_format_strings();
				break;

			case 'post-type':
				$data = get_post_types();
				break;

			case 'post-status':
				$data = get_post_statuses();
				break;

			case 'page-status':
				$data = get_page_statuses();
				break;

		}

		return $data;

	}

}