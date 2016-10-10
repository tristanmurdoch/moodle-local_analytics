<?php
/**
 * @file
 * User name dimension definition.
 */

namespace local_analytics\dimension;

require_once 'dimension_interface.php';

class context implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'context';

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

        $context = \context_course::instance($COURSE->id);
        return $context->get_context_name();
    }
}
