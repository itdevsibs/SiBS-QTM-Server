<?php 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/server/emplist', function (Request $request, Response $response, $args) {
    try {
        // Use your database connection function
        $db = getKronosDB(); // or getQamDB() depending on where gy_employee table is
        
        if (!$db) {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $sql = "SELECT * FROM gy_employee LIMIT 10";
        $stmt = $db->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_OBJ);

        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
        
    } catch (PDOException $e) {
        $error = ['error' => 'Database query failed', 'message' => $e->getMessage()];
        $response->getBody()->write(json_encode($error));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


?>