<?php
/**
 * Fired during plugin activation.
 *
 * @package ByteHawks\ArtemisiaForms\Core
 */

namespace ByteHawks\ArtemisiaForms\Core;

/**
 * Activator class.
 */
class Activator {

    /**
     * Creates the required database tables.
     */
    public static function activate() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Table for Form Configurations
        $table_forms = $wpdb->prefix . 'artemisia_forms';
        $sql_forms = "CREATE TABLE {$table_forms} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            status varchar(50) NOT NULL DEFAULT 'draft',
            config longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        dbDelta( $sql_forms );

        // Table for Form Submissions
        $table_submissions = $wpdb->prefix . 'artemisia_submissions';
        $sql_submissions = "CREATE TABLE {$table_submissions} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            form_id bigint(20) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY form_id (form_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        dbDelta( $sql_submissions );
    }

}
