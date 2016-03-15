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
        $this->resetAfterTest();

        // Prime the plugin cache with our mock plugin.
        \local_analytics\dimensions::instantiate_plugin(__DIR__ . '/testdata/mock_user_name.php', '\local\analytics\dimensions\mock_user_name');

        // Set up the settings.
        set_config('piwikdimensioncontent_visit_1', 'mock_user_name', 'local_analytics');
        set_config('piwikdimensioncontent_visit_2', 'mock_user_name', 'local_analytics');
        set_config('piwikdimensioncontent_visit_3', 'missing_plugin', 'local_analytics');
        set_config('piwikdimensionid_visit_1', '2468', 'local_analytics');
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
            1 => 'mock_user_name',
            2 => 'This is not a _real_ user name!',
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
        $actual = \local_analytics_piwik::get_dimension_values('visit', 2);

        $this->assertDebuggingCalled("Local Analytics Piwik dimension action plugin #2 has been chosen but no
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
        $actual = \local_analytics_piwik::get_dimension_values('visit', 3);

        $this->assertDebuggingCalled("Local Analytics Piwik Dimension Plugin 'missing_plugin' is missing.");
        $this->assertNull($actual);
    }

}
