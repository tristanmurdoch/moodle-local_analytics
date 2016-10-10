<?php
/**
 * @file
 * Course name dimension definition.
 */

namespace local_analytics\dimension;

require_once 'dimension_interface.php';

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
        global $COURSE, $USER, $DB;

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

        // Stolen from is_enrolled
        $sql = "SELECT e.enrol
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)
                      JOIN {user} u ON u.id = ue.userid
                     WHERE ue.userid = :userid AND u.deleted = 0";
        $params = array('userid' => $USER->id, 'courseid' => $COURSE->id);
        $method = $DB->get_field_sql($sql, $params, IGNORE_MULTIPLE);

        return $method;
    }
}
