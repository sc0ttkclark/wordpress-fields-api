<?php

class WP_Fields_API_Widget_Section extends WP_Fields_API_Section {

    /**
     * {@inheritdoc}
     */
    public $type = 'widget';

    /**
     * {@inheritdoc}
     */
    protected function render() {

        ?>
        <div class="fields-form-<?php echo esc_attr( $this->object_type ); ?>-section section-<?php echo esc_attr( $this->id ); ?>-wrap fields-api-section">
            <?php
            if ( $this->label && $this->display_label ) {
                ?>
                <h4><?php $this->render_label(); ?></h4>
                <?php
            }

            $this->render_controls();
            ?>
        </div>
        <?php

    }

    /**
     * {@inheritdoc}
     */
    protected function render_control( $control ) {
        $form               = $this->get_form();
        $widget_instance    = $form->widget_instance;

        $control->input_attrs['id'] = $widget_instance->get_field_id( $control->id );
        $input_id = $control->input_attrs['id'];

        $control->input_attrs['name'] = $widget_instance->get_field_name( $control->id );

        $control->input_attrs['class'] = 'widefat';


        $field = $control->get_field();
        //@fixme For some reason Fields API is not defining the 'control' property or even the parent property
        $field->control = $control;
        $field->value_callback = array( $widget_instance, 'field_value' );

        ?>
        <p <?php $control->wrap_attrs(); ?>>

            <?php if ( $control->label && $control->display_label ) { ?>
                <label for="<?php echo esc_attr( $input_id ); ?>">
                    <?php $control->render_label(); ?>
                </label>
            <?php } ?>

            <?php $control->maybe_render(); ?>

            <?php if ( $control->description ) { ?>
                <p class="description" style="padding: 0;">
                    <?php $control->render_description(); ?>
                </p>
            <?php } ?>
        </p>
        <?php

    }
}