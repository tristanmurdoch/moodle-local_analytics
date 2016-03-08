<?php
/**
 * @file
 * Interface to enumerate and use dimension classes
 */

namespace local_analytics;

class dimensions {
	/**
	 * The array of class instances.
	 */
	static private $dimension_instances;

	/**
	 * Find class instances and populate the array
	 *
	 * @return bool
	 *   Whether an error was encountered in enumerating plugins.
	 */
	static public function enumerate_plugins()
	{
		$dir = dirname(__FILE__) . '/dimensions';

		self::$dimension_instances = array();

		$list_of_files = scandir($dir);
		foreach($list_of_files as $index => $entry) {
			if ($entry == '.' || $entry == '..' || substr($entry, -4) != '.php') {
				unset($list_of_files[$index]);
				continue;
			}

			require_once(__DIR__ . '/dimensions/' . $entry);

			$class_name = substr($entry, 0, -4);

			// Check the expected class exists.
			if (!class_exists($class_name, FALSE)) {
				debugging("Local Analytics: File ${entry} in the dimensions directory doesn't define a class named ${class_name}",
					DEBUG_DEVELOPER);
				continue;
			}

			self::$dimension_instances[$class_name] = new $class_name;
		}
	}

}