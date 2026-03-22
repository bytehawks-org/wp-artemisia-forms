<?php
/**
 * Core Form Model.
 *
 * @package ByteHawks\ArtemisiaForms\Models
 */

namespace ByteHawks\ArtemisiaForms\Models;

/**
 * Model class for interacting with wp_artemisia_forms table.
 */
class Form {

    /**
     * Form ID.
     *
     * @var int|null
     */
    public $id;

    /**
     * Form title.
     *
     * @var string
     */
    public $title;

    /**
     * Form status (e.g., 'published', 'draft').
     *
     * @var string
     */
    public $status;

    /**
     * Parsed JSON configuration array.
     *
     * @var array
     */
    public $config;

    /**
     * Created datetime string.
     *
     * @var string
     */
    public $created_at;

    /**
     * Updated datetime string.
     *
     * @var string
     */
    public $updated_at;

    /**
     * Fetch a form by ID from the database.
     *
     * @param int $id The Form ID.
     * @return self|null Returns a Form instance if found, null otherwise.
     */
    public static function get( $id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'artemisia_forms';

        $record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d LIMIT 1", $id ) );

        if ( ! $record ) {
            return null;
        }

        $form = new self();
        $form->id         = (int) $record->id;
        $form->title      = $record->title;
        $form->status     = $record->status;
        $form->config     = json_decode( $record->config, true ) ?: array();
        $form->created_at = $record->created_at;
        $form->updated_at = $record->updated_at;

        return $form;
    }

    /**
     * Create or update the form in the database.
     *
     * @return bool|int False on error, number of rows affected (or ID created) on success.
     */
    public function save() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'artemisia_forms';

        $data = array(
            'title'  => $this->title,
            'status' => $this->status ?: 'draft',
            'config' => wp_json_encode( $this->config ?: array() ),
        );

        $format = array( '%s', '%s', '%s' );

        if ( $this->id ) {
            // Update an existing form
            $result = $wpdb->update(
                $table_name,
                $data,
                array( 'id' => $this->id ),
                $format,
                array( '%d' )
            );
            return $result !== false;
        } else {
            // Insert a new form
            $result = $wpdb->insert( $table_name, $data, $format );
            if ( $result ) {
                $this->id = $wpdb->insert_id;
                return $this->id;
            }
            return false;
        }
    }
}
