<?php
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

// Configure the template system by telling it where to find templates, and
// where to put the compiled templates.
$tc = ezcTemplateConfiguration::getInstance();
$tc->templatePath = dirname( __FILE__ ) . '/templates';
$tc->compilePath = dirname( __FILE__ ) . '/cache';
$tc->context = new ezcTemplateNoContext();

ezcDbInstance::set(
    ezcDbFactory::create(
        'mysql://mvctools:Yqtl6ngFuxOBoG3g0oGa@localhost/mvctools'
    )
);
ezcDbInstance::set(
    ezcDbFactory::create(
        'mysql://mvctools:Yqtl6ngFuxOBoG3g0oGa@localhost/redmine'
    ),
    'users'
);

$installedApps = array( 
    'admin',
    'sites',
    'dev',
    'pages',
);

$managers = array(  );
foreach( $installedApps as $app )
{
    $testPath = join( DIRECTORY_SEPARATOR, array( 
        $appsPath,
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

ezcPersistentSessionInstance::set( 
    new ezcPersistentSession( 
        ezcDbInstance::get(),
        new ezcPersistentMultiManager( 
            $managers
        )
    )
);

class aiiRouter extends ezcMvcRouter
{
    // @todo: decouple this into ezcMvcRoutesArray
    public function importRouter( &$routes, $prefix, $router )
    {
        if ( is_string( $router ) ) 
        {
            $router = new $router( $this->request );
        }
        
        if ( ! $router instanceof ezcMvcRouter )
        {
            trigger_error( "$router is not a router" );
        }

        foreach( self::prefix( $prefix, $router->createRoutes(  ) ) as $route )
        {
            $routes[] = $route;
        }
    }

    public function createRoutes(  )
    {
        $routes = array(  );
        
        // not the right way but will do for now
        $this->importRouter( $routes, '/admin/', 'aiiAdminRouter' );
        $this->importRouter( $routes, '/cms/', 'aiiPagesRouter' );

        return $routes;
    }
}

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

foreach( $installedApps as $app )
{
    aiiTemplateLocation::$paths[] = $appsPath . DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR . 'templates';
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
        return new aiiRouter( $request );
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
