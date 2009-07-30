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

class aiiFramework {
    /**
     * List of installed app names.
     *
     * @var array
     */
    public $installedApps = array(  );

    /**
     * Absolute path to the apps.
     *
     * The real framework should allow multiple paths.
     * 
     * @var string
     */
    public $appsPath = '';

    /**
     * Absolute path to templates that overload app templates.
     *
     * The real framework should allow multiple paths.
     * 
     * @var string
     */
    public $templatePath = '';

    public $cachePath = '';

    static public $instance = null;
    public static function instance(  ) {
        if ( is_null( self::$instance ) ) {
            self::$instance = new aiiFramework(  );
        }
        return self::$instance;
    }
}

class aiiTemplateLocation implements ezcTemplateLocation
{
    static public $paths = array();
    public $templateName = '';
    public $requestHost = '';
    public function __construct( $templateName, $requestHost = '' )
    {
        $this->templateName = $templateName;
        $this->requestHost = $requestHost;
    }

    /**
     * First check in $tc->templatePath/$request->host.
     * Then, check in $tc->templatePath/
     * Then check in self::$paths filled with apps template paths.
     * 
     * @return void
     */
    public function getPath()
    {
        $tested = array();
        $tc = ezcTemplateConfiguration::getInstance(  );
        $userPath = $tc->templatePath;

        // check $tc->templatePath/$request->host.
        if ( $this->requestHost )
        {
            $testPath = join( DIRECTORY_SEPARATOR, array( 
                $userPath,
                $this->requestHost,
                $this->templateName,
            ) );

            if ( file_exists( $testPath ) )
            {
                return $testPath;
            }

            $tested[] = $testPath;
        }

        // check $tc->templatePath/
        $testPath = $userPath . DIRECTORY_SEPARATOR . $this->templateName;
        if ( file_exists( $testPath ) )
        {
            return $testPath;
        }

        $tested[] = $testPath;

        // check self::$paths
        foreach( self::$paths as $path )
        {
            $testPath = $path . DIRECTORY_SEPARATOR . $this->templateName;

            if ( file_exists( $testPath ) )
            {
                return $testPath;
            }

            $tested[] = $testPath;
        }

        print_r( $tested );
        throw new Exception( 'Could not locate template' );
    }
}

class aiiFrameworkTemplateInitializer implements ezcBaseConfigurationInitializer {
    static public function configureObject( $cfg ) {
        $framework = aiiFramework::instance(  );
        $cfg->templatePath = $framework->templatePath;
        $cfg->compilePath = join( DIRECTORY_SEPARATOR, array( 
            $framework->cachePath,
            'compiled_templates'
        ) );
        $cfg->context = new ezcTemplateNoContext();

        foreach( $framework->installedApps as $app ) {
            aiiTemplateLocation::$paths[] = join( DIRECTORY_SEPARATOR, array( 
                $framework->appsPath,
                $app,
                'templates',
            ) );
        }

        // is it the right place to load apps template custom blocks and functions?
    }
}

class aiiFrameworkPersistentObjectInitializer implements ezcBaseConfigurationInitializer {
    public static function configureObject( $instance ) {
        $framework = aiiFramework::instance(  );
        $managers = array(  );

        foreach( $framework->installedApps as $app )
        {
            $testPath = join( DIRECTORY_SEPARATOR, array( 
                $framework->appsPath,
                $app,
                'pod',
            ) );
            
            if ( is_dir( $testPath ) )
            {
                $managers[] = new ezcPersistentCacheManager( 
                    new ezcPersistentCodeManager( 
                        $testPath
                    )
                );
            }
        }
        
        $session = new ezcPersistentSession( 
            ezcDbInstance::get(),
            new ezcPersistentMultiManager( 
                $managers
            )
        );
    
        return $session;
    }
}

class aiiFrameworkRouter extends ezcMvcRouter {
    public function createRoutes(  ) {
        $framework = aiiFramework::instance(  );
        $routes = array(  );

        foreach( $framework->installedApps as $app ) {
            $routerFile = join( DIRECTORY_SEPARATOR, array( 
                $framework->appsPath,
                $app,
                'router.php',
            ) );

            // something ugly
            // we really don't want to have to make application
            // configuration files and such  ... standards should be argeed
            if ( file_exists( $routerFile ) ) {
                $f = file_get_contents( $routerFile );
                preg_match_all(  '/class ([\w]+) extends [\w]*Router/', $f, $m );
                $routerClass = $m[1][0];

                $router = new $routerClass( $this->request );
                foreach( self::prefix( sprintf( '/%s/', $app ), $router->createRoutes(  ) ) as $route ) {
                    $routes[] = $route;
                }
            }
        }

        return $routes;
    }
}


$framework = aiiFramework::instance(  );

$framework->appsPath = realpath( join( DIRECTORY_SEPARATOR, array( 
    dirname( __FILE__ ),
    'apps',
) ) );

$framework->templatePath = realpath( join( DIRECTORY_SEPARATOR, array( 
    dirname( __FILE__ ),
    'templates',
) ) );

$framework->cachePath = realpath( join( DIRECTORY_SEPARATOR, array( 
    dirname( __FILE__ ),
    'cache',
) ) );

$framework->installedApps = array( 
    'admin',
    'sites',
    'dev',
    'pages',
);

ezcBaseInit::setCallback( 
    'ezcInitPersistentSessionInstance',
    'aiiFrameworkPersistentObjectInitializer'
);

ezcBaseInit::setCallback( 
    'ezcInitTemplateConfiguration',
    'aiiFrameworkTemplateInitializer'
);

class aiiView extends ezcMvcView
{
    public function createZones( $layout )
    {
        if ( $this->result instanceof aiiAdminResult )
        {
            $view = new aiiAdminView( $this->request, $this->result );
            $views = $view->createZones( false );
        }
        elseif ( $this->result instanceof aiiPagesResult )
        {
            $view = new aiiPagesView( $this->request, $this->result );
            $views = $view->createZones( false );
        }

        if ( $layout )
        {
            $views[] = new ezcMvcTemplateViewHandler( 
                'layout',
                new aiiTemplateLocation( 'layout.ezt', $this->request->host )
            );
        }

        return $views;
    }
}


class aiiMvcConfiguration implements ezcMvcDispatcherConfiguration
{
    public function createRequestParser()
    {
        $parser = new ezcMvcHttpRequestParser;
        $parser->prefix = preg_replace( '@/index\.php$@', '', $_SERVER['SCRIPT_NAME'] );
        return $parser;
    }

    public function createRouter( ezcMvcRequest $request )
    {
        return new aiiFrameworkRouter( $request );
    }

    public function createView( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result )
    {
        return new aiiView( $request, $result );
    }

    public function createResponseWriter( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result, ezcMvcResponse $response )
    {
        return new ezcMvcHttpResponseWriter( $response );
    }

    public function createFatalRedirectRequest( ezcMvcRequest $request, ezcMvcResult $result, Exception $response )
    {
        throw $response;
        var_Dump( $request );
        $req = clone $request;
        $req->uri = '/FATAL';

        return $req;
    }

    public function runPreRoutingFilters( ezcMvcRequest $request )
    {
        $sitesFetch = new aiiSitesFetchRequestFilter();
        $request->variables['site'] = $sitesFetch->runPreRoutingFilters( $request );
    }

    public function runRequestFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request )
    {
    }

    public function runResultFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result )
    {
        $result->variables['installRoot'] = preg_replace( '@/index\.php$@', '', $_SERVER['SCRIPT_NAME'] );
    }

    public function runResponseFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result, ezcMvcResponse $response )
    {
    }
}
?>
