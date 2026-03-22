<?php
/**
 * Abstract Field representation.
 *
 * @package ByteHawks\ArtemisiaForms\Fields
 */

namespace ByteHawks\ArtemisiaForms\Fields;

/**
 * Base abstract class that all Form Fields (Text, Email, Select, Image, etc.) must extend.
 */
abstract class AbstractField {

    /**
     * Field ID in the form context.
     *
     * @var string
     */
    protected $id;

    /**
     * Core configuration properties of this field instance.
     * E.g. label, placeholder, is_required, etc.
     *
     * @var array
     */
    protected $props = array();

    /**
     * Constructor.
     *
     * @param string $id    The unique ID of the field in the form.
     * @param array  $props The properties defining the field behavior and display.
     */
    public function __construct( $id, $props = array() ) {
        $this->id    = $id;
        $this->props = wp_parse_args( $props, $this->get_default_props() );
    }

    /**
     * Get the descriptive name of the field type (e.g. "Text Input", "Image Upload").
     *
     * @return string
     */
    abstract public static function get_type_name();

    /**
     * Defines the default properties for this specific field type.
     * Subclasses must provide an array of default configurations (e.g. label defaults).
     *
     * @return array
     */
    abstract protected function get_default_props();

    /**
     * Validate a submitted value against this field's rules.
     *
     * @param mixed $value The user-submitted value.
     * @return true|\WP_Error Returns true if valid, or a WP_Error object if validation fails.
     */
    public function validate( $value ) {
        // Base required check
        $is_required = isset( $this->props['required'] ) ? (bool) $this->props['required'] : false;

        if ( $is_required && ( is_null( $value ) || $value === '' ) ) {
            return new \WP_Error(
                'validation_failed',
                sprintf( __( 'The field "%s" is required.', 'artemisia-forms' ), $this->props['label'] ?? $this->id ),
                array( 'status' => 400 )
            );
        }

        return true;
    }

    /**
     * Sanitize a submitted value before storing it.
     * Subclasses can override this to provide type-specific sanitization.
     *
     * @param mixed $value The user-submitted value.
     * @return mixed The sanitized value.
     */
    public function sanitize( $value ) {
        if ( is_string( $value ) ) {
            return sanitize_text_field( $value );
        }
        return $value;
    }

    /**
     * Calculate a value based on custom logic.
     * Used for "Calculated Fields" feature defined in requirements.
     *
     * @param array $all_submitted_data Access to all other fields to perform math/logic.
     * @return mixed Calculated value.
     */
    public function calculate( $all_submitted_data ) {
        // By default, fields do not calculate anything. They just return null.
        return null;
    }

}
