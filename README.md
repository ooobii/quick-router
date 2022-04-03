# Quick Router for PHP

[![Build Status](https://jenkins.matthewwendel.info/job/quick-router/job/main/badge/icon)](https://jenkins.matthewwendel.info/job/quick-router/job/main/) [![Test Status](https://img.shields.io/jenkins/tests?compact_message&failed_label=failures&jobUrl=https%3A%2F%2Fjenkins.matthewwendel.info%2Fjob%2Fquick-router%2Fjob%2Fmain%2F&label=tests&passed_label=successful&skipped_label=untested)](https://jenkins.matthewwendel.info/job/quick-router/job/main/)
[![Composer](https://img.shields.io/static/v1?label=composer&message=packagist&color=orange)](https://packagist.org/packages/ooobii/quick-router) 

A quick & easy way to start developing an API / web service application using PHP & Apache.

**Heads Up!** Documentation is not complete. There may be some gaps in usage instructions.

---

## Requirements
  - PHP (7.3 or later)
  - Apache
    - Rewrite extension enabled
    - `.htaccess` files enabled



## Installation
This package can be installed with `composer`:
```
composer require ooobii/quick-router
```

In order to use this package, an `.htaccess` file / rewrite rules are required to defined so that all requests are processed by the `index.php` file. You can create / update one in the site's directory by executing the following PHP script:
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

ooobii\QuickRouter\Helpers\SetupHtaccess::createHtaccessFile();
```

This method will create a `.htaccess` file in your project's root folder. If one already exists, the rewrite rules that this package requires will automatically be appended to the end of it. The rules this method generates can also be defined in Apache's virtual host configuration if you'd prefer to have them there.



## Usage
### Create a Router
You can define a new router by instantiating a new `Router` class:
```php
use \ooobii\QuickRouter\Router\Router;

$router = new Router('/api/v1', TRUE);

if(!$router->process()) {
    //url provided doesn't have a route specified.
}
```
You can pass 2 arguments to the `Router` class constructor:
  - "Router Root":
    - Any URL requests that begin with the root provided will be handled by the router.
    - If the URL being requested is outside the scope of this root, the router will ignore the route.<br>
  
    For example, with the router above, the URL `http://<SITE>/v1/someEndpoint` would not be handled by this router, but `http://<SITE>/api/v1/someEnpoint` would be so long as a route is defined for it.
    <br><br>

  - "Enforce JSON Output":
    - `false` (default): allow's the handlers for each route to have full control of the output content.
    - `true`: all route handlers will attempt to be converted to JSON by passing the results of the handler through `json_encode()`.


You can also define multiple routers with different root URLs:
```php
use \ooobii\QuickRouter\Router\Router;

$router_v1 = new Router('/api/v1', TRUE);
$router_v2 = new Router('/api/v1', TRUE);

if(!$router_v1->process() && !$router_v2->process()) {
    //url provided doesn't have a route specified.
}
```

You can pass these routers by reference to an external script file to setup routes for each router. Any routers that are defined must be included in the `index.php` file.


### Define Routes in a Router
#### Error Handle Routes
Once you instantiate a router, you can add handlers for errors that are encountered by specific HTTP error codes:
```php
//define an error route for exceptions encountered during route handling.
$router->addErrorRoute(500, function($exception) {
    return [ 'error' => $exception->getMessage() ];
});

//define an error route for requests that do not have endpoints defined.
$router->addErrorRoute(404, function() {
    return [ 'error' => 'Endpoint not found.' ];
});
```

The methods above add return handlers for specific HTTP error codes. The first argument is the HTTP error code, and the second argument is a callback function that will be executed when the route is matched. The callback function will receive the exception object as its only argument.

**Note:** In this example, it is assumed that JSON output enforcement is enabled. Be sure your route handlers returns a proper echo type or echo's out a proper result based on the content being delivered. 

### Define Standard Routes

