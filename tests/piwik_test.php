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

}
