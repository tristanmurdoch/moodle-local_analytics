<?php

/**
 * @file
 * Piwik specific tests.
 *
 * @package    local_analytics
 * @category   test
 * @copyright  2016 Catalyst IT
 * @author     Nigel Cunningham
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class piwik_test extends \advanced_testcase {

    public function setUp()
    {
        global $USER;
        $this->resetAfterTest();

        // Set up the settings.
        set_config('piwikusedimensions', TRUE, 'local_analytics');

        set_config('piwik_number_dimensions_visit', '5', 'local_analytics');
        set_config('piwikdimensioncontent_visit_1', 'user_name', 'local_analytics');
        set_config('piwikdimensioncontent_visit_5', 'user_name', 'local_analytics');
        set_config('piwikdimensioncontent_visit_6', 'user_name', 'local_analytics');
        set_config('piwikdimensioncontent_visit_10', 'user_name', 'local_analytics');
        set_config('piwikdimensioncontent_visit_11', 'missing_plugin', 'local_analytics');
        set_config('piwikdimensionid_visit_1', '2468', 'local_analytics');
        set_config('piwikdimensionid_visit_5', '2468', 'local_analytics');
        set_config('piwikdimensionid_visit_6', '2468', 'local_analytics');

        set_config('piwik_number_dimensions_action', '5', 'local_analytics');
        set_config('piwikdimensioncontent_action_1', 'course_full_name', 'local_analytics');
        set_config('piwikdimensionid_action_1', '1357', 'local_analytics');

        $USER->firstname = 'Foo';
        $USER->lastname = 'Bar';
        $USER->firstnamephonetic = 'Foo';
        $USER->lastnamephonetic = 'Bar';
        $USER->middlename = '';
        $USER->alternatename = '';
    }

    /**
     * Test that a custom dimension string is formatted as expected.
     *
     * GIVEN the Piwik class
     * WHEN the local_get_custom_dimension_string function is called
     * THEN resulting string should match expectations
     *
     * @test
     */
    public function customDimensionStringFormattedAsExpected() {
        $actual = \local_analytics_piwik::local_get_custom_dimension_string(13579, 'some_context_please', 'chocolate_fish');

        $expected = '_paq.push(["setCustomDimension", customDimensionId = 13579, customDimensionValue = "chocolate_fish"]);' . "\n";
        $this->assertSame($expected, $actual);
    }

    /**
     * Test that expected dimension values are obtained.
     *
     * GIVEN the Piwik class
     * WHEN the local_get_custom_dimension_string function is called
     * THEN resulting string should match expectations
     *
     * @test
     */
    public function customDimensionValuesObtainedCorrectly() {
        $actual = \local_analytics_piwik::get_dimension_values('visit', 1);

        $expected = array(
            0 => '2468',
            1 => 'user_name',
            2 => 'Foo Bar',
        );
        $this->assertSame($expected, $actual);
    }

    /**
     * Test that setting a value but not giving it an ID results in a debugging message.
     *
     * GIVEN the Piwik class
     * WHEN the local_get_custom_dimension_string function is called
     * AND a value is chosen but no ID is set
     * THEN a debug message should be set
     * AND NULL should be returned.
     *
     * @test won't work. Requires testFnName due to assertDebuggingCalled
     */
    public function testCustomDimension() {
        $actual = \local_analytics_piwik::get_dimension_values('visit', 10);

        $this->assertDebuggingCalled("Local Analytics Piwik dimension action plugin #10 has been chosen but no
                        ID has been supplied.");
        $this->assertNull($actual);
    }

    /**
     * Test that setting a value but then removing the plugin results in an error message.
     *
     * GIVEN the Piwik class
     * WHEN the local_get_custom_dimension_string function is called
     * AND a value is chosen but the plugin can't be instantiated
     * THEN a debug message should be set
     * AND NULL should be returned.
     *
     * @test won't work. Requires testFnName due to assertDebuggingCalled
     */
    public function testCustomDimensionHandlesMissingPluginWithDebugMessage() {
        $actual = \local_analytics_piwik::get_dimension_values('visit', 11);

        $this->assertDebuggingCalled("Local Analytics Piwik Dimension Plugin 'missing_plugin' is missing.");
        $this->assertNull($actual);
    }

    /**
     * Test that unset value is handled with no debug message and null return.
     *
     * GIVEN the Piwik class
     * WHEN the local_get_custom_dimension_string function is called
     * AND no value is chosen
     * THEN no debug message should be set
     * AND NULL should be returned.
     *
     * @test won't work. Requires testFnName due to assertDebuggingNotCalled
     */
    public function testCustomDimensionHandlesNoValueSetAsExpected() {
        $actual = \local_analytics_piwik::get_dimension_values('visit', 4);

        $this->assertDebuggingNotCalled();
        $this->assertNull($actual);
    }

    /**
     * Test that dimensions_for_scope honours the number of dimensions setting.
     *
     * GIVEN the Piwik class
     * WHEN the dimensions_for_scope function is called
     * AND the number of dimensions for the visit scope has been set to 5
     * THEN a configured fifth dimension should be used
     * AND a configured sixth dimension should be ignored.
     *
     * @test
     */
    public function dimensionsForScopeHonoursNumberOfDimensionsSetting() {
        $actual = \local_analytics_piwik::dimensions_for_scope('visit');

        $expected = array(
            0 =>
                array(
                    'id' => '2468',
                    'dimension' => 'user_name',
                    'value' => 'Foo Bar',
                ),
            1 =>
                array(
                    'id' => '2468',
                    'dimension' => 'user_name',
                    'value' => 'Foo Bar',
                ),
        );
        $this->assertSame($expected, $actual);
    }

    /**
     * Test rendering action scope dimensions.
     *
     * (The duplicates above get squashed, so only one line of output).
     *
     * GIVEN the Piwik class
     * WHEN the render_dimensions_for_action_scope function is called
     * THEN the output should be as expected.
     *
     * @test
     */
    public function outputOfRenderDimensionsForActionScopeAsExpected() {
        $vars = \local_analytics_piwik::dimensions_for_scope('action');
        $actual = \local_analytics_piwik::render_dimensions_for_action_scope($vars);

        $expected = '_paq.push(["setCustomDimension", customDimensionId = 1357, customDimensionValue = "PHPUnit test site"]);
';
        $this->assertSame($expected, $actual);
    }

    /**
     * Test rendering visit scope dimensions.
     *
     * GIVEN the Piwik class
     * WHEN the render_dimensions_for_visit_scope function is called
     * THEN the output should be as expected.
     *
     * @test
     */
    public function outputOfRenderDimensionsForVisitScopeAsExpected() {
        $vars = \local_analytics_piwik::dimensions_for_scope('visit');
        $actual = \local_analytics_piwik::render_dimensions_for_visit_scope($vars);

        $expected = '_paq.push(["trackPageView","",{"dimension2468":"Foo Bar"}]);
';
        $this->assertSame($expected, $actual);
    }

    /**
     * Test getting all custom variables for a page.
     *
     * GIVEN the Piwik class
     * WHEN the insert_custom_moodle_dimensions function is called
     * THEN the output should be as expected.
     *
     * @test
     */
    public function outputOfInsertCustomMoodleDimensionsWorksAsExpected() {
        $actual = \local_analytics_piwik::insert_custom_moodle_dimensions();
        $expected = '_paq.push(["setCustomDimension", customDimensionId = 1357, customDimensionValue = "PHPUnit test site"]);
_paq.push(["trackPageView","",{"dimension2468":"Foo Bar"}]);
';
        $this->assertSame($expected, $actual);
    }

    /**
     * Test piwikusedimensions setting is honoured.
     *
     * GIVEN the Piwik class
     * WHEN the local_insert_custom_moodle_vars function is called
     * AND the piwikusedimensions configuration value is FALSE
     * AND the custom variables are unconfigured
     * THEN the output should use custom variables.
     *
     * @test
     */
    public function localInsertCustomMoodleVarsHonoursPiwikusedimensions() {

        // First with dimensions enabled.
        $actual = \local_analytics_piwik::local_insert_custom_moodle_vars();
        $expected = '_paq.push(["setCustomDimension", customDimensionId = 1357, customDimensionValue = "PHPUnit test site"]);
_paq.push(["trackPageView","",{"dimension2468":"Foo Bar"}]);
';
        $this->assertSame($expected, $actual);

        // Then with them disabled.
        set_config('piwikusedimensions', FALSE, 'local_analytics');

        $actual = \local_analytics_piwik::local_insert_custom_moodle_vars();
        $expected = '_paq.push(["setCustomVariable", 1, "UserName", "Foo Bar", "page"]);
_paq.push(["setCustomVariable", 2, "UserRole", "", "page"]);
_paq.push(["setCustomVariable", 3, "Context", "Front page", "page"]);
_paq.push(["setCustomVariable", 4, "CourseName", "PHPUnit test site", "page"]);
';
        $this->assertSame($expected, $actual);
    }

}
