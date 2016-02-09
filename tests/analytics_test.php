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
 * Analytics tests.
 *
 * @package    local_analytics
 * @category   test
 * @copyright  2016 Catalyst IT
 * @author     Nigel Cunningham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__DIR__) . '/lib.php');

/**
 * Analytics tests class.
 *
 * @package    local_analytics
 * @category   test
 * @copyright  2016 Catalyst IT
 * @author     Nigel Cunningham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mock_page {
    function user_is_editing() {
        return FALSE;
    }
}

class local_analytics_testcase extends advanced_testcase {
    /** @var stdClass Keeps course object */
    private $course;

    /** @var stdClass Keeps wiki object */
    private $wiki;

    /**
     * Setup test data.
     */
    public function setUp() {
        global $CFG;

        $this->resetAfterTest();
        $this->setAdminUser();

        // Create course and wiki.
        $this->course = $this->getDataGenerator()->create_course();
        $this->wiki = $this->getDataGenerator()->create_module('wiki', array('course' => $this->course->id));

        // Set the location where output will be added.
        set_config('location', 'local_analytics', 'header');
        $CFG->additionalhtmlheader = '';
    }

    /**
     * Test that enabling Piwik analytics causes no JS to be added.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is FALSE
     * THEN no additional HTML should be added to the output.
     *
     * @test
     */
    public function disabledAnalyticsResultsInNoOutput() {
        global $CFG;

        set_config('enabled', FALSE, 'local_analytics');

        local_analytics_execute();

        $this->assertEmpty($CFG->additionalhtmlheader);
    }

    /**
     * Test that Piwik track URL for a course is generated correctly.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_analytics_trackurl function in the piwik support is invoked
     * AND a course category name and course full name can be used
     * THEN it should return the expected URL.
     *
     * @test
     */
    public function piwikTrackUrlForCourse() {
        global $CFG, $PAGE;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($this->course->id);

        $piwik = new local_analytics_piwik();
        $trackurl = $piwik::trackurl();

        $this->assertEquals("'Miscellaneous/Test course 1/View'", $trackurl);
    }


    /**
     * Test that Piwik track URL for an activity within a course is generated correctly.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_analytics_trackurl function in the piwik support is invoked
     * AND a course category name, course full name and activity name can be used
     * THEN it should return the expected URL.
     *
     * @test
     */
    public function piwikTrackUrlForActivityInCourse() {
        global $CFG, $PAGE;

        $PAGE = new mock_page();
        $PAGE->context = context_module::instance($this->wiki->cmid);

        $piwik = new local_analytics_piwik();
        $trackurl = $piwik::trackurl();

        $this->assertEquals("'Miscellaneous/Test course 1/wiki/Wiki 1'", $trackurl);
    }

    /**
     * Test that enabling Piwik analytics causes appropriate JS to be added.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is FALSE
     * THEN no additional HTML should be added to the output.
     *
     * @test
     */
    public function enabledPiwikModuleResultsInNoOutput() {
        global $CFG;

        set_config('enabled', TRUE, 'local_analytics');
        set_config('analytics', 'piwik', 'local_analytics');

        require_once($CFG->dirroot . '/local/analytics/lib.php');

        $location = "additionalhtml" . get_config('local_analytics', 'location');
        $this->assertEmpty($CFG->$location);
    }

}
