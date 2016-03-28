<?php
/**
 * WordPress Fields API Widget Integration class
 *
 * @package WordPress
 * @subpackage Fields API
 */

/**
 * Fields API Widgets class.
 *
 * The Fields API integration with WP_Widget
 */
class WP_Widgets_Fields_API extends WP_Widget {

    /**
     * Holds the widget data
     *
     * @var array
     */
    protected $widget_data = null;

    /**
     * Holds a reference to the corresponding WP_Fields_API_Form
     *
     * @var WP_Fields_API_Form
     */
    protected $form_instance = null;

    /**
     * {@inheritdoc}
     */
    public function __construct( $id_base, $name, $widget_options = array(), $control_options = array() ) {
        global $wp_fields;

        WP_Fields_API_Form_Widget::register( 'widget',  $id_base, $id_base, array( 'widget_instance' => $this ) );

        // Get form
        $this->form_instance = $wp_fields->get_form( 'widget', $id_base, $id_base );

        add_action( 'in_widget_form',  array( $this, 'generate_form' ), 10, 3 );
        add_action( 'fields_register', array( $this, '_fields_register_callback' ) );

        parent::__construct( $id_base, $name, $widget_options, $control_options );
    }

    /**
     * Outputs the Fields API Form
     *
     * @param $widget   The widget instance, passed by reference.
     * @param $return   Return null if new fields are added.
     * @param $instance An array of the widget's settings.
     */
    public function generate_form( $widget, $return, $instance ) {
        //Only use Fields API if the widget is a instance of WP_Widgets_Fields_API
        if ( ! $widget instanceof WP_Widgets_Fields_API ) {
            return;
        }

        if ( $this->form_instance ) {
            $this->form_instance->maybe_render();
        }
    }

    /**
     * Returns the value of a given field based on the $instance data
     *
     * @param $item_id
     * @param $field
     * @return mixed
     */
    public function field_value( $item_id, $field ) {
        //save this for later
        if ( is_null( $this->widget_data ) ) {
            $this->widget_data = $this->get_settings();
        }

        if ( array_key_exists( $this->number, $this->widget_data ) ) {
            $instance = $this->widget_data[ $this->number ];

            if ( isset( $instance[ $field->id ] ) ) {
                return $instance[ $field->id ];
            }

            return $field->default;
        }

        return $field->default;
    }

    /**
     * The old way for outputting the form, defaults to empty
     *
     * @param array $instance The Widget $instance data
     *
     * @return string The output of the form
     */
    public function form( $instance ) {
        return '';
    }

    /**
     * Call the fields methods where sections, controls and fields are registered
     */
    public function _fields_register_callback() {
        //Only use Fields API if the widget is a instance of WP_Widgets_Fields_API
        if ( ! $this instanceof WP_Widgets_Fields_API ) {
            return;
        }

        global $wp_fields;

        $this->fields( $wp_fields );
    }

    /**
     * Widgets should override this method to add fields
     *
     * @param $wp_fields The WP_Fields_API Object
     */
    public function fields( $wp_fields ) {
        //do nothing by default
    }

    /**
     * Saving the Widget Fields
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     * @return array
     */
    final public function update( $new_instance, $old_instance ) {
        $instance = array();

        $sections = $this->form_instance->get_sections();

        foreach ( $sections as $section_id => $section ) {
            $controls = $section->get_controls();

            foreach( $controls as $control_id => $control ) {
                $field = $control->get_field();

                if ( isset( $new_instance[ $field->id ] ) ) {
                    $instance[ $field->id ] = $field->sanitize( $new_instance[ $field->id ] );
                }
            }
        }

        return $instance;
    }
}