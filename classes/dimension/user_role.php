<?php
/**
 * @file
 * User name dimension definition.
 */

namespace local_analytics\dimension;

require_once 'dimension_interface.php';

class user_role implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'user_role';

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
        global $USER, $COURSE;
        if (is_siteadmin($USER->id)) {
            $rolestr = "Admin";
        } else {
            $context = \context_course::instance($COURSE->id);
            $roles = get_user_roles($context, $USER->id);
            $rolestr = array ();
            foreach ($roles as $role) {
                $rolestr[] = role_get_name($role, $context);
            }
            $rolestr = implode(', ', $rolestr);
        }
        return $rolestr;
    }
}
