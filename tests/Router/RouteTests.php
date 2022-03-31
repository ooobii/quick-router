<?php 
declare(strict_types=1);
ini_set('xdebug.mode', "coverage");

use PHPUnit\Framework\TestCase;

/**
 * RouteTests
 * @group Route
 */
class RouteTests extends TestCase {

    /** @test */
    public function jsonRestriction() {

        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, '/', function() {}, FALSE);
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create "GET" route class.');
        $this->assertEquals(FALSE, $route->alwaysReturnsJSON(), 'Failed to set JSON output restriction.');

        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, '/', function() {}, TRUE);
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create "GET" route class.');
        $this->assertEquals(TRUE, $route->alwaysReturnsJSON(), 'Failed to set JSON output restriction.');

    }


    /** @test */
    public function requestTypeRestriction() {

        // GET
        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, '/', function() {});

        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create "GET" route class.');
        $this->assertEquals(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, $route->requestType(), 'Failed to set "GET" request type.');


        // POST
        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::POST, '/', function() {});

        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create "POST" route class.');
        $this->assertEquals(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::POST, $route->requestType(), 'Failed to set "POST" request type.');


        // PUT
        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::PUT, '/', function() {});

        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create "PUT" route class.');
        $this->assertEquals(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::PUT, $route->requestType(), 'Failed to set "PUT" request type.');


        // DELETE
        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::DELETE, '/', function() {});

        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create "DELETE" route class.');
        $this->assertEquals(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::DELETE, $route->requestType(), 'Failed to set "DELETE" request type.');


        // PATCH
        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::PATCH, '/', function() {});

        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create "PATCH" route class.');
        $this->assertEquals(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::PATCH, $route->requestType(), 'Failed to set "PATCH" request type.');


        // HEAD
        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::HEAD, '/', function() {});

        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create "HEAD" route class.');
        $this->assertEquals(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::HEAD, $route->requestType(), 'Failed to set "HEAD" request type.');


        // OPTIONS
        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::OPTIONS, '/', function() {});

        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create "OPTIONS" route class.');
        $this->assertEquals(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::OPTIONS, $route->requestType(), 'Failed to set "OPTIONS" request type.');
    }


    /** @test */
    public function handlerText() {

        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, '/', function($input) {
            return 'This is part of a test.';
        });

        $router = new \ooobii\QuickRouter\Router\Router();
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create route class.');
        $this->assertEquals(TRUE, is_callable($route), 'Route is not able to be invoked directly.');
        $this->assertEquals(
            'This is part of a test.', 
            $route($router, []), 
            'The handler failed to return the string value to be returned.'
        );
    }


    /** @test */
    public function handlerJson() {

        $testInput = [4, 5, 6];
        $expectedOutput = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
            'd' => [4, 5, 6]
        ];
        $expectedJson = json_encode($expectedOutput);

        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, '/', function($input) {
            return [
                'a' => 1,
                'b' => 2,
                'c' => 3,
                'd' => $input
            ];
        }, TRUE);

        $router = new \ooobii\QuickRouter\Router\Router();
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create route class.');
        $this->assertEquals(TRUE, is_callable($route), 'Route is not able to be invoked directly.');

        $this->assertEquals(
            $expectedOutput, 
            $route($router, $testInput),
            'The handler failed to return a proper JSON object value to be returned.'
        );
    }


    /** @test */
    public function extractParamValuesFromUri() {

        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, '/test/without/params', function($input) {
            return 'This is part of a test.';
        });
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create route class.');
        $this->assertIsBool($route->extractParametersFromUrlParts('/1/test/testing'), 'Failed to extract param values from URI.');



        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, '/{id}/test/{input}', function($input) {
            return 'This is part of a test.';
        });
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create route class.');

        $expectedParams = [
            'id' => '1',
            'input' => 'testing'
        ];
        $this->assertEquals($expectedParams, $route->extractParametersFromUrlParts('/1/test/testing'), 'Failed to extract param values from URI.');
        $this->assertIsBool($route->extractParametersFromUrlParts('/1/test/testing/wee'), 'Failed to extract param values from URI.');
        $this->assertIsBool($route->extractParametersFromUrlParts('/1/test'), 'Failed to extract param values from URI.');

    }


    /** @test */
    public function handlerInputHandling() {
        $router = new \ooobii\QuickRouter\Router\Router();
        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, '/', function($input) {
            return "The testing input value is {$input['testValue']}.";
        });

        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to create route class.');
        $this->assertEquals(TRUE, is_callable($route), 'Route is not able to be invoked directly.');
        $this->assertEquals(
            "The testing input value is wee.", 
            $route($router, ['testValue' => 'wee']), 
            'The handler failed to return the string value interpolated with an input value.'
        );
    }


    /** @test */
    public function endpointQualificationBasic() {
        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, '/test/something', function($input) {
            return "The testing input value is {$input['testValue']}.";
        });
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to load route class from router.');
        $this->assertEquals('/test/something', $route->endpoint(), 'Failed to set endpoint.');


        $inputURI_1 = '/test/something';
        $inputURI_2 = '/test/wee/something';
        $inputURI_3 = '/differentRoot/test/something';
        $inputURI_4 = '/test/something/somethingElse';
        $inputType_1 = \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET;
        $inputType_2 = \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::POST;
        $inputType_3 = \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::PUT;
        $this->assertEquals(TRUE,  $route->doesRouteQualify($inputURI_1), 'URI that should qualify for route reported incorrect result.');
        $this->assertEquals(TRUE,  $route->doesRouteQualify($inputURI_1, $inputType_1), 'URI that should qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_1, $inputType_2), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_2), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_2, $inputType_1), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_2, $inputType_2), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_3), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_3, $inputType_1), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_3, $inputType_3), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_4), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_4, $inputType_2), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_4, $inputType_3), 'URI that should NOT qualify for route reported incorrect result.');


    }


    /** @test */
    public function endpointQualificationWithUriParams() {
        $route = new \ooobii\QuickRouter\Router\Route(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::POST, '/test/{subject}/working/{id}', function($input) {
            return "The testing input value is {$input['testValue']}.";
        });
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Failed to load route class from router.');
        $this->assertEquals('/test/{subject}/working/{id}', $route->endpoint(), 'Failed to set endpoint.');
        $this->assertEquals(TRUE, $route->hasUriParameters(), 'URI parameters within route failed to register.');


        $inputURI_1 = '/test/subj/working/2';               //VALID
        $inputURI_2 = '/test/something';                    //INVALID
        $inputURI_3 = '/differentRoot/test/subj/working/2'; //INVALID
        $inputURI_4 = '/test/something/somethingElse';      //INVALID
        $inputType_1 = \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::POST;
        $inputType_2 = \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET;
        $inputType_3 = \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::PUT;
        $this->assertEquals(TRUE,  $route->doesRouteQualify($inputURI_1), 'URI that should qualify for route reported incorrect result.');
        $this->assertEquals(TRUE,  $route->doesRouteQualify($inputURI_1, $inputType_1), 'URI that should qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_1, $inputType_2), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_2), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_2, $inputType_3), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_3), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_3, $inputType_2), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_4), 'URI that should NOT qualify for route reported incorrect result.');
        $this->assertEquals(FALSE, $route->doesRouteQualify($inputURI_4, $inputType_1), 'URI that should NOT qualify for route reported incorrect result.');

    }


}