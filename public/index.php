<?php declare(strict_types=1);


require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load config
require_once dirname(__DIR__) . '/config.php';

// Load environment and set error reporting level
$environment = constant('VIEW_DEBUG') ? E_ALL : 0;
error_reporting($environment);

// Load application master logger
$appLog = (new \Conflabs\JsonFileViewer\AppLog())->log;

// Load routes
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {

    $r->addRoute('GET', '/', '\Conflabs\JsonFileViewer\Controllers\HomeController/index');

    $r->addRoute('GET', '/link/stats', '\Conflabs\JsonFileViewer\Controllers\StatsController/index');
});

// Fetch method and URI from server
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

// Get Route information from Dispatcher
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// Handle routes
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        (new \Conflabs\JsonFileViewer\Controllers\ErrorController())->index();
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        [$class, $method] = explode("/", $handler, 2);
        call_user_func_array([new $class, $method], $vars);
        break;
}