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
 * Tests for dimensions plugin values.
 *
 * @package    local_analytics
 * @category   test
 * @copyright  2016 Catalyst IT
 * @author     Nigel Cunningham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_analytics;

use local\analytics\dimensions\dimension_interface;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once(__DIR__ . '/../dimensions.php');
require_once($CFG->libdir . '/coursecatlib.php');


/**
 * Class local_analytics_dimensions_testcase
 */
class local_analytics_dimensions_values_testcase extends \advanced_testcase
{

    /**
     * Setup test data.
     */
    public function setUp()
    {
        global $COURSE, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();

        \local_analytics\dimensions::instantiate_plugins();

        $COURSE->fullname = "I'm a course";
        $COURSE->idnumber = "9642";
        $COURSE->shortname = "Hat on cat";

        $USER->email = 'noone@nowhere.null';
        $USER->firstname = 'Kevin';
        $USER->lastname = '11';
        $USER->department = 'Null void';
        $USER->institution = 'Plumber Jail';
        $USER->profile['facultycostcode'] = 'Foo';
    }

    /**
     * Test that the context plugin returns the correct name for an associated context.
     *
     * GIVEN the context dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be the context name associated with the current $COURSE.
     *
     * @test
     */
    public function contextPluginReturnsCourseContextName()
    {
        $instance = new \local\analytics\dimensions\context();
        $actual = $instance->value();

        $expected = "Front page";
        $this->assertEquals($expected, $actual);

    }

    /**
     * Test that the course category hierarchy plugin returns the full path of the course.
     *
     * GIVEN the course category dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be the full hierachy associated with the current $COURSE.
     *
     * @test
     */
    public function courseHierachyPluginReturnsCourseCategoryPath()
    {
        global $COURSE;

        $instance = new \local\analytics\dimensions\course_category_hierarchy_full_path();
        $actual = $instance->value();

        // Front page has no parents so result is False.
        $this->assertFalse($actual);

        // Create a set of nested categories.
        $category1 = \coursecat::create(array('name' => 'Top'));
        $category2 = \coursecat::create(array('name' => 'Middle', 'parent' => $category1->id));
        $category3 = \coursecat::create(array('name' => 'Bottom', 'parent' => $category2->id));

        $COURSE = $this->getDataGenerator()->create_course(array('category' => $category3->id));

        $actual = $instance->value();

        $expected = "Top\Middle\Bottom";
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that the course enrolment method plugin returns the enrolment method of the user in the course.
     *
     * GIVEN the course enrolment method dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be the enrolment method providing access to the user to the current $COURSE.
     *
     * @test
     */
    public function courseEnrolmentMethodPluginReturnsEnrolmentMethod()
    {

    }

    /**
     * Test that the course full name plugin returns the full name of the course.
     *
     * GIVEN the course full name dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be the full name of the current $COURSE.
     *
     * @test
     */
    public function courseFullNamePluginReturnsCourseFullName()
    {
        $instance = new \local\analytics\dimensions\course_full_name();
        $actual = $instance->value();

        $expected = "I'm a course";
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that the course ID number plugin returns the ID number of the course.
     *
     * GIVEN the course ID number dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be the ID number of the current $COURSE.
     *
     * @test
     */
    public function courseIdNumberPluginReturnsCourseIdNumber()
    {
        $instance = new \local\analytics\dimensions\course_id_number();
        $actual = $instance->value();

        $expected = "9642";
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that the course short name plugin returns the short name of the course.
     *
     * GIVEN the course short name dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be the short name of the current $COURSE.
     *
     * @test
     */
    public function courseShortNamePluginReturnsCourseShortName()
    {
        $instance = new \local\analytics\dimensions\course_short_name();
        $actual = $instance->value();

        $expected = "Hat on cat";
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that the is_on_bundoora_campus plugin returns whether an IP address matches the ranges given.
     *
     * GIVEN the is_on_bundoora_campus dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be a boolean indicating whether the current IP address is within IP ranges set.
     *
     * @test
     */
    public function isOnBundooraCampusPluginReturnsWhetherAtBundoora()
    {
        global $CFG, $_SERVER;

        $instance = new \local\analytics\dimensions\is_on_bundoora_campus();

        unset($CFG->bundoora_campus_ips);
        $actual = $instance->value();
        $this->assertFalse($actual);

        $CFG->bundoora_campus_ips = "1.2.3.0/24";
        $_SERVER['HTTP_CLIENT_IP'] = '1.2.3.4';

        $actual = $instance->value();
        $this->assertTrue($actual);

        $_SERVER['HTTP_CLIENT_IP'] = '1.2.5.4';

        $actual = $instance->value();
        $this->assertFalse($actual);
    }

    /**
     * Test that the is_on_campus plugin returns whether an IP address matches the ranges given.
     *
     * GIVEN the is_on_campus dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be a boolean indicating whether the current IP address is within IP ranges set.
     *
     * @test
     */
    public function isOnCampusPluginReturnsWhetherAtACampus()
    {
        global $CFG, $_SERVER;

        $instance = new \local\analytics\dimensions\is_on_campus();

        unset($CFG->on_campus_ips);
        $actual = $instance->value();
        $this->assertFalse($actual);

        $CFG->on_campus_ips = "1.2.3.0/24, 1.2.5.0/24, 192.168.0.0/16, 10.0.0.0/8";
        $_SERVER['HTTP_CLIENT_IP'] = '1.2.3.4';

        $actual = $instance->value();
        $this->assertTrue($actual);

        $_SERVER['HTTP_CLIENT_IP'] = '1.2.5.4';

        $actual = $instance->value();
        $this->assertTrue($actual);

        $_SERVER['HTTP_CLIENT_IP'] = '10.0.2.153';

        $actual = $instance->value();
        $this->assertTrue($actual);

        $_SERVER['HTTP_CLIENT_IP'] = '17.54.23.253';

        $actual = $instance->value();
        $this->assertFalse($actual);
    }

    /**
     * Test that the user department plugin returns the Department field of the user profile.
     *
     * GIVEN the user department dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be the user's Department profile field.
     *
     * @test
     */
    public function userDepartmentPluginReturnsUserDepartment()
    {
        global $USER;

        $instance = new \local\analytics\dimensions\user_department();
        $actual = $instance->value();

        $expected = "Null void";
        $this->assertEquals($expected, $actual);

        // Test guest user (fields often unset).
        $this->setGuestUser();
        unset($USER->department);
        $actual = $instance->value();
        $this->assertFalse($actual);
    }

    /**
     * Test that the user email domain plugin returns the domain portion of the user email address.
     *
     * GIVEN the user email domain dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be the domain portion of the user's email address.
     *
     * @test
     */
    public function userEmailDomainPluginReturnsUserEmailDomain()
    {
        global $USER;

        $instance = new \local\analytics\dimensions\user_email_domain();
        $actual = $instance->value();

        $expected = "nowhere.null";
        $this->assertEquals($expected, $actual);

        // Test guest user (fields often unset).
        $this->setGuestUser();
        unset($USER->email);
        $actual = $instance->value();
        $this->assertFalse($actual);
    }

    /**
     * Test that the user institution plugin returns the institution field of the user profile.
     *
     * GIVEN the user institution dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be the user's institution profile field.
     *
     * @test
     */
    public function userInsitutionPluginReturnsUserInsitution()
    {
        global $USER;

        $instance = new \local\analytics\dimensions\user_institution();
        $actual = $instance->value();

        $expected = "Plumber Jail";
        $this->assertEquals($expected, $actual);

        // Test guest user (fields often unset).
        $this->setGuestUser();
        unset($USER->institution);
        $actual = $instance->value();
        $this->assertFalse($actual);
    }

    /**
     * Test that the user name plugin returns the user's name.
     *
     * GIVEN the user name dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be the user's name.
     *
     * @test
     */
    public function userNamePluginReturnsUserName()
    {
        global $USER;

        $instance = new \local\analytics\dimensions\user_name();
        $actual = $instance->value();

        $expected = "Kevin 11";
        $this->assertEquals($expected, $actual);

        // Test not fooled by masquerading.
        $user = $this->getDataGenerator()->create_user();
        $_SESSION['extra'] = true;

        // Try admin loginas this user in system context.
        $this->assertObjectNotHasAttribute('realuser', $USER);
        \core\session\manager::loginas($user->id, \context_system::instance());

        // Should return admin user details.
        set_config('local_analytics', TRUE, 'masquerade_handling');
        $actual = $instance->value();
        $this->assertEquals($expected, $actual);

        // Shouldn't return admin user details.
        set_config('masquerade_handling', FALSE, 'local_analytics', FALSE);
        $actual = $instance->value();
        $this->assertNotEquals($expected, $actual);
    }

    /**
     * Test that the user profile field faculty cost code plugin returns the faculty cost code custom field of the user profile.
     *
     * GIVEN the user profile field faculty cost code dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be the user's faculty cost code profile field.
     *
     * @test
     */
    public function userFacultyCostCodePluginReturnsUserFacultyCostCode()
    {
        global $USER;

        $instance = new \local\analytics\dimensions\user_profile_field_faculty_cost_code();
        $actual = $instance->value();

        $expected = "Foo";
        $this->assertEquals($expected, $actual);

        unset($USER->profile['facultycostcode']);
        $actual = $instance->value();
        $this->assertFalse($actual);
    }

    /**
     * Test that the user role plugin returns the user's current role.
     *
     * GIVEN the user role dimension plugin
     * WHEN its value function is invoked
     * THEN the result should be the user's current role.
     *
     * @test
     */
    public function userRolePluginReturnsUserRole()
    {
        $instance = new \local\analytics\dimensions\user_role();
        $actual = $instance->value();

        $expected = "Admin";
        $this->assertEquals($expected, $actual);

        // Test guest user (fields often unset).
        $this->setGuestUser();
        $actual = $instance->value();
        $expected = "Guest";
        $this->assertEquals($expected, $actual);
    }

}
