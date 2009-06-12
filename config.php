<?php
// Set the include path to the eZ Components location, and bootstrap the
// library. The two lines below assume that you're using eZ Components from
// SVN -- see the installation guide at http://ezcomponents.org/docs/install.
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
    $testPath = $appsPath .
        DIRECTORY_SEPARATOR .
        $app .
        DIRECTORY_SEPARATOR .
        'pod';

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
    public function createRoutes(  )
    {
        $pagesRouter = new aiiPagesRouter( $this->request );

        $routes = self::prefix( '/cms/', $pagesRouter->createRoutes(  ) );

        $adminRouter = new aiiAdminRouter( $this->request );
        foreach( self::prefix( '/admin/', $adminRouter->createRoutes(  ) ) as $route )
        {
            $routes[] = $route;
        }

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
            return $view->createZones( $layout );
        }
        elseif ( $this->result instanceof aiiPagesResult )
        {
            $view = new aiiPagesView( $this->request, $this->result );
            return $view->createZones( $layout );
        }
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
        $tc = ezcTemplateConfiguration::getInstance(  );
        $userPath = $tc->templatePath;

        // check $tc->templatePath/$request->host.
        if ( $this->requestHost )
        {
            $testPath = $userPath . DIRECTORY_SEPARATOR . $this->templateName;

            if ( file_exists( $testPath ) )
            {
                return $testPath;
            }          
        }

        // check $tc->templatePath/
        $testPath = $userPath . DIRECTORY_SEPARATOR . $this->templateName;
        if ( file_exists( $testPath ) )
        {
            return $testPath;
        }

        // check self::$paths
        foreach( self::$paths as $path )
        {
            $testPath = $path . DIRECTORY_SEPARATOR . $this->templateName;

            if ( file_exists( $testPath ) )
            {
                return $testPath;
            }
        }
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
