<?php
/**
 * @file
 * User name dimension definition.
 */

namespace local_analytics\dimension;

require_once 'dimension_interface.php';

class user_email_domain implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'user_email_domain';

    /**
     * Scope of the dimension.
     */
    static $scope = 'visit';

    /**
     * Get the value for js to send.
     *
     * @return mixed
     *   The value of the dimension.
     */
    public function value() {
        global $USER;

        // Handle guest without error.
        if (!isset($USER->email)) {
            return FALSE;
        }

        $parts = explode('@', $USER->email);
        return $parts[1];
    }
}
