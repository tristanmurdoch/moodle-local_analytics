<?php
/**
 * @file
 * User name dimension definition.
 */

namespace local\analytics\dimensions;

require_once dirname(__DIR__) . '/dimension_interface.php';

class user_institution implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'user_institution';

    /**
     * Get the value for js to send.
     *
     * @return mixed
     *   The value of the dimension.
     */
    public function value() {
        global $USER;
        if (is_siteadmin($USER->id)) {
            $rolestr = "Admin";
        } else {
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