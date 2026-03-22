<?php
/**
 * Core Submission Model.
 *
 * @package ByteHawks\ArtemisiaForms\Models
 */

namespace ByteHawks\ArtemisiaForms\Models;

/**
 * Model class for interacting with wp_artemisia_submissions table.
 */
class Submission {

    /**
     * Submission ID.
     *
     * @var int|null
     */
    public $id;

    /**
     * Related Form ID.
     *
     * @var int
     */
    public $form_id;

    /**
     * User ID (if logged in at submission time).
     *
     * @var int|null
     */
    public $user_id;

    /**
     * Parsed JSON submission data array.
     *
     * @var array
     */
    public $data;

    /**
     * Created datetime string.
     *
     * @var string
     */
    public $created_at;

    /**
     * Fetch a submission by ID.
     *
     * @param int $id The Submission ID.
     * @return self|null Returns a Submission instance if found, null otherwise.
     */
    public static function get( $id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'artemisia_submissions';

        $record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d LIMIT 1", $id ) );

        if ( ! $record ) {
            return null;
        }

        $submission = new self();
        $submission->id         = (int) $record->id;
        $submission->form_id    = (int) $record->form_id;
        $submission->user_id    = $record->user_id ? (int) $record->user_id : null;
        $submission->data       = json_decode( $record->data, true ) ?: array();
        $submission->created_at = $record->created_at;

        return $submission;
    }

    /**
     * Create or update the submission in the database.
     *
     * @return bool|int False on error, number of rows affected (or ID created) on success.
     */
    public function save() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'artemisia_submissions';

        $data_array = array(
            'form_id' => $this->form_id,
            'user_id' => $this->user_id,
            'data'    => wp_json_encode( $this->data ?: array() ),
        );

        $format = array( '%d', '%d', '%s' );

        // If user is null, make sure it inserts NULL instead of 0
        if ( is_null( $data_array['user_id'] ) ) {
            unset( $data_array['user_id'] );
            // Adjust format to keep form_id and data only
            $format = array( '%d', '%s' );
        }

        if ( $this->id ) {
            // Update existing submission (rarely needed, data is usually immutable)
            $result = $wpdb->update(
                $table_name,
                $data_array,
                array( 'id' => $this->id ),
                $format,
                array( '%d' )
            );
            return $result !== false;
        } else {
            // Insert new submission
            $result = $wpdb->insert( $table_name, $data_array, $format );
            if ( $result ) {
                $this->id = $wpdb->insert_id;
                return $this->id;
            }
            return false;
        }
    }
}
