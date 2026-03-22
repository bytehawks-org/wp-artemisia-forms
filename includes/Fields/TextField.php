<?php
/**
 * Text Input Field.
 *
 * @package ByteHawks\ArtemisiaForms\Fields
 */

namespace ByteHawks\ArtemisiaForms\Fields;

/**
 * Basic text field representation supporting validation.
 */
class TextField extends AbstractField {

    /**
     * Get the descriptive name.
     *
     * @return string
     */
    public static function get_type_name() {
        return __( 'Text Input', 'artemisia-forms' );
    }

    /**
     * Define defaults for a text field.
     *
     * @return array
     */
    protected function get_default_props() {
        return array(
            'label'       => __( 'Untitled Text', 'artemisia-forms' ),
            'placeholder' => '',
            'required'    => false,
            'max_length'  => 255,
        );
    }

    /**
     * Override validation to include a specific max length check.
     *
     * @param mixed $value The user-submitted value.
     * @return true|\WP_Error
     */
    public function validate( $value ) {
        // Run base validation (e.g. required check)
        $parent_validation = parent::validate( $value );
        if ( is_wp_error( $parent_validation ) ) {
            return $parent_validation;
        }

        // Specific Text Field logic: Check max length if provided
        $max_length = isset( $this->props['max_length'] ) ? (int) $this->props['max_length'] : 255;
        if ( ! empty( $value ) && mb_strlen( $value ) > $max_length ) {
            return new \WP_Error(
                'validation_failed',
                sprintf( __( 'The field "%s" exceeds the maximum length of %d characters.', 'artemisia-forms' ), $this->props['label'], $max_length ),
                array( 'status' => 400 )
            );
        }

        return true;
    }
}
