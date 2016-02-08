<?php
/**
 * @file analytics_interface.php
 * Interface for analytics support.
 */

interface local_analytics_interface {

    /**
     * Get the local analytics tracking URL.
     *
     * @return string
     *   The URL.
     */
    static public function trackurl();

    /**
     * Insert tracking.
     *
     * Insert the tracking script in output variables.
     */
    static public function insert_tracking();

}