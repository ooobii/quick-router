<?php 
declare(strict_types=1);
ini_set('xdebug.mode', "coverage");

use ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE;
use PHPUnit\Framework\TestCase;

/**
 * RouterTests
 * @group Router
 */
class RouterTests extends TestCase {

    public function setUp(): void {

        // $this->tempFolder = sys_get_temp_dir() . '/quick-router_' . uniqid();
        // $this->pid = NULL;

        // //create temp folder for testing.
        // if(is_dir($this->tempFolder)) rmdir($this->tempFolder);
        // mkdir($this->tempFolder);

        // //copy source to proper folder.
        // copy('tests/testController.php', $this->tempFolder . '/index.php');
        // system('cp -r vendor ' . $this->tempFolder . '/');
        // copy('composer.json', $this->tempFolder . '/composer.json');
        // copy('composer.lock', $this->tempFolder . '/composer.lock');

        // //run composer update ignoring all command output
        // system('composer update --working-dir "' . $this->tempFolder . '" > /dev/null 2>&1');

    }

    public function tearDown(): void {

        // //kill the PID of the server.
        // if($this->pid) {
        //     shell_exec('kill ' . $this->pid);
        //     $pid = NULL;
        // }

        // //remove temp folder.
        // if(is_dir($this->tempFolder))
        //     system('rm -rf -- ' . escapeshellarg($this->tempFolder));

    }



    /** @test */
    public function standardConstruct() {
        $router = new \ooobii\QuickRouter\Router\Router();

        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Router', $router, 'Failed to create router class.');
        $this->assertEquals('/', $router->root(), 'Router instantiated with incorrect default root URI.');
        $this->assertEquals(FALSE, $router->alwaysReturnsJSON(), 'Router instantiated with incorrect default JSON output assertion.');
    }


    /** @test */
    public function constructWithCustomRoot() {
        $router = new \ooobii\QuickRouter\Router\Router('/Api');

        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Router', $router, 'Failed to create router class with custom root.');
        $this->assertEquals('/Api', $router->root());
        $this->assertEquals(FALSE, $router->alwaysReturnsJSON());
    }


    /** @test */
    public function constructWithMissingRootSlash() {
        $router = new \ooobii\QuickRouter\Router\Router('');

        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Router', $router, 'Failed to create router class.');
        $this->assertEquals('/', $router->root(), 'Router instantiated with incorrect default root URI.');
        $this->assertEquals(FALSE, $router->alwaysReturnsJSON(), 'Router instantiated with incorrect default JSON output assertion.');
    }


    /** @test */
    public function constructInHttpContextWithBadRoute() {

        //setup mock $_SERVER vars.
        $this->SERVER_BACKUP = $_SERVER;
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['argv'] = [0 => 'index.php', 1 => '/test/dump'];

        try {

            include 'tests/testController.php';
            $this->assertInstanceOf('\ooobii\QuickRouter\Router\Router', $testRouter, 'Failed to create router class from test controller file.');
            $this->assertFalse($testRouter->process(), 'Router falsely reported route handling.');
            $this->assertFalse($testRouter->isCLI(), 'Router falsely detected CLI context.');

        } catch(\Throwable $ex) {
            $_SERVER = $this->SERVER_BACKUP;
            throw ($ex);
        } finally {
            $_SERVER = $this->SERVER_BACKUP;
        }



        //restore $_SERVER vars.
        $_SERVER = $this->SERVER_BACKUP;

    }


    /** @test */
    public function constructInHttpContextWithGoodRoute() {

        //setup mock $_SERVER vars.
        $this->SERVER_BACKUP = $_SERVER;
        $_SERVER['REQUEST_URI'] = '/testApi/test/dump';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        try {

            $router = new \ooobii\QuickRouter\Router\Router('/testApi/');
            $this->assertInstanceOf('\ooobii\QuickRouter\Router\Router', $router, 'Failed to create router class from test controller file.');

            $router->addRoute(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, '/test/dump', function($input) {
                return 'testSuccessful';
            });
            $this->assertCount(1, $router->routes(), 'Router failed to add route.');
            $route = $router->getRoute('/test/dump');
            $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Router failed locate route added to router.');

            $this->assertFalse($router->isCLI(), 'Router falsely detected CLI context.');
            $this->assertTrue($router->process(), 'Router falsely reported route handling.');

        } catch(\Throwable $ex) {
            $_SERVER = $this->SERVER_BACKUP;
            throw ($ex);
        } finally {
            $_SERVER = $this->SERVER_BACKUP;
        }



        //restore $_SERVER vars.
        $_SERVER = $this->SERVER_BACKUP;

    }


    /** @test */
    public function constructAsAlwaysJson() {
        $router = new \ooobii\QuickRouter\Router\Router('/Api', TRUE);

        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Router', $router, 'Failed to create router class with JSON output assertion.');
        $this->assertEquals('/Api', $router->root(), 'Router instantiated with incorrect root URI.');
        $this->assertEquals(TRUE, $router->alwaysReturnsJSON(), 'Router instantiated with incorrect JSON output assertion.');
    }


    /** @test */
    public function getRequestTypeFromRouter() {
        $router = new \ooobii\QuickRouter\Router\Router();
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Router', $router, 'Failed to create router class.');

        $this->assertEquals(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, $router->requestType(), 'Incorrect class added to array of routes in router.');
    }


    /** @test */
    public function addRouteToRouter() {
        $router = new \ooobii\QuickRouter\Router\Router();
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Router', $router, 'Failed to create router class.');

        $router->addRoute(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET, '/test', function($input) {
            return 'test result';
        });
        $this->assertEquals(1, count($router->routes()), 'Failed to add route to router.');
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $router->routes()[0], 'Incorrect class added to array of routes in router.');
    }


    /** @test */
    public function addErrorRouteToRouter() {
        $router = new \ooobii\QuickRouter\Router\Router();
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Router', $router, 'Failed to create router class.');

        $router->setErrorRoute(404, function($input) {
            return '404 result';
        });
        $this->assertEquals(1, count($router->errorRoutes()), 'Failed to add route to router.');
        $this->assertInstanceOf('closure', $router->errorRoutes()[404], 'Incorrect class added to array of routes in router.');
    }


    /** @test */
    public function getRouteFromEndpointUri() {
        $router = new \ooobii\QuickRouter\Router\Router();
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Router', $router, 'Failed to create router class.');

        $router->addRoute(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::PUT, '/test/endpoint', function($input) {
            return 'test result';
        });
        $this->assertEquals(1, count($router->routes()), 'Failed to add route to router.');

        $route = $router->routes()[0];
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $route, 'Incorrect class added to array of routes in router.');
        $this->assertEquals('/test/endpoint', $route->endpoint(), 'Incorrect endpoint added to route.');
        $this->assertEquals($route, $router->getRoute('/test/endpoint'), 'Failed to get route from router using endpoint URI.');
        $this->assertEquals($route, $router->getRoute('/test/endpoint', \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::PUT), 'Failed to get route from router using endpoint URI.');
        $this->assertIsBool($router->getRoute('/test/endpoint', \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET), 'Failed to get route from router using endpoint URI.');
        $this->assertIsBool($router->getRoute('/test', \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET), 'Failed to get route from router using endpoint URI.');
        $this->assertIsBool($router->getRoute('/something/else', \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET), 'Failed to get route from router using endpoint URI.');
    }


    /** @test */
    public function getRouteFromEndpointUriWithParams() {
        $router = new \ooobii\QuickRouter\Router\Router();
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Router', $router, 'Failed to create router class.');

        $router->addRoute(\ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::PUT, '/test/{subject}/working/{id}', function($input) {
            return 'test result';
        });
        $this->assertEquals(1, count($router->routes()), 'Failed to add route to router.');
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Route', $router->routes()[0], 'Incorrect class added to array of routes in router.');

        $route = $router->routes()[0];
        $this->assertEquals('/test/{subject}/working/{id}', $route->endpoint(), 'Incorrect endpoint added to route.');
        $this->assertEquals(TRUE, $route->hasUriParameters(), 'URI parameters failed to register in route.');
        $this->assertEquals(2, count($route->getUriParameterIndices()), 'Incorrect number of URI parameters registered in route.');


        $this->assertEquals(
            $route, $router->getRoute('/test/subj/working/1'),
            'Failed to get route from router using endpoint URI with values.'
        );
        $this->assertEquals(
            $route,
            $router->getRoute('/test/subj/working/1', \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::PUT),
            'Failed to get route from router using endpoint URI with values.'
        );
        $this->assertEquals(
            $route,
            $router->getRoute('/test/{subject}/working/{id}', \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::PUT, TRUE),
            'Failed to get route from router using true endpoint URI.'
        );
        

        $this->assertNotInstanceOf(
            '\ooobii\QuickRouter\Router\Route',
            $router->getRoute('/testing/subj/working/1'),
            'Route was returned with incorrect middle part in URI.'
        );
        $this->assertNotInstanceOf(
            '\ooobii\QuickRouter\Router\Route',
            $router->getRoute('/test/subj/working/1/ending'),
            'Route was returned with incorrect ending part on URI.'
        );
        $this->assertNotInstanceOf(
            '\ooobii\QuickRouter\Router\Route',
            $router->getRoute('/test/subj/working/1', \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::GET),
            "Route was returned with a valid URI provided, but an incorrect request method."
        );
        $this->assertNotInstanceOf(
            '\ooobii\QuickRouter\Router\Route',
            $router->getRoute('/test/subj/working/1', \ooobii\QuickRouter\Types\HTTP_REQUEST_TYPE::PUT, TRUE),
            "Route was returned with a valid URI & request type provided, "
        );
    }


    /** @test */
    public function testCliSignification() {
        $router = new \ooobii\QuickRouter\Router\Router();
        $this->assertInstanceOf('\ooobii\QuickRouter\Router\Router', $router, 'Failed to create router class.');

        $this->assertTrue($router->isCli(), 'Failed to detect CLI environment.');
    }


}
