<?php
/**
 * @file
 * Interface to enumerate and use dimension classes
 */

namespace local_analytics;

class dimensions
{
    /**
     * The array of class instances.
     */
    static private $dimension_instances = null;

    /**
     * Find class instances and populate the array
     *
     * @return array of strings
     *   A list of the names of files containing plugins.
     */
    static public function enumerate_plugins() {
        $dir = dirname(__FILE__) . '/dimensions';

        $list_of_files = scandir($dir);
        foreach ($list_of_files as $index => $entry) {
            if ($entry == '.' || $entry == '..' || substr($entry, -4) != '.php') {
                unset($list_of_files[$index]);
            }
        }

        return $list_of_files;
    }

    /**
     * Instantiate a single plugin and return the instance.
     *
     * @param string $file
     *   The name of the file to require.
     * @param string $class_name
     *   The name of the class that should be defined by the file.
     *
     * @return
     *   The class instance or NULL if an error occurred.
     */
    static public function instantiate_plugin($file, $class_name) {
        require_once($file);

        // Check the expected class exists.
        if (!class_exists($class_name, false)) {
            debugging("Local Analytics: File ${file} doesn't define a class named ${class_name}",
                DEBUG_DEVELOPER);
            return NULL;
        }

        $instance = new $class_name;
        return $instance;
    }

    /**
     * Instantiate plugins and populate the array.
     *
     * @return array
     *   An array keyed by plugin scope and class, with values being class instances.
     */
    static public function instantiate_plugins() {
        if (is_null(self::$dimension_instances)) {
            $list_of_files = self::enumerate_plugins();

            $plugins = array();

            foreach ($list_of_files as $index => $entry) {
                $filename = __DIR__ . '/dimensions/' . $entry;
                $class_name = '\local\analytics\dimensions\\' . substr($entry, 0, -4);

                $instance = self::instantiate_plugin($filename, $class_name);

                $scope = $instance::$scope;
                if (!array_key_exists($scope, $plugins)) {
                    $plugins[$scope] = array();
                }

                if (!is_null($instance)) {
                    $plugins[$scope][$class_name] = $instance;
                }
            }

            self::$dimension_instances = $plugins;
        }

        return self::$dimension_instances;
    }

    /**
     * Get plugin options list.
     *
     * @param string $scope_requested
     *   The scope by which to filter results.
     *
     * @return array
     *   An array of items for a select combo.
     */
    static public function setting_options($scope_requested) {
        static $result = null;

        if (is_null($result)) {
            $plugins = self::instantiate_plugins();

            $result = array();

            foreach ($plugins as $scope => $scope_plugins) {
                // Nothing is selected entry.
                $result[$scope][''] = '';

                foreach ($scope_plugins as $file => $plugin) {
                    $lang_string = get_string($plugin::$name, 'local_analytics');
                    $result[$scope][$plugin::$name] = $lang_string;
                }
            }
        }

        return $result[$scope_requested];

    }

}
