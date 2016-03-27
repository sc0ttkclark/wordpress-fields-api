<?php

class WP_Widgets_Fields_API extends WP_Widget {

    protected $widget_data = null;

    protected $form_instance = null;

    public function __construct( $id_base, $name, $widget_options = array(), $control_options = array() ) {
        global $wp_fields;

        WP_Fields_API_Form_Widget::register( 'widget',  $id_base, $id_base, array( 'widget_instance' => $this ) );

        // Get form
        $this->form_instance = $wp_fields->get_form( 'widget', $id_base, $id_base );

        add_action( 'in_widget_form',  array( $this, 'generate_form' ), 10, 3 );
        add_action( 'fields_register', array( $this, '_fields_register_callback' ) );

        parent::__construct( $id_base, $name, $widget_options, $control_options );
    }

    public function generate_form( $widget, $return, $instance ) {
        //Only use Fields API if the widget is a instance of WP_Widgets_Fields_API
        if ( ! $widget instanceof WP_Widgets_Fields_API ) {
            return;
        }

        if ( $this->form_instance ) {
            $this->form_instance->maybe_render();
        }
    }

    public function field_value( $item_id, $field ) {
        $control = $field->control;
        
        if ( ! $control ) {
            return $field->default;
        }
        //save this for later
        if ( is_null( $this->widget_data ) ) {
            $this->widget_data = $this->get_settings();
        }

        if ( array_key_exists( $this->number, $this->widget_data ) ) {
            $instance = $this->widget_data[$this->number];

            if ( isset( $instance[ $control->id ] ) ) {
                return $instance[ $control->id ];
            }

            return $field->default;

        }
    }

    final public function form( $instance ) {
        return '';
    }

    public function _fields_register_callback() {
        //Only use Fields API if the widget is a instance of WP_Widgets_Fields_API
        if ( ! $this instanceof WP_Widgets_Fields_API ) {
            return;
        }

        global $wp_fields;

        $this->fields( $wp_fields );
    }

    public function fields( $wp_fields ) {
        //do nothing by default
    }

    /**
     * Processing widget options on save
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

                if ( isset( $new_instance[ $control->id ] ) ) {
                    $instance[ $control->id ] = $field->sanitize( $new_instance[ $control->id ] );
                }
            }
        }

        return $instance;
    }
}