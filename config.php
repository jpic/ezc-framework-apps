<?php
// {{{ class autoload setup
define( 'EZC_TRUNK_PATH',
    join( DIRECTORY_SEPARATOR, array(
        dirname( __FILE__ ),
        'ezc',
        'trunk',
) ) );

define( 'APPS_TRUNK_PATH',
    join( DIRECTORY_SEPARATOR, array(
        dirname( __FILE__ ),
        'apps',
) ) );

define( 'DOCTRINE_PATH',
    join( DIRECTORY_SEPARATOR, array(
        dirname( __FILE__ ),
        'doctrine',
) ) );

set_include_path( join( PATH_SEPARATOR, array( 
    get_include_path(  ),
    EZC_TRUNK_PATH,
    APPS_TRUNK_PATH,
) ) );

require join( DIRECTORY_SEPARATOR, array( 
    DOCTRINE_PATH,
    'lib',
    'Doctrine.php'
) );
spl_autoload_register( array( 'Doctrine', 'autoload'));

require 'ezc/Base/src/base.php';
ezcBase::setRunMode( ezcBase::MODE_DEVELOPMENT );
spl_autoload_register( array( 'ezcBase', 'autoload'));

include dirname( __FILE__ ) . '/apps/dev/autoload_config.php';
// }}}

Doctrine_Manager::connection( 
    "mysql://fcgid:...@localhost/shared",
    'main'
);

include 'framework_part0.php';

$project = aiiProjectConfiguration::instance( dirname( __FILE__ ), 'ocpsys' );

$installedApps = array( 
    'core',
    'admin',
    'sites',
    'dev',
    'pages',
);

$appsPaths = array(  );

foreach( $installedApps as $appName ) {
    // i know my apps are all in the same dir
    $appsPaths[] = join( DIRECTORY_SEPARATOR, array( 
        APPS_TRUNK_PATH,
        $appName,
    ) );
}

$project->setUp( $appsPaths );

unset( $installedApps );
unset( $appsPaths );
unset( $appName );

?>
