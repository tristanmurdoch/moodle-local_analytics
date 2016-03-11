<?php
/**
 * @file
 * User name dimension definition.
 */

namespace local\analytics\dimensions;

require_once dirname(__DIR__) . '/dimension_interface.php';

class user_profile_field_faculty_cost_code implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'user_profile_field_faculty_cost_code';

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
        if (!isset($USER->profile['facultycostcode'])) {
            return FALSE;
        }

        return $USER->profile['facultycostcode'];
    }
}
