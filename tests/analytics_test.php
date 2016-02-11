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
    private $editing = FALSE;
    public $heading = 'This is a heading';

    function user_is_editing() {
        return $this->editing;
    }

    function set_editing($value) {
        $this->editing = $value;
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

        // Assign the guest role to the guest user in the course.
        $context = context_course::instance($this->course->id);
        role_assign(6, 1, $context->id);

        // Set the location where output will be added.
        set_config('location', 'header', 'local_analytics');
        $CFG->additionalhtmlheader = '';

        // Set default config to minimise repetition across tests.
        set_config('enabled', TRUE, 'local_analytics');
        set_config('analytics', 'piwik', 'local_analytics');
        set_config('imagetrack', TRUE, 'local_analytics');
        set_config('siteurl', 'somewhere', 'local_analytics');
        set_config('siteid', 2468, 'local_analytics');
        set_config('trackadmin', TRUE, 'local_analytics');
        set_config('cleanurl', TRUE, 'local_analytics');
        set_config('location', 'header', 'local_analytics');
    }

    /**
     * Test that shouldTrack returns TRUE for site admins when trackadmin on.
     *
     * GIVEN the local analytics plugin
     * WHEN its shouldTrack function is invoked
     * AND trackadmin is TRUE
     * AND the user is a site admin
     * THEN the result should be TRUE
     */
    public function shouldTrackReturnsTrueForSiteadminsWhenTrackAdminOn() {
      $piwik = new local_analytics_piwik();
      $actual = $piwik::shouldTrack();

      $this->assertTrue($actual);
    }

    /**
     * Test that shouldTrack returns FALSE for site admins when trackadmin off.
     *
     * GIVEN the local analytics plugin
     * WHEN its shouldTrack function is invoked
     * AND trackadmin is FALSE
     * AND the user is a site admin
     * THEN the result should be FALSE
     */
    public function shouldTrackReturnsFalseForSiteadminsWhenTrackAdminOff() {

      set_config('trackadmin', FALSE, 'local_analytics');

      $piwik = new local_analytics_piwik();
      $actual = $piwik::shouldTrack();

      $this->assertFalse($actual);
    }

    /**
     * Test that shouldTrack returns TRUE for non site admins.
     *
     * GIVEN the local analytics plugin
     * WHEN its shouldTrack function is invoked
     * AND the user is not a site admin
     * THEN the result should be TRUE
     */
    public function shouldTrackReturnsTrueForNonSiteadmins() {

      $this->setGuestUser();

      $piwik = new local_analytics_piwik();
      $actual = $piwik::shouldTrack();

      $this->assertTrue($actual);

      // Trackadmin shouldn't make a difference.
      set_config('trackadmin', FALSE, 'local_analytics');
      $actual = $piwik::shouldTrack();

      $this->assertTrue($actual);
    }

    /**
     * Test that enabling Piwik analytics causes no JS to be added.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is FALSE
     * THEN no additional content should be added to the output.
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
     * Test that having a bogus analytics engine setting enabled results in a debugging message.
     *
     * This test deliberately has 'test' at the start of the name because at the time of writing, the
     * debugging() code doesn't detect @ test when deciding how to dispose of a debugging message.
     * It will therefore mess up the debugging output without this hint.
     *
     * GIVEN the local analytics plugin
     * WHEN the enabled setting for the module is TRUE
     * AND the analytics module is invalid
     * THEN a debugging message should be added to the output.
     *
     * @test
     */
    public function testEnabledBogusModuleResultsInDebuggingMessage() {
        global $CFG;

        set_config('analytics', 'i_am_bogus', 'local_analytics');

        local_analytics_execute();

        $this->assertDebuggingCalled();
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
    public function piwikTrackUrlForCourseReturnsExpectedString() {
        global $CFG, $PAGE;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($this->course->id);

        $piwik = new local_analytics_piwik();
        $trackurl = $piwik::trackurl();

        $this->assertEquals("'Miscellaneous/Test course 1/View'", $trackurl);
    }

    /**
     * Test that Piwik track URL for a course with editing enabled is generated correctly.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_analytics_trackurl function in the piwik support is invoked
     * AND editing is enabled
     * AND a course category name and course full name can be used
     * THEN it should return the expected URL.
     *
     * @test
     */
    public function piwikTrackUrlForCourseBeingEditedReturnsExpectedString() {
        global $CFG, $PAGE;

        $PAGE = new mock_page();
        $PAGE->set_editing(TRUE);
        $PAGE->context = context_course::instance($this->course->id);

        $piwik = new local_analytics_piwik();
        $trackurl = $piwik::trackurl();

        $this->assertEquals("'Miscellaneous/Test course 1/Edit'", $trackurl);
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
    public function piwikTrackUrlForActivityInCourseReturnsExpectedString() {
        global $CFG, $PAGE;

        $PAGE = new mock_page();
        $PAGE->context = context_module::instance($this->wiki->cmid);

        $piwik = new local_analytics_piwik();
        $trackurl = $piwik::trackurl();

        $this->assertEquals("'Miscellaneous/Test course 1/wiki/Wiki 1'", $trackurl);
    }

    /**
     * Test that Piwik custom variable string generation produces anticipated output.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_get_custom_var_string function in the piwik support is invoked
     * THEN it should return the expected string.
     *
     * @test
     */
    public function piwikCustomVariableStringGenerationProducesExpectedOutput() {
        $piwik = new local_analytics_piwik();
        $actual = $piwik::local_get_custom_var_string(987, 'name', 'value', 'context');

        $expected = '_paq.push(["setCustomVariable", 987, "name", "value", "page"]);';
        $expected .= "\n";

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that Piwik insert custom moodle vars function returns the anticipated vars string for siteadmins.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_get_custom_var_string function in the piwik support is invoked
     * AND the user is a siteadmin
     * THEN it should return the expected string.
     *
     * @test
     */
    public function piwikCustomMoodleVarsGenerationProducesExpectedOutputForAdmin() {
        global $PAGE;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($this->course->id);

        $piwik = new local_analytics_piwik();
        $actual = $piwik::local_insert_custom_moodle_vars();

        $expected = '_paq.push(["setCustomVariable", 1, "UserName", "Admin User", "page"]);' . "\n";
        $expected .= '_paq.push(["setCustomVariable", 2, "UserRole", "Admin", "page"]);' . "\n";
        $expected .= '_paq.push(["setCustomVariable", 3, "Context", "Front page", "page"]);' . "\n";
        $expected .= '_paq.push(["setCustomVariable", 4, "CourseName", "PHPUnit test site", "page"]);' . "\n";

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that Piwik insert custom moodle vars function returns the anticipated vars string for non admins.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_get_custom_var_string function in the piwik support is invoked
     * AND the user is not a site administrator
     * THEN it should return the expected string.
     *
     * @test
     */
    public function piwikCustomMoodleVarsGenerationProducesExpectedOutputForNonAdmin() {
        global $PAGE, $COURSE, $USER, $DB;

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', array('id' => 1));

        $piwik = new local_analytics_piwik();
        $actual = $piwik::local_insert_custom_moodle_vars();

        $expected = '_paq.push(["setCustomVariable", 1, "UserName", "Guest user  ", "page"]);' . "\n";
        $expected .= '_paq.push(["setCustomVariable", 2, "UserRole", "Guest", "page"]);' . "\n";
        $expected .= '_paq.push(["setCustomVariable", 3, "Context", "Course: Test course 1", "page"]);' . "\n";
        $expected .= '_paq.push(["setCustomVariable", 4, "CourseName", "Test course 1", "page"]);' . "\n";

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that Piwik's insert tracking function works as expected.
     *
     * GIVEN the local analytics plugin
     * WHEN the insert_tracking function in the piwik support is invoked
     * THEN the Pikiw Javascript should be inserted in the additionalhtml
     *
     * @test
     */
    public function piwikInsertsExpectedJavascriptInAdditionalHtml() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', array('id' => 1));

        $piwik = new local_analytics_piwik();
        $piwik::insert_tracking();

        $actual = $CFG->additionalhtmlheader;
        $expected = file_get_contents(__DIR__ . '/expected/piwik_additional.html');

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that Piwik's insert tracking function works as expected without clean URLs.
     *
     * GIVEN the local analytics plugin
     * WHEN the insert_tracking function in the piwik support is invoked
     * AND the clean URL option is disabled
     * THEN the Pikiw Javascript should be as expected.
     *
     * @test
     */
    public function piwikInsertsExpectedJavascriptInAdditionalHtmlWithoutCleanUrlOption() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('cleanurl', FALSE, 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', array('id' => 1));

        $piwik = new local_analytics_piwik();
        $piwik::insert_tracking();

        $actual = $CFG->additionalhtmlheader;
        $expected = file_get_contents(__DIR__ . '/expected/piwik_additional_no_cleanurl.html');

        $this->assertEquals($expected, $actual);
    }


    /**
     * Test that Piwik's insert tracking function works as expected without image tracking turned on.
     *
     * GIVEN the local analytics plugin
     * WHEN the insert_tracking function in the piwik support is invoked
     * AND image tracking is disabled
     * THEN the Pikiw Javascript should be inserted in the additionalhtml as expected.
     *
     * @test
     */
    public function piwikInsertsExpectedJavascriptInAdditionalHtmlWithoutImageTrackOption() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('imagetrack', FALSE, 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', array('id' => 1));

        $piwik = new local_analytics_piwik();
        $piwik::insert_tracking();

        $actual = $CFG->additionalhtmlheader;
        $expected = file_get_contents(__DIR__ . '/expected/piwik_additional_no_imagetrack.html');

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that enabling Piwik analytics causes appropriate JS to be added.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the analytics module is set to Piwik
     * THEN the Piwik Javascript should be added to the output.
     *
     * @test
     */
    public function piwikModuleEnabledResultsInExpectedOutput() {
        global $CFG;

        local_analytics_execute();

        $this->assertNotEmpty($CFG->additionalhtmlheader);
    }

    /**
     * Test that enabling Google analytics universal causes appropriate JS to be added for a course page.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics Universal
     * THEN the GA Universal Javascript should be added to the output.
     *
     * @test
     */
    public function googleAnalyticsTrackUrlForCourseIsCorrectForCoursePageBeingViewed() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('analytics', 'ganalytics', 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', array('id' => 1));

        $ga = new local_analytics_ganalytics();
        $actual = $ga::trackurl(TRUE, TRUE);

        $this->assertEquals("'/Miscellaneous/Test+course+1/View'", $actual);
    }

    /**
     * Test that tracker URL for a course page being edited is correct when using GA.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics
     * THEN the tracker URL should be /Miscellaneous/Test+course+1/Edit.
     *
     * @test
     */
    public function googleAnalyticsTrackUrlForCourseIsCorrectForCoursePageBeingEdited() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('analytics', 'ganalytics', 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);
        $PAGE->set_editing(TRUE);

        $USER = $DB->get_record('user', array('id' => 1));

        $ga = new local_analytics_ganalytics();
        $actual = $ga::trackurl(TRUE, TRUE);

        $this->assertEquals("'/Miscellaneous/Test+course+1/Edit'", $actual);
    }

    /**
     * Test that GA track URL for an activity within a course is generated correctly.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_analytics_trackurl function in the GA support is invoked
     * AND a course category name, course full name and activity name can be used
     * THEN it should return the expected URL.
     *
     * @test
     */
    public function googleAnalyticsTrackUrlForActivityInCourseIsCorrect() {
        global $CFG, $PAGE;

        set_config('analytics', 'ganalytics', 'local_analytics');

        $PAGE = new mock_page();
        $PAGE->context = context_module::instance($this->wiki->cmid);

        $ga = new local_analytics_ganalytics();
        $trackurl = $ga::trackurl(TRUE, TRUE);

        $this->assertEquals("'/Miscellaneous/Test+course+1/wiki/Wiki+1'", $trackurl);
    }

    /**
     * Test that enabling Google analytics universal causes appropriate JS to be added when clean URL is disabled.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics Universal
     * AND the clean URL option is disabled
     * THEN the GA Universal Javascript should be added to the output.
     *
     * @test
     */
    public function googleAnalyticsTrackUrlForActivityInCourseIsCorrectForCoursePageWithUncleanUrl() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('analytics', 'ganalytics', 'local_analytics');
        set_config('cleanurl', FALSE, 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', array('id' => 1));

        local_analytics_execute();

        $expected = file_get_contents(__DIR__ . '/expected/google_analytics_course_page_unclean_url.html');
        $actual = $CFG->additionalhtmlheader;

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that enabling Google analytics universal causes appropriate JS to be added when clean URL is enabled.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics Universal
     * AND the clean URL option is enabled
     * THEN the GA Universal Javascript should be added to the output.
     *
     * @test
     */
    public function googleAnalyticsTrackUrlForActivityInCourseIsCorrectForCoursePageWithCleanUrl() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('analytics', 'ganalytics', 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', array('id' => 1));

        local_analytics_execute();

        $expected = file_get_contents(__DIR__ . '/expected/google_analytics_course_page.html');
        $actual = $CFG->additionalhtmlheader;

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that enabling Google analytics universal causes appropriate JS to be added for a course page.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics Universal
     * THEN the GA Universal Javascript should be added to the output.
     *
     * @test
     */
    public function googleAnalyticsUniversalTrackUrlForCourseIsCorrectForCoursePageBeingViewed() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('analytics', 'guniversal', 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', array('id' => 1));

        $ga = new local_analytics_guniversal();
        $actual = $ga::trackurl(TRUE, TRUE);

        $this->assertEquals("'/Miscellaneous/Test+course+1/View'", $actual);
    }

    /**
     * Test that tracker URL for a course page being edited is correct when using GA.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics
     * THEN the tracker URL should be /Miscellaneous/Test+course+1/Edit.
     *
     * @test
     */
    public function googleAnalyticsUniversalTrackUrlForCourseIsCorrectForCoursePageBeingEdited() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('analytics', 'guniversal', 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);
        $PAGE->set_editing(TRUE);

        $USER = $DB->get_record('user', array('id' => 1));

        $ga = new local_analytics_guniversal();
        $actual = $ga::trackurl(TRUE, TRUE);

        $this->assertEquals("'/Miscellaneous/Test+course+1/Edit'", $actual);
    }

    /**
     * Test that GA universal track URL for an activity within a course is generated correctly.
     *
     * GIVEN the local analytics plugin
     * WHEN the local_analytics_trackurl function in the piwik support is invoked
     * AND a course category name, course full name and activity name can be used
     * THEN it should return the expected URL.
     *
     * @test
     */
    public function googleAnalyticsUniversalTrackUrlForActivityInCourse() {
        global $CFG, $PAGE;

        set_config('analytics', 'guniversal', 'local_analytics');

        $PAGE = new mock_page();
        $PAGE->context = context_module::instance($this->wiki->cmid);

        $guniversal = new local_analytics_guniversal();
        $trackurl = $guniversal::trackurl(TRUE, TRUE);

        $this->assertEquals("'/Miscellaneous/Test+course+1/wiki/Wiki+1'", $trackurl);
    }

    /**
     * Test that enabling Google analytics universal causes appropriate JS to be added.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics Universal
     * THEN the GA Universal Javascript should be added to the output.
     *
     * @test
     */
    public function enabledGoogleAnalyticsUniversalModuleResultsInExpectedOutput() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('analytics', 'guniversal', 'local_analytics');
        set_config('imagetrack', FALSE, 'local_analytics');

        $USER = $DB->get_record('user', array('id' => 1));

        local_analytics_execute();

        $expected = file_get_contents(__DIR__ . '/expected/google_analytics_universal.html');
        $actual = $CFG->additionalhtmlheader;

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that enabling Google analytics universal causes appropriate JS to be added when clean URL is disabled.
     *
     * GIVEN the local analytics plugin
     * WHEN its lib.php is included
     * AND the enabled setting for the module is TRUE
     * AND the page being visited is a course page
     * AND the analytics module is set to Google Analytics Universal
     * AND the clean URL option is disabled
     * THEN the GA Universal Javascript should be added to the output.
     *
     * @test
     */
    public function enabledGoogleAnalyticsUniversalModuleResultsInExpectedOutputForCoursePageWithUncleanUrl() {
        global $PAGE, $COURSE, $USER, $DB, $CFG;

        set_config('analytics', 'guniversal', 'local_analytics');
        set_config('cleanurl', FALSE, 'local_analytics');

        $COURSE = $this->course;

        $PAGE = new mock_page();
        $PAGE->context = context_course::instance($COURSE->id);

        $USER = $DB->get_record('user', array('id' => 1));

        local_analytics_execute();

        $expected = file_get_contents(__DIR__ . '/expected/google_analytics_universal_course_unclean_url.html');
        $actual = $CFG->additionalhtmlheader;

        $this->assertEquals($expected, $actual);
    }

}
