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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Analytics
 *
 * This module provides extensive analytics on a platform of choice
 * Currently support Google Analytics and Piwik
 *
 * @package    local_analytics
 * @copyright  David Bezemer <info@davidbezemer.nl>, www.davidbezemer.nl
 * @author     David Bezemer <info@davidbezemer.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if (is_siteadmin()) {
    $settings = new admin_settingpage('local_analytics', get_string('pluginname', 'local_analytics'));
    $ADMIN->add('localplugins', $settings);

    $name = 'local_analytics/enabled';
    $title = get_string('enabled', 'local_analytics');
    $description = get_string('enabled_desc', 'local_analytics');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_analytics/analytics';
    $title = get_string('analytics', 'local_analytics');
    $description = get_string('analyticsdesc', 'local_analytics');
    $ganalytics = get_string('ganalytics', 'local_analytics');
    $guniversal = get_string('guniversal', 'local_analytics');
    $piwik = get_string('piwik', 'local_analytics');
    $default = 'piwik';
    $choices = array(
        'piwik' => $piwik,
        'ganalytics' => $ganalytics,
        'guniversal' => $guniversal,
    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    $name = 'local_analytics/siteid';
    $title = get_string('siteid', 'local_analytics');
    $description = get_string('siteid_desc', 'local_analytics');
    $default = '1';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);


    $name = 'local_analytics/piwikusedimensions';
    $title = get_string('piwikusedimensions', 'local_analytics');
    $description = get_string('piwikusedimensions_desc', 'local_analytics');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_analytics/piwik_number_dimensions';
    $title = get_string('piwik_number_dimensions', 'local_analytics');
    $description = get_string('piwik_number_dimensions_desc', 'local_analytics');
    $default = '5';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    // Get a list of the dimension values that may be used.
    require_once(__DIR__ . '/dimensions.php');

    // Find out what scopes are supported (making it future proof)
    $plugins = \local_analytics\dimensions::instantiate_plugins();
    $num_dimensions = get_config('local_analytics', 'piwik_number_dimensions', 5);

    foreach ($plugins as $scope => $scope_plugins) {
        $choices = \local_analytics\dimensions::setting_options($scope);

        for ($i = 1; $i <= $num_dimensions; $i++) {
            $name = 'local_analytics/piwikdimension' . $scope . '_' . $i;
            $lang_args = new \stdClass();
            $lang_args->id = $i;
            $lang_args->scope = $scope;
            $title = get_string('piwikdimension', 'local_analytics', $lang_args);
            $description = get_string('piwikdimensiondesc', 'local_analytics', $lang_args);
            $setting = new admin_setting_configselect($name, $title, $description, '', $choices);
            $settings->add($setting);
        }
    }

    $name = 'local_analytics/imagetrack';
    $title = get_string('imagetrack', 'local_analytics');
    $description = get_string('imagetrack_desc', 'local_analytics');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_analytics/siteurl';
    $title = get_string('siteurl', 'local_analytics');
    $description = get_string('siteurl_desc', 'local_analytics');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $settings->add($setting);

    $name = 'local_analytics/trackadmin';
    $title = get_string('trackadmin', 'local_analytics');
    $description = get_string('trackadmin_desc', 'local_analytics');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_analytics/masquerade_handling';
    $title = get_string('masquerade_handling', 'local_analytics');
    $description = get_string('masquerade_handling_desc', 'local_analytics');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_analytics/cleanurl';
    $title = get_string('cleanurl', 'local_analytics');
    $description = get_string('cleanurl_desc', 'local_analytics');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'local_analytics/location';
    $title = get_string('location', 'local_analytics');
    $description = get_string('locationdesc', 'local_analytics');
    $head = get_string('head', 'local_analytics');
    $topofbody = get_string('topofbody', 'local_analytics');
    $footer = get_string('footer', 'local_analytics');
    $default = 'head';
    $choices = array(
        'head' => $head,
        'topofbody' => $topofbody,
        'footer' => $footer,
    );
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

}
