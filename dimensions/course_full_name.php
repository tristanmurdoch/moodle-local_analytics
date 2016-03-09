<?php
/**
 * @file
 * Course name dimension definition.
 */

namespace local\analytics\dimensions;

class course_full_name {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'course_full_name';

    /**
     * Get the value for js to send.
     *
     * @return mixed
     *   The value of the dimension.
     */
    public function get_value() {
        global $COURSE;

        return $COURSE->fullname;
    }
}