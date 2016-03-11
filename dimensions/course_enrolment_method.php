<?php
/**
 * @file
 * Course name dimension definition.
 */

namespace local\analytics\dimensions;

require_once dirname(__DIR__) . '/dimension_interface.php';

class course_enrolment_method implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'course_enrolment_method';

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
        global $COURSE, $USER;

        $context = \context_course::instance($COURSE->id);

        // Based on is_enrolled in accesslib.php.

        if ($context->instanceid == SITEID) {
            // everybody participates on frontpage
            return false;
        }

        $until = enrol_get_enrolment_end($context->instanceid, $USER->id);

        if ($until === false) {
            return false;
        }

        return $COURSE->fullname;
    }
}
