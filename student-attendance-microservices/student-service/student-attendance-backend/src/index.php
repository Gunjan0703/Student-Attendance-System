<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Create App
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Database connection function
function getDB() {
    $host = $_ENV['DB_HOST'];
    $db   = $_ENV['MYSQL_NAME'];
    $user = $_ENV['MYSQL_USER'];
    $pass = $_ENV['MYSQL_PASSWORD'];
    $charset = 'utf8mb4';

    $dsn = "mysql:host=mysql;dbname=attendancemsystem;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $user, $pass, $options);
}

// --- JWT Middleware ---
$jwtMiddleware = function (Request $request, RequestHandler $handler): Response {
    $authHeader = $request->getHeaderLine('Authorization');

    if (!$authHeader) {
        $response = new Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => 'Authorization header required']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    $token = str_replace('Bearer ', '', $authHeader);

    try {
        $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
        $request = $request->withAttribute('user', $decoded);
        return $handler->handle($request);
    } catch (Exception $e) {
        $response = new Slim\Psr7\Response();
        $response->getBody()->write(json_encode(['error' => 'Invalid token']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
};

// --- Auth route ---
$app->post('/login', function(Request $request, Response $response) {
    $data = $request->getParsedBody();
    $username = trim($data['username'] ?? '');
    $password = $data['password'] ?? '';
    $role = strtolower(trim($data['role'] ?? ''));

    if (!$username || !$password || !$role) {
        $response->getBody()->write(json_encode(['error' => 'Missing credentials']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $db = getDB();
    $tableMap = [
        'admin' => ['table' => 'tbladmin', 'column' => 'emailAddress'],
        'teacher' => ['table' => 'tblclassteacher', 'column' => 'emailAddress'],
        'student' => ['table' => 'tblstudents', 'column' => 'admissionNumber']
    ];

    if (!isset($tableMap[$role])) {
        $response->getBody()->write(json_encode(['error' => 'Invalid role']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $table = $tableMap[$role]['table'];
    $column = $tableMap[$role]['column'];

    $stmt = $db->prepare("SELECT * FROM $table WHERE $column=? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Password verification
    $storedPassword = $user['password'];
    $isValid = password_verify($password, $storedPassword); // Works for all roles if hashed

    if (!$isValid) {
        $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    // Generate JWT token
    $payload = [
        'sub' => $user['Id'],
        'role' => $role,
        'iat' => time(),
        'exp' => time() + 3600
    ];

    $token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

    $response->getBody()->write(json_encode(['token' => $token]));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});



// --- Protected Routes ---
// Get Students
$app->get('/students', function(Request $request, Response $response) {
    $db = getDB();
    $stmt = $db->query("SELECT id, name, email FROM tblstudents");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($students));
    return $response->withHeader('Content-Type', 'application/json');
})->add($jwtMiddleware);

// Get Attendance
$app->get('/attendance', function(Request $request, Response $response) {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM attendance");
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($attendance));
    return $response->withHeader('Content-Type', 'application/json');
})->add($jwtMiddleware);

$app->run();
