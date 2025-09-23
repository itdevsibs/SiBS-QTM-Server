<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/server/test', function (Request $request, Response $response, $args) {
    $response->getBody()->write(json_encode(['message' => 'API is working!']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/server/health', function (Request $request, Response $response, $args) {
    $response->getBody()->write(json_encode(['status' => 'healthy', 'timestamp' => time()]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Add this new route to test database connections
$app->get('/server/db-status', function (Request $request, Response $response, $args) {
    $kronosDB = getKronosDB();
    $qamDB = getQamDB();
    
    $result = [
        'kronos_connected' => $kronosDB !== null,
        'qam_connected' => $qamDB !== null,
        'both_connected' => isDatabaseConnected(),
        'timestamp' => time()
    ];
    
    // Test actual queries if connections exist
    if ($kronosDB) {
        try {
            $stmt = $kronosDB->query("SELECT 1");
            $result['kronos_query_test'] = 'success';
        } catch (PDOException $e) {
            $result['kronos_query_test'] = 'failed: ' . $e->getMessage();
        }
    }
    
    if ($qamDB) {
        try {
            $stmt = $qamDB->query("SELECT 1");
            $result['qam_query_test'] = 'success';
        } catch (PDOException $e) {
            $result['qam_query_test'] = 'failed: ' . $e->getMessage();
        }
    }
    
    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/server/db-debug', function (Request $request, Response $response, $args) {
    $result = ['debug' => 'Starting connection test'];
    
    try {
        // Test Kronos connection
        $kronosDB = new PDO(
            "mysql:host=172.18.0.164;port=3306;dbname=kronos_testdb;charset=utf8mb4",
            "sibssoftdev",
            "sibssoftdev"
        );
        $result['kronos_test'] = 'success';
    } catch (PDOException $e) {
        $result['kronos_error'] = $e->getMessage();
    }
    
    try {
        // Test QAM connection
        $qamDB = new PDO(
            "mysql:host=172.18.0.164;port=3306;dbname=qam_testdb;charset=utf8mb4",
            "sibssoftdev",
            "sibssoftdev"
        );
        $result['qam_test'] = 'success';
    } catch (PDOException $e) {
        $result['qam_error'] = $e->getMessage();
    }
    
    $response->getBody()->write(json_encode($result));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/server/php-info', function (Request $request, Response $response, $args) {
    $result = [
        'php_version' => phpversion(),
        'loaded_extensions' => get_loaded_extensions(),
        'pdo_available' => extension_loaded('pdo'),
        'pdo_mysql_available' => extension_loaded('pdo_mysql'),
        'mysql_available' => extension_loaded('mysql'),
        'mysqli_available' => extension_loaded('mysqli')
    ];
    
    $response->getBody()->write(json_encode($result, JSON_PRETTY_PRINT));
    return $response->withHeader('Content-Type', 'application/json');
});