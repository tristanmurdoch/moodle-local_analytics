<?php
/**
 * @file
 * Course name dimension definition.
 */

namespace local_analytics\dimension;

require_once 'dimension_interface.php';

class is_on_campus implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'is_on_campus';

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
        global $CFG;

        if (!isset($CFG->on_campus_ips)) {
            return FALSE;
        }

        return remoteip_in_list($CFG->on_campus_ips);
    }
}
