Dimensions Plugin Readme.

Each dimension plugin should implement the dimension interface, defined in dimension_interface.php. This means each
plugin should have static variables and functions as listed below.

$name: The machine readable name of the plugin. (No spaces!)

$scope: The scope in which the plugin is used ('action' or 'visit'). Visit is for variables that generally don't
change over the course of a secssion. Action is for variables that relate to the particular request.

$value: The value function should return the content to be sent to Piwik.
