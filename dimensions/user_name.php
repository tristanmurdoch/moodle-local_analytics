<?php
/**
 * @file
 * User name dimension definition.
 */

namespace local\analytics\dimensions;

require_once dirname(__DIR__) . '/dimension_interface.php';

class user_name implements dimension_interface {
    /**
     * Name of dimension - used in lang plugin and arrays.
     */
    static $name = 'user_name';

    /**
     * Get the value for js to send.
     *
     * @return mixed
     *   The value of the dimension.
     */
    public function value() {
        global $USER;
        $user = $USER;
        $is_masquerading = \core\session\manager::is_loggedinas();

        if ($is_masquerading) {
            $use_real = get_config('local_analytics', 'masquerade_handling');
            if ($use_real) {
                $user = \core\session\manager::get_realuser();
            }
        }

        $real_name = fullname($user);
        return $real_name;
    }
}