<?php
$appsPath = realpath(dirname(__FILE__) . '/..');
set_include_path(get_include_path() . PATH_SEPARATOR . $appsPath);
ezcBase::addClassRepository( $appsPath );
