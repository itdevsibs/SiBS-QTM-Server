<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/database/dbconnect.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Create App
$app = AppFactory::create();

// enable JSON / form body parsing
$app->addBodyParsingMiddleware();


// Add CORS middleware
$app->add(function ($request, $handler) {

    $origin = $request->getHeaderLine('Origin'); // get the Origin header/URL from the request
    $allowedOrigins = [
        'http://172.18.0.164:8081' /*docker prod URL*/,
        'http://localhost:5173' /*reactJS default dev URL*/,
        'https://testreact.getleadsource.com' /* staging URL */
    ]; //allowed origins

    $isOptions = strtoupper($request->getMethod()) === 'OPTIONS';
    $response = $isOptions ? new \Slim\Psr7\Response(204) : $handler->handle($request);

    if(in_array($origin, $allowedOrigins, true)) {
        $response = $response
        ->withHeader('Access-Control-Allow-Origin', $origin)        
        ->withHeader('Access-Control-Allow-Credentials', 'true');
    }

    $response = $response
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Expose-Headers', 'Authorization')
        ->withHeader('Vary', 'Origin');

    // Cache preflight response for 1 day unless in development or dockerprod environment
    if ($_ENV['app_env'] !== 'development' && $_ENV['app_env'] !== 'dockerprod') {
        $response = $response->withHeader('Access-Control-Max-Age', '86400');
    }

    return $response;
});

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Handle preflight requests
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->get('/favicon.ico', function (Request $request, Response $response, $args) {
    return $response->withStatus(204);
});

require __DIR__ . '/api/login.php';
require __DIR__ . '/api/test.php';
require __DIR__ . '/api/users.php';


$app->run();