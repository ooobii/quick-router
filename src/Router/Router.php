<?php

namespace ooobii\QuickRouter\Router;

use \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE;


/**
 * Defines a collection of routes that can be used to process requests of a particular endpoint.
 */
class Router {

    /**
     * Stores routes that are created by the user.
     * @var array
     */
    private $_routes = [];

    /**
     * Stores functions to be called when certain HTTP error codes are encountered.
     * @var array<int|Closure>
     */
    private $_errorRoutes = [];

    /**
     * Stores the path to the directory that the router is being used in.
     * @var string
     */
    private $_input;

    /**
     * Stores the path to the request the user is asking for.
     * @var string
     */
    private $_inputUri;

    /**
     * Stores the root of request URIs that this router responds to.
     * @var string
     */
    private $_rootUri;

    /**
     * Stores the HTTP request type that the user is asking for.
     * @var ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE
     */
    private $_requestType;

    /**
     * Signifies if the output returned by the router should always be JSON-parsable.
     * @var bool
     */
    private $_alwaysJson;


    /**
     * Defines a new router for processing requests
     * 
     * @param string $root The root that the router should start processing requests for.
     * @param bool $alwaysJson Signifies if all routes connected to the router should always be JSON-parsable.
     */
    public function __construct($root = NULL, $alwaysJson = FALSE) {

        //if the root is not set, set it to the current directory
        if (empty($root)) $root = '/';

        //store root URL this router should respond to.
        $this->_rootUri = $root;
        $this->_alwaysJson = $alwaysJson;
        $this->_input = $this->_parseParams();

        //if this is not a CLI request,
        if(!$this->isCLI()) {

            //use server variables to get the request type and URI.
            $this->_inputUri = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        } else {

            //if no parameter was supplied, treat as root request.
            if(!isset($_SERVER['argv'][1])) {
                $this->_inputUri = rtrim($this->_rootUri, '/');

            } else {

                //otherwise, use first CLI parameter as request.
                $this->_inputUri = rtrim(strtolower($_SERVER['argv'][1]), '/');

            }
        }

        //if we're fetching the root URL of the router, append a slash to the end of the request URL.
        if($this->_inputUri === $this->_rootUri) {
            $this->_inputUri = $this->_rootUri . '/';
        }

    }

    /**
     * Signifies if the current request originates from a command line environment.
     * 
     * @return bool
     */
    public function isCLI() {
        return !isset($_SERVER['REQUEST_URI']) && !isset($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Returns the root of the request URIs that this router responds to.
     * 
     * @return string
     */
    public function root() {
        return $this->_rootUri;
    }

    /**
     * Returns the collection of routes that are associated to this router for each HTTP request type.
     * 
     * @return array<\ooobii\QuickRouter\Router\Route>
     */
    public function routes() {
        return $this->_routes;
    }

    /**
     * Returns a direct reference to the route that will be handled by a particular endpoint.
     * 
     * @param string $endpoint The URI that the returned route will handle.
     * @param null|string $requestType If provided, require the route returned to be assigned to the particular HTTP request type.
     * @param bool $real 
     *   - If set to `FALSE` (default), parameters defined in the route URI will be ignored.
     *   - If set to `TRUE`, the provided endpoint will be used as-is.
     * @return bool|\ooobii\QuickRouter\Router\Route
     *   - If the provided endpoint is not associated with any route, nothing is returned.
     *   - If the provided endpoint is associated with a route within this router, the route is returned. 
     */
    public function &getRoute(string $endpoint, ?string $requestType = NULL, bool $real = FALSE) {

        //go through each endpoint, and check if route qualifies.
        foreach($this->_routes as &$route) {

            /** @var \ooobii\QuickRouter\Router\Route $route */

            //the route must have the same request type.
            if($requestType !== NULL && $route->requestType() !== $requestType) continue;

            //if we're checking the real endpoint value
            if($real) {

                if($route->endpoint() !== $endpoint) continue;

            } else {

                if(!$route->doesRouteQualify($endpoint, $requestType)) continue;

            }

            //checks pass for current route!
            return $route;

        }

        $noResult = FALSE;
        return $noResult;

    }

    /**
     * Returns the collection of routes that are associated to this router for each HTTP error code.
     * 
     * @return array<int,\ooobii\QuickRouter\Router\Route>
     */
    public function errorRoutes() {
        return $this->_errorRoutes;
    }

    /**
     * Returns the current request's HTTP request type.
     * 
     * @return string
     * @see \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE
     */
    public function requestType() {
        return $this->_requestType;
    }

    /**
     * Signifies if all routes attached to this router should return JSON responses.
     * 
     * @return bool
     */
    public function alwaysReturnJson() {
        return $this->_alwaysJson;
    }




    /**
     * Executes the route that matches the current request, and returns data to the user.
     * 
     * @return bool
     */
    public function process() {

        //check if the request URI is part of this root.
        if(strpos(strtolower($this->_inputUri), strtolower($this->_rootUri)) !== 0) {
            return FALSE;
        }

        //remove root from request URI.
        $this->_inputUri = substr($this->_inputUri, strlen($this->_rootUri));

        //find the routes that match this request type & URI.
        $qualifiedRoutes = array_filter($this->_routes, function(Route $route) {
            return $route->doesRouteQualify($this->_inputUri, $this->requestType());
        });

        //loop through the routes and find the one that matches the current request.
        foreach($qualifiedRoutes as $route) {

            try {

                //if this route has parameters to be extracted from the input URL,
                if($route->hasUriParameters()) {

                    //extract the parameters from the input URL and merge them with our current input.
                    $this->_parseRouteParameters($route);

                }

                //execute the route's handler.
                $output = $route($this, $this->_input);

                //if the router or route is supposed to always return JSON, convert it.
                if($this->_alwaysJson || $route->alwaysJson) {
                    $output = json_encode($output);

                    //make sure that the JSON conversion was successful.
                    if(json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception("The output from this endpoint failed to be converted to JSON.");
                    }

                }

                //print the output to the output stream.
                if($output !== NULL) {
                    echo $output;

                    //in a CLI context, we should print a new line so prompt isn't on the same line as output.
                    if($this->isCLI()) echo PHP_EOL;
                }

            } catch(\Throwable $exception) {

                //error was encountered within the handler. handle it with an error route.
                return $this->handleError(500, $exception);

            }

            //route processed successfully
            return TRUE;

        }

        //if we've reached this point, no qualified routes were discovered.
        return FALSE;
    }

    /**
     * Adds a route handler to the router.
     * 
     * @param string $type The type of request that this route should handle.
     * @param string $endpoint The path that this route should handle.
     * @param \Closure $handler The function to be called when the route is requested.
     *   - This function accepts one argument: an array of input parameters related to the request.
     */
    public function addRoute(string $type, string $endpoint, \Closure $handler, bool $alwaysJson = FALSE) {
        $endpoint = rtrim($endpoint, '/');
        $this->_routes[] = new \ooobii\QuickRouter\Router\Route($type, $endpoint, $handler, $alwaysJson);
    }

    /**
     * Adds a handler to be called when a particular HTTP status code is reached.
     * 
     * @param int $status The HTTP status code that should trigger the handler.
     * @param \Closure $handler The function to be called when the status code is reached.
     *  - This function accepts one argument: an exception that was thrown (if any) while processing the request.
     */
    public function setErrorRoute(int $status, callable $handler) {
        $this->_errorRoutes[$status] = $handler;
    }



    /**
     * Transforms parameters provided in the user's request.
     * @var array
     * @throws Exception
     */
    private function _parseParams() {
        if(!$this->isCLI()) {
            $this->_requestType = strtoupper($_SERVER['REQUEST_METHOD']);
        } else {
            $this->_requestType = HTTP_REQUEST_TYPE::GET;
        }

        switch($this->_requestType) {

            case HTTP_REQUEST_TYPE::HEAD:
            case HTTP_REQUEST_TYPE::DELETE:
            case HTTP_REQUEST_TYPE::GET:
                $input = $_GET;
            break;

            case HTTP_REQUEST_TYPE::POST:
                $input = $_POST;
            break;

            case HTTP_REQUEST_TYPE::OPTIONS:
            case HTTP_REQUEST_TYPE::PATCH:
            case HTTP_REQUEST_TYPE::PUT:
                parse_str(file_get_contents("php://input"), $input);
            break;
            

            default:
                throw new \Exception("This HTTP request type is not implemented.");
            break;

        }

        $params = [];
        foreach($input as $key => $query) {
            $parsed = json_decode($query);
            if(json_last_error() === JSON_ERROR_NONE) {
                $params[$key] = $parsed;
            } else {
                $params[$key] = $query;
            }
        }

        return $params;


    }

    private function _parseRouteParameters(\ooobii\QuickRouter\Router\Route $route) {

        $routeParamValues = $route->extractParametersFromUrlParts($this->_inputUri);
        if($routeParamValues !== FALSE) {
            $this->_input = array_merge($this->_input, $routeParamValues);
        }

    }

    /**
     * Executes the error handler for the provided HTTP error code.
     * 
     * @param int $status The HTTP error code to execute the error handler for.
     * @param \Throwable|null $exception The exception that was thrown that caused the error.
     * @return void|bool
     */
    public function handleError(int $status, \Throwable $exception = null) {

        if($exception instanceof \Throwable && $exception->getCode() !== 0)
            $status = $exception->getCode();

        if(isset($this->_errorRoutes[$status])) {
            $this->_errorRoutes[$status]($exception);
            http_response_code($status);
            return TRUE;
        }

        return FALSE;
    }

}