<?php
/**
 * Fired during plugin deactivation.
 *
 * @package ByteHawks\ArtemisiaForms\Core
 */

namespace ByteHawks\ArtemisiaForms\Core;

/**
 * Deactivator class.
 */
class Deactivator {

    /**
     * Tasks to perform on plugin deactivation.
     */
    public static function deactivate() {
        // We typically do not drop tables on deactivation to preserve user data.
        // Data removal should happen only within uninstall.php if the user requests it.
        
        // Flush rewrite rules in case we added custom endpoints 
        flush_rewrite_rules();
    }

}
