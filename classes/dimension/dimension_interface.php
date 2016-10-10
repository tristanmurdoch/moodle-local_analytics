<?php
/**
 * @file
 * User name dimension definition.
 */

namespace local_analytics\dimension;

interface dimension_interface {

    /**
     * Get the value for js to send.
     *
     * @return mixed
     *   The value of the dimension.
     */
    public function value();

}
