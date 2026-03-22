<?php
/**
 * Frontend Shortcode handler for Artemisia Forms.
 *
 * @package    ArtemisiaForms
 * @subpackage ArtemisiaForms\Frontend
 */

namespace ByteHawks\ArtemisiaForms\Frontend;

/**
 * Handles the registration and rendering of the form shortcode.
 */
class Shortcode {

    /**
     * Initialize the shortcode hooks.
     */
    public function init() {
        add_shortcode( 'artemisia_form', array( $this, 'render_form_shortcode' ) );
    }

    /**
     * Render the actual HTML for the form based on DB config.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render_form_shortcode( $atts ) {
        // Parse attributes.
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts, 'artemisia_form' );

        $form_id = (int) $atts['id'];

        if ( ! $form_id ) {
            return '<p>' . esc_html__( 'Please provide a valid form ID.', 'artemisia-forms' ) . '</p>';
        }

        // Load form from Database via OOP Model.
        $form = \ByteHawks\ArtemisiaForms\Models\Form::get( $form_id );

        if ( ! $form || 'published' !== $form->status ) {
            return '<p>' . esc_html__( 'Form not found or not published.', 'artemisia-forms' ) . '</p>';
        }

        $config = $form->config;
        $fields = isset( $config['fields'] ) && is_array( $config['fields'] ) ? $config['fields'] : array();

        if ( empty( $fields ) ) {
            return '<p>' . esc_html__( 'This form has no fields.', 'artemisia-forms' ) . '</p>';
        }

        // Start HTML output. We will submit to WP Admin AJAX or REST API later.
        ob_start();
        ?>
        <div class="artemisia-form-wrapper artemisia-form-<?php echo esc_attr( $form_id ); ?>">
            <form id="artemisia-form-<?php echo esc_attr( $form_id ); ?>" class="artemisia-frontend-form" method="POST">
                <input type="hidden" name="form_id" value="<?php echo esc_attr( $form_id ); ?>" />
                
                <?php foreach ( $fields as $field ) : ?>
                    <?php $this->render_field( $field ); ?>
                <?php endforeach; ?>

                <div class="artemisia-form-submit-wrapper">
                    <button type="submit" class="artemisia-submit-btn"><?php esc_html_e( 'Submit', 'artemisia-forms' ); ?></button>
                </div>
            </form>
            <div class="artemisia-form-messages" style="display: none;"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Renders a single field HTML based on its JSON config.
     *
     * @param array $field The field config array.
     */
    private function render_field( $field ) {
        $type = isset( $field['type'] ) ? $field['type'] : 'text';
        $id = 'af_field_' . sanitize_title( isset( $field['id'] ) ? $field['id'] : wp_rand( 100, 999 ) );
        $name = isset( $field['id'] ) ? esc_attr( $field['id'] ) : '';
        $label = isset( $field['label'] ) ? esc_html( $field['label'] ) : '';
        $placeholder = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';
        $required_attr = ! empty( $field['required'] ) ? 'required' : '';
        $custom_class = isset( $field['custom_class'] ) ? esc_attr( $field['custom_class'] ) : '';
        // Position logic (above, below, left, right, hidden)
        $label_pos = isset( $field['label_pos'] ) ? $field['label_pos'] : 'above'; 
        
        $wrapper_class = "artemisia-field-wrapper artemisia-field-type-{$type} {$custom_class}";
        ?>
        <div class="<?php echo esc_attr( $wrapper_class ); ?>" style="margin-bottom: 15px;">
            <?php if ( 'hidden' !== $label_pos ) : ?>
                <label for="<?php echo esc_attr( $id ); ?>" style="display: block; font-weight: bold; margin-bottom: 5px;">
                    <?php echo $label; ?> <?php echo $required_attr ? '<span class="af-required">*</span>' : ''; ?>
                </label>
            <?php endif; ?>

            <?php
            switch ( $type ) {
                case 'textarea':
                    echo '<textarea id="' . esc_attr( $id ) . '" name="' . $name . '" placeholder="' . $placeholder . '" ' . $required_attr . ' style="width: 100%; padding: 8px;"></textarea>';
                    break;
                case 'email':
                    echo '<input type="email" id="' . esc_attr( $id ) . '" name="' . $name . '" placeholder="' . $placeholder . '" ' . $required_attr . ' style="width: 100%; padding: 8px;" />';
                    break;
                case 'select':
                    echo '<select id="' . esc_attr( $id ) . '" name="' . $name . '" ' . $required_attr . ' style="width: 100%; padding: 8px;">';
                    echo '<option value="">' . esc_html__( 'Please select...', 'artemisia-forms' ) . '</option>';
                    // TODO: Expand this in the UI to allow defining options
                    echo '<option value="opt1">Option 1</option>';
                    echo '<option value="opt2">Option 2</option>';
                    echo '</select>';
                    break;
                case 'text':
                default:
                    echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . $name . '" placeholder="' . $placeholder . '" ' . $required_attr . ' style="width: 100%; padding: 8px;" />';
                    break;
            }
            ?>
            
            <?php if ( ! empty( $field['help_text'] ) ) : ?>
                <p class="af-help-text" style="font-size: 0.8em; color: #666; margin-top: 4px;"><?php echo esc_html( $field['help_text'] ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }
}
