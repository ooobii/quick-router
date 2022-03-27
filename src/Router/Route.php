<?php

namespace ooobii\QuickRouter\Router;

class Route {

    /**
     * Stores the function to be called when the route is requested.
     * @var \Closure
     */
    private $_handler;

    /**
     * The type of request that this route should handle.
     * @var string
     */
    public $requestType;

    /**
     * The path that this route should handle.
     * @var string
     */
    public $endpoint;

    /**
     * Determines if output from this router should always be JSON.
     * @var bool
     */
    public $alwaysJson = FALSE;

    /**
     * Stores the path to the directory that the router is being used in.
     * @var array
     */
    private $_endpointParts = [];

    /**
     * Stores if this route has parameters to parse within it's path.
     * @var bool
     */
    private $_hasParams = FALSE;

    /**
     * Stores the names of the parameters as keys & the part index as the value.
     * @var array
     */
    private $_params = [];


    /**
     * Defines a new route for processing requests
     * 
     * @param string $type The HTTP request type this route responds to.
     * @param string $endpoint The path that this route should handle relative to the router's root.
     * @param Closure $handler The function to be called when the route is requested.
     * @param bool $alwaysJson Determines if output from this route should always be treated as a JSON object.
     */
    public function __construct(string $type, string $endpoint, \Closure $handler, bool $alwaysJson = FALSE) {
        $this->endpoint = $endpoint;
        $this->_endpointParts = explode('/', $endpoint);
        $this->requestType = $type;
        $this->alwaysJson = $alwaysJson;
        $this->_handler = $handler;

        //store parameter part locations from the endpoint (if any).
        $this->_hasParams = strpos($this->endpoint, '{') !== FALSE;

        if($this->_hasParams) {

            //break out the endpoint into parts.
            $parts = explode('/', $this->endpoint);

            //for each part, check if it is a parameter by looking for brackets.
            foreach($parts as $index => $part) {
                if(strpos($part, '{') !== FALSE && strpos($part, '}') !== FALSE) {
                    $this->_params[substr($part, 1, -1)] = $index;
                }
            }

        }


    }

    /**
     * Signifies if the route has parameters that will be parsed from the input URL.
     * 
     * @return bool
     */
    public function hasParameters() {
        return $this->_hasParams;
    }

    /**
     * Checks the provided URL to see if it matches this route.
     * @param string $inputUrl The URL requested by the user.
     * @return bool `TRUE` if this request should be processed by this route, `FALSE` otherwise.
     */
    public function doesRouteQualify(string $inputUrl) {

        //break out the input url into parts.
        $inputUrlParts = explode('/', $inputUrl);

        //make sure each of the endpoint parts for this route are in the input url.
        foreach($this->_endpointParts as $index => $part) {

            //if the part is not in the input url, this route doesn't qualify.
            if(!isset($inputUrlParts[$index]))
                return FALSE;

            //if the part is not the same as the input url part, this route doesn't qualify.
            if(!$this->_hasParams) {
                
                if(strtolower($part) !== strtolower($inputUrlParts[$index]))
                    return FALSE;

            } else {

                if(!in_array($index, $this->_params) && strtolower($part) !== strtolower($inputUrlParts[$index]))
                    return FALSE;

            }

        }

        //checks pass! Return TRUE.
        return TRUE;

    }

    /**
     * Checks the endpoint of this route's URL for parameters, and if there are any, extracts them from the input URL.
     * @param string $inputUrl The URL requested by the user.
     * @return bool|array<string,mixed> `FALSE` if parameters are not in use for this route, otherwise an associative array of parameters and their values. 
     */
    public function extractParametersFromUrlParts(string $inputUrl) {

        //if this endpoint doesn't have parameters defined, return FALSE.
        if(!$this->_hasParams)
            return FALSE;

        //if the input URL doesn't pass qualification, return FALSE.
        if(!$this->doesRouteQualify($inputUrl))
            return FALSE;

        //store input parameters to be returned.
        $params = [];

        //break out the input url into parts.
        $inputUrlParts = explode('/', $inputUrl);

        //check if the url has parts located for each parameter.
        foreach($this->_params as $paramName => $paramIndex) {

            //if the parameter index is not in the input url, return FALSE.
            if(!isset($inputUrlParts[$paramIndex]))
                return FALSE;

            //if the parameter index is in the input url, store the value.
            $paramValue = $inputUrlParts[$paramIndex];

            //if the parameter value is in the input url, store the value.
            $params[$paramName] = $paramValue;

        }

        //if no input parameters were collected, return FALSE, otherwise return the array of parameters and their values.
        return empty($params) ? FALSE : $params;

    }


    /**
     * Invokes the request handler and returns the output.
     * 
     * @param \QuickRouter\Router\Router $parent The router that this route is being invoked from.
     * @param array $inputParameters The parameters that were passed to the route.
     * @return mixed The output of the route's handler.
     */
    public function __invoke(\ooobii\QuickRouter\Router\Router &$parent, array $inputParameters) {
        return $this->_handler->call($parent, $inputParameters);
    }


}