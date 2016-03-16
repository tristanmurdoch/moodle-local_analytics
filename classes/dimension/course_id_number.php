<?php
/**
 * @file
 * Course name dimension definition.
 */

namespace local_analytics\dimension;

require_once 'dimension_interface.php';

class course_id_number implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'course_id_number';

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

        return $COURSE->idnumber;
    }
}
