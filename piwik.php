<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Analytics
 *
 * This module provides extensive analytics on a platform of choice
 * Currently support Google Analytics and Piwik
 *
 * @package local_analytics
 * @copyright David Bezemer <info@davidbezemer.nl>, www.davidbezemer.nl
 * @author David Bezemer <info@davidbezemer.nl>, Bas Brands <bmbrands@gmail.com>, Gavin Henrick <gavin@lts.ie>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('AbstractLocalAnalytics.php');

class local_analytics_piwik extends AbstractLocalAnalytics {
    /**
     * Build a custom variable string.
     *
     * @param integer $index
     *            The custom variable index number (1 through 5).
     *
     * @param string $name
     *            The key name.
     *
     * @param string $value
     *            The value string.
     *
     * @param string $context
     *            The string describing the context.
     *
     * @return string The generated string.
     */
    static public function local_get_custom_var_string($index, $name, $value, $context) {
        $result = '_paq.push(["setCustomVariable", ';
        $result .= $index . ', ';
        $result .= '"' . $name . '", ';
        $result .= '"' . $value . '", ';
        /* $result .= '"' . $context . '"'; */
        $result .= '"page"';
        $result .= "]);\n";

        return $result;
    }

    /**
     * see http://piwik.org/blog/2012/10/using-custom-variables-in-piwik-tutorial/
     *
     * There can be up to 5 Custom Variables in the piwik callback.
     * These are dynamically defined
     *
     * Note, in the future this will be replaced with 'Custom Dimensions'
     * - http://piwik.org/docs/custom-variables/
     * https://piwik.org/faq/general/faq_21117/
     */
    static public function local_insert_custom_moodle_vars() {
        global $DB, $PAGE, $COURSE, $SITE, $USER;
        $customvars = "";
        $context = context_course::instance($COURSE->id);

        // Option is visit/page
        // see http://piwik.org/docs/custom-variables/
        $scope = 'page';

        // User Details
        // "John Smith ([user_id])"
        $customvars .= self::local_get_custom_var_string(1, 'UserName', fullname($USER), $scope);

        // User Role
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
        $customvars .= self::local_get_custom_var_string(2, 'UserRole', $rolestr, $scope);

        // Context Type: i.e. page , course, activity ?
        $customvars .= self::local_get_custom_var_string(3, 'Context', $context->get_context_name(), $scope);

        // Course Name
        // "Mathematics for Accountants ([course_id])"
        $customvars .= self::local_get_custom_var_string(4, 'CourseName', $COURSE->fullname, $scope);

        // Max 5 Variables

        return $customvars;
    }

    static public function insert_tracking() {
        global $CFG, $USER;

        $imagetrack = get_config('local_analytics', 'imagetrack');
        $siteurl = get_config('local_analytics', 'siteurl');
        $siteid = get_config('local_analytics', 'siteid');
        $cleanurl = get_config('local_analytics', 'cleanurl');
        $location = "additionalhtml" . get_config('local_analytics', 'location');

        if (!empty($siteurl)) {
            if ($imagetrack) {
                $addition = '<noscript><p><img src="//' . $siteurl . '/piwik.php?idsite=' . $siteid . '" style="border:0;" alt="" /></p></noscript>';
            } else {
                $addition = '';
            }

            if ($cleanurl) {
                $doctitle = "_paq.push(['setDocumentTitle', " . self::trackurl() . "]);";
            } else {
                $doctitle = "";
            }

            if (self::shouldTrack()) {
                $CFG->$location .= "
    <!-- Start Piwik Code -->
    <script type='text/javascript'>
        var _paq = _paq || [];
        " . $doctitle . self::local_insert_custom_moodle_vars() . "
        _paq.push(['setUserId', $USER->id]);
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
          var u='//" . $siteurl . "/';
          _paq.push(['setTrackerUrl', u+'piwik.php']);
          _paq.push(['setSiteId', " . $siteid . "]); var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
        g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
        })();
    </script>" . $addition . "<!-- End Piwik Code -->\n";
            }
        }
    }
}
