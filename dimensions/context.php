<?php
/**
 * @file
 * User name dimension definition.
 */

namespace local\analytics\dimensions;

class context {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'context';

    /**
     * Get the value for js to send.
     *
     * @return mixed
     *   The value of the dimension.
     */
    public function value() {
        global $COURSE;

        $context = \context_course::instance($COURSE->id);
        return $context->get_context_name();
    }
}