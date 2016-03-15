<?php
/**
 * @file
 * Course name dimension definition.
 */

namespace local\analytics\dimensions;

require_once dirname(__DIR__) . '/dimension_interface.php';

class course_category_hierarchy_full_path implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'course_category_hierarchy_full_path';

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
        $parents = $context->get_parent_contexts();

        // The lowest level is given first.
        $parents = array_reverse($parents);

        // Elide the top level ('System') context.
        array_shift($parents);

        $result = '';

        foreach($parents as $key => $content) {
            $result .= '\\' . $content->get_context_name(false);
        }

        $result = substr($result, 1);
        return $result;
    }
}
