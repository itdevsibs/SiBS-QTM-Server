<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->post('/server/login', function (Request $request, Response $response, $args) {
      
    try {
    $data = $request->getParsedBody();

    $username = $data['username'];
    $password = $data['password'];

    if(empty($username) || empty($password)) {
        $resbody = ['error' => 'Username and password are required'];
        $response->getBody()->write(json_encode($resbody));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $db = getQAMDb();

    if (!$db) {
        $resbody = ['error' => 'Database connection failed'];
        $response->getBody()->write(json_encode($resbody));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    $encryptedPass = encryptPass($password);

    $query = "SELECT sibsid, email, password FROM accounts WHERE sibsid =:username1 OR email=:username2";
    $stmt = $db->prepare($query);
    $stmt->execute(['username1' => $username, 'username2' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if(empty($user) || $user['password'] !== $encryptedPass) {
        $resbody = ['error' => 'Invalid username or password', 'user' => $user, 'encryptedPass' => $encryptedPass];
        $response->getBody()->write(json_encode($resbody));
        return $response->withHeader( 'Content-Type', 'application/json')->withStatus(401);
    } 

    $resbody = [$user];
    $response->getBody()->write(json_encode($resbody));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

    } catch (Exception $e) {
        $resbody = ['error' => 'Login Failed', 'message' => $e->getMessage()];
        $response->getBody()->write(json_encode($resbody));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->post('/server/register', function (Request $request, Response $response, $args) {
    // Registration logic
    $response->getBody()->write(json_encode(['message' => 'Register endpoint']));
    return $response->withHeader('Content-Type', 'application/json');
});

//encryption function 

function encryptPass($pass) {
    $encryptionMethod = "AES-256-CBC";
    $secret_key = "FDSYF6YDSA9F8FD98F7DS98F7D9S";
    $secret_iv = "fsGZHasd0";
    $key = hash("sha256", $secret_key);
    $iv = substr(hash("sha256", $secret_iv), 0, 16);
    $encryptedPass = base64_encode(openssl_encrypt($pass, $encryptionMethod, $key, 0, $iv));

	return($encryptedPass);

}