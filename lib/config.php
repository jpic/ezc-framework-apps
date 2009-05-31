<?php
class aiiMvcConfiguration implements ezcMvcDispatcherConfiguration
{
    function createRequestParser()
    {
        $parser = new ezcMvcHttpRequestParser;
        $parser->prefix = preg_replace( '@/index\.php$@', '', $_SERVER['SCRIPT_NAME'] );
        return $parser;
    }

    function createRouter( ezcMvcRequest $request )
    {
        return new aiiRouter( $request );
    }

    function createView( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result )
    {
        switch ( $routeInfo->matchedRoute )
        {
            case '/':
                return new aiiRootView( $request, $result );
            case '/:name':
                return new aiiNameView( $request, $result );
            case '/downloadTest':
                return new aiiTestView( $request, $result );
        }
    }

    function createResponseWriter( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result, ezcMvcResponse $response )
    {
        return new ezcMvcHttpResponseWriter( $response );
    }

    function createFatalRedirectRequest( ezcMvcRequest $request, ezcMvcResult $result, Exception $response )
    {
        var_Dump( $request );
        $req = clone $request;
        $req->uri = '/FATAL';

        return $req;
    }

    function runPreRoutingFilters( ezcMvcRequest $request )
    {
    }

    function runRequestFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request )
    {
    }

    function runResultFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result )
    {
        $result->variables['installRoot'] = preg_replace( '@/index\.php$@', '', $_SERVER['SCRIPT_NAME'] );
    }

    function runResponseFilters( ezcMvcRoutingInformation $routeInfo, ezcMvcRequest $request, ezcMvcResult $result, ezcMvcResponse $response )
    {
    }
}
?>
