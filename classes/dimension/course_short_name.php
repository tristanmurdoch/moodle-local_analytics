<?php
/**
 * @file
 * Course name dimension definition.
 */

namespace local_analytics\dimension;

require_once 'dimension_interface.php';

class course_short_name implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'course_short_name';

    /**
     * Scope of the dimension.
     */
    static $scope = 'action';

    /**
     * Get the value for js to send.
     *
     * @return mixed
     *   The value of the dimension.
     */
    public function value() {
        global $COURSE;

        return $COURSE->shortname;
    }
}
