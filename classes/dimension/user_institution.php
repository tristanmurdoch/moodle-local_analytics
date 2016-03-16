<?php
/**
 * @file
 * User name dimension definition.
 */

namespace local_analytics\dimension;

require_once 'dimension_interface.php';

class user_institution implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'user_institution';

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
        if (!isset($USER->institution)) {
            return FALSE;
        }

        return $USER->institution;
    }
}
