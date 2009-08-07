<?php
/**
 * Encapsulates conventions for a fully eZ Components powered reusable
 * application.
 */
class aiiAppConfiguration implements ezcMvcDispatcherConfiguration { // {{{
    public $namespace = '';
    public $path = '';
    protected $configuration = array( );

    public function __construct( $path , $namespace ) {
        $this->path = $path;
        $this->namespace = $namespace;

        $this->configuration['PersistentObject'] = array( 
            'definitionsPath' => 'pod',
            'definitionsManager' => null,
        );

        $this->configuration['MvcTools'] = array( 
            'layoutTemplateName' => 'layout.ezt',
            'layoutZoneName' => 'layout',
        );

        $this->configuration['Template'] = array(
            'sourcePath' => 'templates',
            'compilePath' => 'cache/compiled_templates',
            'context' => new ezcTemplateNoContext(  ),
            'extensionClasses' => array(  ),
        );

        $extensionsDir = join( DIRECTORY_SEPARATOR, array( 
            $this->path,
            'template_extensions',
        ) );

        if ( is_dir( $extensionsDir ) ) {
            $extensionsFiles = join( DIRECTORY_SEPARATOR, array( 
                $extensionsDir,
                '*',
            ) );

            foreach( glob( $extensionsFiles ) as $file ) {
                $f = file_get_contents( $file );
                preg_match_all(  '/class ([\w]+) implements [\w]*Custom(Function|Block)/', $f, $m );
                $class = $m[1][0];

                $this->configuration['Template']['extensionClasses'][] = $class;
            }
        }
    }

    public function getComponentConfig( $componentName, $variableName ) {
        if ( !array_key_exists( $componentName, $this->configuration ) ) {
            trigger_error( "$componentName not in this configuration" );
        }

        if ( !is_array( $this->configuration[$componentName] ) ) {
            trigger_error( "$componentName configuration is not an array" );
        }

        if ( !array_key_exists( $variableName, $this->configuration[$componentName] ) ) {
            trigger_error( "$variableName not in this $componentName configuration" );
        }

        return $this->configuration[$componentName][$variableName];
    }

    /**
     * This app configuration factory expects "router.php" to be at the app
     * root path and only contain one class definition which has method
     * "createRoutes".
     */
    public function createRouter( ezcMvcRequest $request ) {
        $routerFile = join( DIRECTORY_SEPARATOR, array( 
            $this->path,
            'router.php',
        ) );

        // maybe try to get it from appName_createRouter function
        // here to allow the app to instanciate its own router
        // here

        if ( file_exists( $routerFile ) ) {
            $f = file_get_contents( $routerFile );
            preg_match_all(  '/class ([\w]+) extends [\w]*Router/', $f, $m );
            $routerClass = $m[1][0];

            // maybe check if a static method named instance(  )
            // was part of the class to let it instanciate its own router
            // here
            
            $router = new $routerClass( $request );

            // this makes the difference
            $router->appNameSpace = $this->appNameSpace;

            return $router;
        }
    }

    public function createView( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result )
    {
        $viewFile = join( DIRECTORY_SEPARATOR, array( 
            $this->path,
            'view.php',
        ) );

        // maybe try to get it from appName_createRouter function
        // here to allow the app to instanciate its own view
        // here

        if ( file_exists( $viewFile ) ) {
            $f = file_get_contents( $viewFile );
            preg_match_all(  '/class ([\w]+) extends [\w]*View/', $f, $m );
            $viewClass = $m[1][0];

            // maybe check if a static method named instance(  )
            // was part of the class to let it instanciate its own view
            // here
            
            $view = new $viewClass( $request, $result );

            // this makes the difference
            $view->appNameSpace = $this->appNameSpace;

            return $view;
        }

        trigger_error( "$viewFile does not exist");
    }

    public function createRequestParser()
    {
        $parser = new ezcMvcHttpRequestParser;
        $parser->prefix = preg_replace( '@/index\.php$@', '', $_SERVER['SCRIPT_NAME'] );
        return $parser;
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
} // }}}

/**
 * It's purpose is to encapsulate mutliple application configurations
 * even if this applications are not supposed to be runnable/usefull by
 * themselves ( ie. they depend on other applications ).
 */
class aiiProjectConfiguration extends aiiAppConfiguration { // {{{
    /**
     * List of applications encapsulated by this project config.
     *
     * @var array
     */
    public $apps = array(  );

    // this should be handled by aiiProjectInstance, like ezcDbInstance
    static public $instance = null;
    public static function instance( $path = '', $namespace = '') {
        if ( is_null( self::$instance ) ) {
            self::$instance = new aiiProjectConfiguration( $path, $namespace );
        }
        return self::$instance;
    }

    public function setUp( $appPaths, $factory = null ) {
        if ( !is_null( $factory ) ) {
            if ( !is_object( $factory ) || !method_exists( $factory, 'createAppConfiguration' ) ) {
                trigger_error( "\$factory argument, if specified, should be an object with method 'createAppConfiguration()'" );
            }
        } else {
            $factory = new aiiAppConfigurationFactory(  );
        }

        foreach( $appPaths as $appPath ) {
            // either try something else than the default app configuration
            // factory, either let the user subclass this

            $app = $factory->createAppConfiguration( $appPath );
            $this->apps[$app->namespace] = $app;
        }

        ezcBaseInit::setCallback( 
            'ezcInitPersistentSessionInstance',
            'aiiProjectPersistentObjectInitializer'
        );
        
        ezcBaseInit::setCallback( 
            'ezcInitTemplateConfiguration',
            'aiiProjectTemplateInitializer'
        );
    }

    public function createRouter( ezcMvcRequest $request ) {
        $router = new aiiProjectRouter( $request );
        $router->project = $this;
        return $router;
    }
    
    public function createView( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result )
    {
        $view = new aiiFrameworkView( $request, $result, $routeInfo );
        $view->project = $this;
        return $view;
    }

    // set the appNameSpace on the result
    public function runResultFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result )
    {
        $result->appNameSpace = $routeInfo->matchedRouteObject->appNameSpace;
        
        $result->variables['installRoot'] = preg_replace( '@/index\.php$@', '', $_SERVER['SCRIPT_NAME'] );
    }
} // }}}

/**
 * Returns an instance of app configuration for an application in a specific
 * path.
 *
 * This one sets the namespace and returns an aiiAppConfiguration which should
 * figure the rest by itself - and let it be overloadable per-application.
 */
class aiiAppConfigurationFactory { // {{{
    public function createAppConfiguration( $path ) {
        // check if path/app.php exists and returns an appConfiguration ?
        
        $app = new aiiAppConfiguration( $path, $this->getNamespace( $path ) );

        return $app;
    }

    /**
     * This app configuration factory expects the app root folder name to
     * correspond to the app namespace.
     */
    public function getNamespace( $path ) {
        $split = split ( DIRECTORY_SEPARATOR, $path );
        // i'm unsure why i cannot use -1 here but ok ...
        return $split[count( $split ) - 1];
    }
} // }}}

/**
 * First check in $tc->templatePath/$request->host.
 * Then, check in $tc->templatePath/
 * Then check in self::$paths filled with apps template paths.
 * 
 * @return void
 */
class aiiTemplateLocation implements ezcTemplateLocation { // {{{
    static public $paths = array();
    public $templateName = '';
    public $requestHost = '';
    public function __construct( $templateName, $requestHost = '' )
    {
        $this->templateName = $templateName;
        $this->requestHost = $requestHost;
    }

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
        $testPath = join( DIRECTORY_SEPARATOR, array(
            $userPath,
            $this->templateName,
        ) );

        if ( file_exists( $testPath ) )
        {
            return $testPath;
        }

        $tested[] = $testPath;

        // check self::$paths
        foreach( self::$paths as $path )
        {
            $testPath = join( DIRECTORY_SEPARATOR, array( 
                $path,
                $this->templateName,
            ) );

            if ( file_exists( $testPath ) )
            {
                return $testPath;
            }

            $tested[] = $testPath;
        }

        var_dump( $tested );
        throw new Exception( 'Could not locate template' );
    }
} // }}}

/**
 * Sets up aiiTemplateLocation.
 */
class aiiProjectTemplateInitializer implements ezcBaseConfigurationInitializer { // {{{
    static public function configureObject( $cfg ) {
        $project = aiiProjectConfiguration::instance(  );
        
        $cfg->templatePath = join( DIRECTORY_SEPARATOR, array( 
            $project->path,
            $project->getComponentConfig( 'Template', 'sourcePath' ),
        ) );

        $cfg->compilePath = join( DIRECTORY_SEPARATOR, array( 
            $project->path,
            $project->getComponentConfig( 'Template', 'compilePath' ),
        ) );        

        $cfg->context = $project->getComponentConfig( 'Template', 'context' );

        foreach( $project->apps as $app ) {
            aiiTemplateLocation::$paths[] = join( DIRECTORY_SEPARATOR, array( 
                $app->path,
                $app->getComponentConfig( 'Template', 'sourcePath' ),
            ) );

            foreach( $app->getComponentConfig( 'Template', 'extensionClasses' ) as $class ) {
                $cfg->addExtension( $class );
            }
        }
    }
} // }}}

/**
 * Sets up a default persistent object session instance able to load any
 * of the persistent object class of the project applications.
 */
class aiiProjectPersistentObjectInitializer implements ezcBaseConfigurationInitializer { // {{{
    public static function configureObject( $instance ) {
        $project = aiiProjectConfiguration::instance(  );

        $managers = array(  );

        foreach( $project->apps as $app )
        {
            $manager = $project->getComponentConfig( 'PersistentObject', 'definitionsManager' );

            if ( ! $manager instanceof ezcPersistentDefinitionManager ) {
                $testPath = join( DIRECTORY_SEPARATOR, array( 
                    $app->path,
                    $app->getComponentConfig( 'PersistentObject', 'definitionsPath' ),
                ) );
                
                if ( is_dir( $testPath ) )
                {
                    $manager = new ezcPersistentCacheManager( 
                        new ezcPersistentCodeManager( 
                            $testPath
                        )
                    );
                }
            }
            
            if ( $manager instanceof ezcPersistentDefinitionManager ) {
                $managers[] = $manager;
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
} // }}}

/**
 * Gets routers of all project applications, and uses their routes.
 *
 * Note that it prefixes routes with the app namespace.
 */
class aiiProjectRouter extends ezcMvcRouter { // {{{
    public $project;
    public function createRoutes(  ) {
        $routes = array(  );

        foreach( $this->project->apps as $app ) {
            if ( $router = $app->createRouter( $this->request ) ) {
                foreach( self::prefix( sprintf( '/%s/', $app->namespace ), $router->createRoutes(  ) ) as $name => $route ) {
                    $route->appNameSpace = $app->namespace;
                    $routes[$name] = $route;
                }
            }
        }

        return $routes;
    }

    // one liner modification under comment "hack"
    public function getRoutingInformation(  ) {
        $routes = $this->createRoutes();

        if ( ezcBase::inDevMode() && ( !is_array( $routes ) || !count( $routes ) ) )
        {
            throw new ezcMvcNoRoutesException();
        }

        foreach ( $routes as $route )
        {
            if ( ezcBase::inDevMode() && !$route instanceof ezcMvcRoute )
            {
                throw new ezcBaseValueException( 'route', $route, 'instance of ezcMvcRoute' );
            }

            $routingInformation = $route->matches( $this->request );

            if ( $routingInformation !== null )
            {
                // hack
                $routingInformation->matchedRouteObject = $route;

                // Add the router to the routing information struct, so that
                // can be passed to the controllers for reversed route
                // generation.
                $routingInformation->router = $this;

                return $routingInformation;
            }
        }

        throw new ezcMvcRouteNotFoundException( $this->request );
    }
} // }}}

/**
 * Returns the view with appNameSpace equal to result appNameSpace.
 */
class aiiFrameworkView extends ezcMvcView { // {{{
    public $project;
    public $routeInfo;
    public function __construct( ezcMvcRequest $request, ezcMvcResult $result, ezcMvcRoutingInformation $routeInfo ) {
        parent::__construct( $request, $result );

        $this->routeInfo = $routeInfo;
    }
    public function createZones( $layout )
    {
        foreach( $this->project->apps as $app ) {
            $result = $this->result;
            if ( $this->result->appNameSpace == $app->namespace ) {
                $view = $app->createView( $this->routeInfo, $this->request, $this->result );
                $views = $view->createZones( false );
            }
        }

        if ( !isset( $views ) ) {
            trigger_error( "Could not figure the app view" );
        }

        if ( $layout )
        {
            $views[] = new ezcMvcTemplateViewHandler( 
                $this->project->getComponentConfig( 
                    'MvcTools',
                    'layoutZoneName'
                ),
                new aiiTemplateLocation( 
                    $this->project->getComponentConfig( 
                        'MvcTools',
                        'layoutTemplateName'
                    ),
                    $this->request->host
                )
            );
        }

        return $views;
    }
} // }}}
