<?php
/**
 * Fields API Control Class
 *
 * @package WordPress
 * @subpackage Fields_API
 */
class WP_Fields_API_Control {

	/**
	 * Incremented with each new class instantiation, then stored in $instance_number.
	 *
	 * Used when sorting two instances whose priorities are equal.
	 *
	 * @access protected
	 * @var int
	 */
	protected static $instance_count = 0;

	/**
	 * Order in which this instance was created in relation to other instances.
	 *
	 * @access public
	 * @var int
	 */
	public $instance_number = 0;

	/**
	 * Unique identifier.
	 *
	 * @access public
	 * @var string
	 */
	public $id = '';

	/**
	 * Object type.
	 *
	 * @access public
	 * @var string
	 */
	public $object_type = '';

	/**
	 * Object name (for post types and taxonomies).
	 *
	 * @access public
	 * @var string
	 */
	public $object_name = '';

	/**
	 * All fields tied to the control.
	 *
	 * @access public
	 * @var array
	 */
	public $fields = array();

	/**
	 * The primary field for the control (if there is one).
	 *
	 * @access public
	 * @var string|WP_Fields_API_Field
	 */
	public $field = 'default';

	/**
	 * The primary screen for the control (if there is one).
	 *
	 * @access public
	 * @var string|WP_Fields_API_Section
	 */
	public $section = '';

	/**
	 * The primary screen for the control (if there is one).
	 *
	 * @access public
	 * @var string|WP_Fields_API_Screen
	 */
	public $screen = '';

	/**
	 * @access public
	 * @var int
	 */
	public $priority = 10;

	/**
	 * @access public
	 * @var string
	 */
	public $label = '';

	/**
	 * @access public
	 * @var string
	 */
	public $description = '';

	/**
	 * @todo: Remove choices
	 *
	 * @access public
	 * @var array
	 */
	public $choices = array();

	/**
	 * @access public
	 * @var array
	 */
	public $input_attrs = array();

	/**
	 * @access public
	 * @var string
	 */
	public $type = 'text';

	/**
	 * Callback.
	 *
	 * @access public
	 *
	 * @see WP_Fields_API_Control::active()
	 *
	 * @var callable Callback is called with one argument, the instance of
	 *               WP_Fields_API_Control, and returns bool to indicate whether
	 *               the control is active (such as it relates to the URL
	 *               currently being previewed).
	 */
	public $active_callback = '';

	/**
	 * Constructor.
	 *
	 * Parameters are not set to maintain PHP overloading compatibility (strict standards)
	 */
	public function __construct() {

		$args = func_get_args();

		call_user_func_array( array( $this, 'init' ), $args );

	}

	/**
	 * Secondary constructor; Any supplied $args override class property defaults.
	 *
	 * @param string $object_type   Object type.
	 * @param string $id            A specific ID of the control.
	 * @param array  $args          Control arguments.
	 */
	public function init( $object_type, $id, $args = array() ) {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		$this->object_type = $object_type;

		if ( is_array( $id ) ) {
			$args = $id;

			$id = '';
		} else {
			$this->id = $id;
		}

		$keys = array_keys( get_object_vars( $this ) );

		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}

		self::$instance_count += 1;
		$this->instance_number = self::$instance_count;

		// Process fields.
		if ( empty( $this->fields ) ) {
			$this->fields = $id;
		}

		$fields = array();

		if ( is_array( $this->fields ) ) {
			foreach ( $this->fields as $key => $field ) {
				$field_obj = $wp_fields->get_field( $this->object_type, $field, $this->object_name );

				if ( $field_obj ) {
					$fields[ $key ] = $field_obj;
				}
			}
		} else {
			$field_obj = $wp_fields->get_field( $this->object_type, $this->fields, $this->object_name );

			if ( $field_obj ) {
				$this->field       = $field_obj;
				$fields['default'] = $field_obj;
			}
		}

		$this->fields = $fields;

	}

	/**
	 * Enqueue control related scripts/styles.
	 *
	 */
	public function enqueue() {}

	/**
	 * Check whether control is active to current Fields API preview.
	 *
	 * @access public
	 *
	 * @return bool Whether the control is active to the current preview.
	 */
	final public function active() {

		$control = $this;
		$active = call_user_func( $this->active_callback, $this );

		/**
		 * Filter response of WP_Fields_API_Control::active().
		 *
		 * @param bool                  $active  Whether the Field control is active.
		 * @param WP_Fields_API_Control $control WP_Fields_API_Control instance.
		 */
		$active = apply_filters( 'fields_control_active_' . $this->object_type, $active, $control );

		return $active;

	}

	/**
	 * Default callback used when invoking WP_Fields_API_Control::active().
	 *
	 * Subclasses can override this with their specific logic, or they may
	 * provide an 'active_callback' argument to the constructor.
	 *
	 * @access public
	 *
	 * @return bool Always true.
	 */
	public function active_callback() {

		return true;

	}

	/**
	 * Fetch a field's value.
	 * Grabs the main field by default.
	 *
	 * @param string $field_key
	 * @return mixed The requested field's value, if the field exists.
	 */
	final public function value( $field_key = 'default' ) {

		if ( isset( $this->fields[ $field_key ] ) ) {
			/**
			 * @var $field WP_Fields_API_Field
			 */
			$field = $this->fields[ $field_key ];

			return $field->value();
		}

		return null;

	}

	/**
	 * Get the data to export to the client via JSON.
	 *
	 * @return array Array of parameters passed to the JavaScript.
	 */
	public function json() {

		$array = array();

		$array['fields'] = wp_list_pluck( $this->fields, 'id' );
		$array['type'] = $this->type;
		$array['priority'] = $this->priority;
		$array['active'] = $this->active();
		$array['section'] = $this->section;
		$array['content'] = $this->get_content();
		$array['label'] = $this->label;
		$array['description'] = $this->description;
		$array['instanceNumber'] = $this->instance_number;

		return $array;

	}

	/**
	 * Check if the theme supports the control and check user capabilities.
	 *
	 * @return bool False if theme doesn't support the control or user doesn't have the required permissions, otherwise true.
	 */
	final public function check_capabilities() {

		/**
		 * @var $wp_fields WP_Fields_API
		 */
		global $wp_fields;

		/**
		 * @var $field WP_Fields_API_Field
		 */
		foreach ( $this->fields as $field ) {
			if ( !$field || ! $field->check_capabilities() ) {
				return false;
			}
		}

		$section = $wp_fields->get_section( $this->object_type, $this->section, $this->object_name );

		if ( isset( $section ) && ! $section->check_capabilities() ) {
			return false;
		}

		return true;

	}

	/**
	 * Get the control's content for insertion.
	 *
	 * @return string Contents of the control.
	 */
	final public function get_content() {

		ob_start();

		$this->maybe_render();

		$template = trim( ob_get_clean() );

		return $template;

	}

	/**
	 * Check capabilities and render the control.
	 *
	 * @uses WP_Fields_API_Control::render()
	 */
	final public function maybe_render() {

		if ( ! $this->check_capabilities() ) {
			return;
		}

		/**
		 * Fires just before the current control is rendered.
		 *
		 * @param WP_Fields_API_Control $this WP_Fields_API_Control instance.
		 */
		do_action( 'fields_render_control_' . $this->object_type, $this );

		/**
		 * Fires just before a specific control is rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the control ID.
		 *
		 * @param WP_Fields_API_Control $this {@see WP_Fields_API_Control} instance.
		 */
		do_action( 'fields_render_control_' . $this->object_type . '_' . $this->object_name . '_' . $this->id, $this );

		$this->render();

	}

	/**
	 * Renders the control wrapper and calls $this->render_content() for the internals.
	 *
	 */
	protected function render() {

		$id    = 'fields-control-' . str_replace( '[', '-', str_replace( ']', '', $this->id ) );
		$class = 'fields-control fields-control-' . $this->type;

		?><li id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $class ); ?>">
			<?php $this->render_content(); ?>
		</li><?php

	}

	/**
	 * Get the data link attribute for a field.
	 *
	 *
	 * @param string $field_key
	 * @return string Data link parameter, if $field_key is a valid field, empty string otherwise.
	 */
	public function get_link( $field_key = 'default' ) {

		if ( ! isset( $this->fields[ $field_key ] ) ) {
			return '';
		}

		return 'data-fields-field-link="' . esc_attr( $this->fields[ $field_key ]->id ) . '"';

	}

	/**
	 * Render the data link attribute for the control's input element.
	 *
	 * @uses WP_Fields_API_Control::get_link()
	 *
	 * @param string $field_key
	 */
	public function link( $field_key = 'default' ) {

		echo $this->get_link( $field_key );

	}

	/**
	 * Render the custom attributes for the control's input element.
	 *
	 * @access public
	 */
	public function input_attrs() {

		foreach ( $this->input_attrs as $attr => $value ) {
			echo $attr . '="' . esc_attr( $value ) . '" ';
		}

	}

	/**
	 * Render the control's content.
	 *
	 * Allows the content to be overriden without having to rewrite the wrapper in $this->render().
	 *
	 * Supports basic input types `text`, `checkbox`, `textarea`, `radio`, `select` and `dropdown-pages`.
	 * Additional input types such as `email`, `url`, `number`, `hidden` and `date` are supported implicitly.
	 *
	 * Control content can alternately be rendered in JS. See {@see WP_Fields_API_Control::print_template()}.
	 */
	public function render_content() {

		switch ( $this->type ) {
			case 'checkbox':
				?>
				<label>
					<input type="checkbox" value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); checked( $this->value() ); ?> />
					<?php echo esc_html( $this->label ); ?>
					<?php if ( ! empty( $this->description ) ) : ?>
						<span class="description fields-control-description"><?php echo $this->description; ?></span>
					<?php endif; ?>
				</label>
				<?php
				break;
			case 'radio':
				if ( empty( $this->choices ) )
					return;

				$name = '_fields-radio-' . $this->id;

				if ( ! empty( $this->label ) ) : ?>
					<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif;
				if ( ! empty( $this->description ) ) : ?>
					<span class="description fields-control-description"><?php echo $this->description ; ?></span>
				<?php endif;

				foreach ( $this->choices as $value => $label ) :
					?>
					<label>
						<input type="radio" value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $name ); ?>" <?php $this->link(); checked( $this->value(), $value ); ?> />
						<?php echo esc_html( $label ); ?><br/>
					</label>
					<?php
				endforeach;
				break;
			case 'select':
				if ( empty( $this->choices ) )
					return;

				?>
				<label>
					<?php if ( ! empty( $this->label ) ) : ?>
						<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php endif;
					if ( ! empty( $this->description ) ) : ?>
						<span class="description fields-control-description"><?php echo $this->description; ?></span>
					<?php endif; ?>

					<select <?php $this->link(); ?>>
						<?php
						foreach ( $this->choices as $value => $label )
							echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' . $label . '</option>';
						?>
					</select>
				</label>
				<?php
				break;
			case 'textarea':
				?>
				<label>
					<?php if ( ! empty( $this->label ) ) : ?>
						<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php endif;
					if ( ! empty( $this->description ) ) : ?>
						<span class="description fields-control-description"><?php echo $this->description; ?></span>
					<?php endif; ?>
					<textarea rows="5" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
				</label>
				<?php
				break;
			case 'dropdown-pages':
				$dropdown = wp_dropdown_pages(
					array(
						'name'              => '_fields-dropdown-pages-' . $this->id,
						'echo'              => 0,
						'show_option_none'  => __( '&mdash; Select &mdash;' ),
						'option_none_value' => '0',
						'selected'          => $this->value(),
					)
				);

				// Hackily add in the data link parameter.
				$dropdown = str_replace( '<select', '<select ' . $this->get_link(), $dropdown );

				printf(
					'<label class="fields-control-select"><span class="fields-control-title">%s</span> %s</label>',
					$this->label,
					$dropdown
				);
				break;
			default:
				?>
				<label>
					<?php if ( ! empty( $this->label ) ) : ?>
						<span class="fields-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php endif;
					if ( ! empty( $this->description ) ) : ?>
						<span class="description fields-control-description"><?php echo $this->description; ?></span>
					<?php endif; ?>
					<input type="<?php echo esc_attr( $this->type ); ?>" <?php $this->input_attrs(); ?> value="<?php echo esc_attr( $this->value() ); ?>" <?php $this->link(); ?> />
				</label>
				<?php
				break;
		}

	}

	/**
	 * Render the control's JS template.
	 *
	 * This function is only run for control types that have been registered with
	 * {@see WP_Fields_API::register_control_type()}.
	 *
	 * In the future, this will also print the template for the control's container
	 * element and be override-able.
	 */
	public function print_template() {

?>
    <script type="text/html" id="tmpl-fields-<?php echo esc_attr( $this->object_type ); ?>-control-<?php echo esc_attr( $this->type ); ?>-content">
        <?php $this->content_template(); ?>
    </script>
<?php

	}

	/**
	 * An Underscore (JS) template for this control's content (but not its container).
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see WP_Fields_API_Control::to_json()}.
	 *
	 * @see WP_Fields_API_Control::print_template()
	 */
	public function content_template() {

		// Nothing by default

	}

}

/**
 * Fields API Color Control class.
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Color_Control extends WP_Fields_API_Control {

	/**
	 * @access public
	 * @var string
	 */
	public $type = 'color';

	/**
	 * @access public
	 * @var array
	 */
	public $statuses = array();

	/**
	 * Constructor.
	 *
	 * @since 3.4.0
	 * @uses WP_Fields_API_Control::__construct()
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Control ID.
	 * @param array                $args    Optional. Arguments to override class property defaults.
	 */
	public function __construct( $manager, $id, $args = array() ) {

		$this->statuses = array(
			'' => __( 'Default' ),
		);

		parent::__construct( $manager, $id, $args );

	}

	/**
	 * Enqueue scripts/styles for the color picker.
	 *
	 * @since 3.4.0
	 */
	public function enqueue() {
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @uses WP_Fields_API_Control::json()
	 */
	public function json() {

		$json = parent::json();

		$json['statuses']     = $this->statuses;
		$json['defaultValue'] = $this->setting->default;

		return $json;

	}

	/**
	 * Don't render the control content from PHP, as it's rendered via JS on load.
	 */
	public function render_content() {

		// @todo Figure out what to do for render_content vs content_template for purposes of Customizer vs other Fields implementations

	}

	/**
	 * Render a JS template for the content of the color picker control.
	 */
	public function content_template() {

		// @todo Figure out what to do for render_content vs content_template for purposes of Customizer vs other Fields implementations
		?>
		<# var defaultValue = '';
		if ( data.defaultValue ) {
			if ( '#' !== data.defaultValue.substring( 0, 1 ) ) {
				defaultValue = '#' + data.defaultValue;
			} else {
				defaultValue = data.defaultValue;
			}
			defaultValue = ' data-default-color=' + defaultValue; // Quotes added automatically.
		} #>
		<label>
			<# if ( data.label ) { #>
				<span class="customize-control-title">{{{ data.label }}}</span>
			<# } #>
			<# if ( data.description ) { #>
				<span class="description customize-control-description">{{{ data.description }}}</span>
			<# } #>
			<div class="customize-control-content">
				<input class="color-picker-hex" type="text" maxlength="7" placeholder="<?php esc_attr_e( 'Hex Value' ); ?>" {{ defaultValue }} />
			</div>
		</label>
		<?php
	}
}

/**
 * Customize Media Control class.
 *
 * @since 4.2.0
 *
 * @see WP_Fields_API_Control
 */
class WP_Fields_API_Media_Control extends WP_Fields_API_Control {
	/**
	 * Control type.
	 *
	 * @since 4.2.0
	 * @access public
	 * @var string
	 */
	public $type = 'media';

	/**
	 * Media control mime type.
	 *
	 * @since 4.2.0
	 * @access public
	 * @var string
	 */
	public $mime_type = '';

	/**
	 * Button labels.
	 *
	 * @since 4.2.0
	 * @access public
	 * @var array
	 */
	public $button_labels = array();

	/**
	 * Constructor.
	 *
	 * @since 4.1.0
	 * @since 4.2.0 Moved from WP_Fields_API_Upload_Control.
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Control ID.
	 * @param array                $args    Optional. Arguments to override class property defaults.
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );

		$this->button_labels = array(
			'select'       => __( 'Select File' ),
			'change'       => __( 'Change File' ),
			'default'      => __( 'Default' ),
			'remove'       => __( 'Remove' ),
			'placeholder'  => __( 'No file selected' ),
			'frame_title'  => __( 'Select File' ),
			'frame_button' => __( 'Choose File' ),
		);
	}

	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @since 3.4.0
	 * @since 4.2.0 Moved from WP_Fields_API_Upload_Control.
	 */
	public function enqueue() {
		wp_enqueue_media();
	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @since 3.4.0
	 * @since 4.2.0 Moved from WP_Fields_API_Upload_Control.
	 *
	 * @see WP_Fields_API_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();
		$this->json['label'] = html_entity_decode( $this->label, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$this->json['mime_type'] = $this->mime_type;
		$this->json['button_labels'] = $this->button_labels;
		$this->json['canUpload'] = current_user_can( 'upload_files' );

		$value = $this->value();

		if ( is_object( $this->setting ) ) {
			if ( $this->setting->default ) {
				// Fake an attachment model - needs all fields used by template.
				// Note that the default value must be a URL, NOT an attachment ID.
				$type = in_array( substr( $this->setting->default, -3 ), array( 'jpg', 'png', 'gif', 'bmp' ) ) ? 'image' : 'document';
				$default_attachment = array(
					'id' => 1,
					'url' => $this->setting->default,
					'type' => $type,
					'icon' => wp_mime_type_icon( $type ),
					'title' => basename( $this->setting->default ),
				);

				if ( 'image' === $type ) {
					$default_attachment['sizes'] = array(
						'full' => array( 'url' => $this->setting->default ),
					);
				}

				$this->json['defaultAttachment'] = $default_attachment;
			}

			if ( $value && $this->setting->default && $value === $this->setting->default ) {
				// Set the default as the attachment.
				$this->json['attachment'] = $this->json['defaultAttachment'];
			} elseif ( $value ) {
				$this->json['attachment'] = wp_prepare_attachment_for_js( $value );
			}
		}
	}

	/**
	 * Don't render any content for this control from PHP.
	 *
	 * @since 3.4.0
	 * @since 4.2.0 Moved from WP_Fields_API_Upload_Control.
	 *
	 * @see WP_Fields_API_Media_Control::content_template()
	 */
	public function render_content() {}

	/**
	 * Render a JS template for the content of the media control.
	 *
	 * @since 4.1.0
	 * @since 4.2.0 Moved from WP_Fields_API_Upload_Control.
	 */
	public function content_template() {
		?>
		<label for="{{ data.settings['default'] }}-button">
			<# if ( data.label ) { #>
				<span class="customize-control-title">{{ data.label }}</span>
			<# } #>
			<# if ( data.description ) { #>
				<span class="description customize-control-description">{{{ data.description }}}</span>
			<# } #>
		</label>

		<# if ( data.attachment && data.attachment.id ) { #>
			<div class="current">
				<div class="container">
					<div class="attachment-media-view attachment-media-view-{{ data.attachment.type }} {{ data.attachment.orientation }}">
						<div class="thumbnail thumbnail-{{ data.attachment.type }}">
							<# if ( 'image' === data.attachment.type && data.attachment.sizes && data.attachment.sizes.medium ) { #>
								<img class="attachment-thumb" src="{{ data.attachment.sizes.medium.url }}" draggable="false" />
							<# } else if ( 'image' === data.attachment.type && data.attachment.sizes && data.attachment.sizes.full ) { #>
								<img class="attachment-thumb" src="{{ data.attachment.sizes.full.url }}" draggable="false" />
							<# } else if ( 'audio' === data.attachment.type ) { #>
								<# if ( data.attachment.image && data.attachment.image.src && data.attachment.image.src !== data.attachment.icon ) { #>
									<img src="{{ data.attachment.image.src }}" class="thumbnail" draggable="false" />
								<# } else { #>
									<img src="{{ data.attachment.icon }}" class="attachment-thumb type-icon" draggable="false" />
								<# } #>
								<p class="attachment-meta attachment-meta-title">&#8220;{{ data.attachment.title }}&#8221;</p>
								<# if ( data.attachment.album || data.attachment.meta.album ) { #>
									<p class="attachment-meta"><em>{{ data.attachment.album || data.attachment.meta.album }}</em></p>
								<# } #>
								<# if ( data.attachment.artist || data.attachment.meta.artist ) { #>
									<p class="attachment-meta">{{ data.attachment.artist || data.attachment.meta.artist }}</p>
								<# } #>
								<audio style="visibility: hidden" controls class="wp-audio-shortcode" width="100%" preload="none">
									<source type="{{ data.attachment.mime }}" src="{{ data.attachment.url }}"/>
								</audio>
							<# } else if ( 'video' === data.attachment.type ) { #>
								<div class="wp-media-wrapper wp-video">
									<video controls="controls" class="wp-video-shortcode" preload="metadata"
										<# if ( data.attachment.image && data.attachment.image.src !== data.attachment.icon ) { #>poster="{{ data.attachment.image.src }}"<# } #>>
										<source type="{{ data.attachment.mime }}" src="{{ data.attachment.url }}"/>
									</video>
								</div>
							<# } else { #>
								<img class="attachment-thumb type-icon icon" src="{{ data.attachment.icon }}" draggable="false" />
								<p class="attachment-title">{{ data.attachment.title }}</p>
							<# } #>
						</div>
					</div>
				</div>
			</div>
			<div class="actions">
				<# if ( data.canUpload ) { #>
					<button type="button" class="button remove-button"><?php echo $this->button_labels['remove']; ?></button>
					<button type="button" class="button upload-button" id="{{ data.settings['default'] }}-button"><?php echo $this->button_labels['change']; ?></button>
					<div style="clear:both"></div>
				<# } #>
			</div>
		<# } else { #>
			<div class="current">
				<div class="container">
					<div class="placeholder">
						<div class="inner">
							<span>
								<?php echo $this->button_labels['placeholder']; ?>
							</span>
						</div>
					</div>
				</div>
			</div>
			<div class="actions">
				<# if ( data.defaultAttachment ) { #>
					<button type="button" class="button default-button"><?php echo $this->button_labels['default']; ?></button>
				<# } #>
				<# if ( data.canUpload ) { #>
					<button type="button" class="button upload-button" id="{{ data.settings['default'] }}-button"><?php echo $this->button_labels['select']; ?></button>
				<# } #>
				<div style="clear:both"></div>
			</div>
		<# } #>
		<?php
	}
}

/**
 * Customize Upload Control Class.
 *
 * @since 3.4.0
 *
 * @see WP_Fields_API_Media_Control
 */
class WP_Fields_API_Upload_Control extends WP_Fields_API_Media_Control {
	public $type          = 'upload';
	public $mime_type     = '';
	public $button_labels = array();
	public $removed = ''; // unused
	public $context; // unused
	public $extensions = array(); // unused

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @since 3.4.0
	 *
	 * @uses WP_Fields_API_Media_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();

		$value = $this->value();
		if ( $value ) {
			// Get the attachment model for the existing file.
			$attachment_id = attachment_url_to_postid( $value );
			if ( $attachment_id ) {
				$this->json['attachment'] = wp_prepare_attachment_for_js( $attachment_id );
			}
		}
	}
}

/**
 * Customize Image Control class.
 *
 * @since 3.4.0
 *
 * @see WP_Fields_API_Upload_Control
 */
class WP_Fields_API_Image_Control extends WP_Fields_API_Upload_Control {
	public $type = 'image';
	public $mime_type = 'image';

	/**
	 * Constructor.
	 *
	 * @since 3.4.0
	 * @uses WP_Fields_API_Upload_Control::__construct()
	 *
	 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
	 * @param string               $id      Control ID.
	 * @param array                $args    Optional. Arguments to override class property defaults.
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );

		$this->button_labels = array(
			'select'       => __( 'Select Image' ),
			'change'       => __( 'Change Image' ),
			'remove'       => __( 'Remove' ),
			'default'      => __( 'Default' ),
			'placeholder'  => __( 'No image selected' ),
			'frame_title'  => __( 'Select Image' ),
			'frame_button' => __( 'Choose Image' ),
		);
	}

	/**
	 * @since 3.4.2
	 * @deprecated 4.1.0
	 */
	public function prepare_control() {}

	/**
	 * @since 3.4.0
	 * @deprecated 4.1.0
	 *
	 * @param string $id
	 * @param string $label
	 * @param mixed $callback
	 */
	public function add_tab( $id, $label, $callback ) {}

	/**
	 * @since 3.4.0
	 * @deprecated 4.1.0
	 *
	 * @param string $id
	 */
	public function remove_tab( $id ) {}

	/**
	 * @since 3.4.0
	 * @deprecated 4.1.0
	 *
	 * @param string $url
	 * @param string $thumbnail_url
	 */
	public function print_tab_image( $url, $thumbnail_url = null ) {}
}