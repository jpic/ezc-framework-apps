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

set_include_path( join( PATH_SEPARATOR, array( 
    get_include_path(  ),
    EZC_TRUNK_PATH,
    APPS_TRUNK_PATH,
) ) );

require 'Base/src/base.php';

function __autoload( $className )
{
    if ( $className == 'PEAR_Error' )
    {
        include 'PEAR.php';
        return;
    }

	if ( !ezcBase::autoload( $className ) )
    {
        include str_replace( '_', '/', $className ) . '.php';
    }
}

include dirname( __FILE__ ) . '/apps/dev/autoload_config.php';
// }}}

ezcDbInstance::set(
    ezcDbFactory::create(
        "mysql://site:3yziLEJfSwUwEz4FYvEzhxUCwNe7ak@localhost/site"
    )
);

include 'framework.php';

$project = aiiProjectConfiguration::instance(  );
$project->path = dirname( __FILE__ );

$installedApps = array( 
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
