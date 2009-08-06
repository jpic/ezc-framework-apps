<?php
// Include the configuration file
include '../config.php';

// Instantiate the dispatcher configuration object.
$project = aiiProjectConfiguration::instance();

// Send the configuration to the dispatcher, and run it.
$dispatcher = new ezcMvcConfigurableDispatcher( $project );
$dispatcher->run();
?>
