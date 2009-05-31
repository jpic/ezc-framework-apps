<?php
// Include the configuration file
include '../config.php';

// Instantiate the dispatcher configuration object.
$config = new aiiMvcConfiguration();

// Send the configuration to the dispatcher, and run it.
$dispatcher = new ezcMvcConfigurableDispatcher( $config );
$dispatcher->run();
?>
