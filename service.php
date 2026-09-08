<?php
header('Content-Type: application/json; charset=utf-8');

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$databaseName = "login_db";

$conn = new mysqli($servername, $dbusername, $dbpassword, $databaseName);

if ($conn->connect_error) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Adatbázis-kapcsolati hiba']));
}

$conn->set_charset('utf8mb4');

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    exit(json_encode(['success' => false, 'message' => 'Nem engedélyezett kérés']));
}

$action = $_POST['action'] ?? '';
$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if(!in_array($action, ['login', 'register'], true)){
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Ismeretlen művelet']));
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === ''){
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Hibás email vagy jelszó']));
}

if($action === 'register'){
    $name = trim($_POST['name'] ?? '');
    $newsletter = !empty($_POST['newsletter']) ? 1 : 0;

    if($name === '' || mb_strlen($name) > 100 || strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) ||
        !preg_match('/\d/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)){
        http_response_code(400);
        exit(json_encode(['success' => false, 'message' => 'A regisztrációs adatok nem megfelelőek']));
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $statement = $conn->prepare('INSERT INTO felhasznalok (name, email, password, newsletter) VALUES (?, ?, ?, ?)');
    $statement->bind_param('sssi', $name, $email, $hashedPassword, $newsletter);

    if(!$statement->execute()){
        if($statement->errno === 1062){
            http_response_code(409);
            exit(json_encode(['success' => false, 'message' => 'Ez az email már létezik']));
        }

        http_response_code(500);
        exit(json_encode(['success' => false, 'message' => 'A regisztráció nem sikerült']));
    }

    echo json_encode(['success' => true, 'message' => 'Sikeres regisztráció']);
    $statement->close();
    $conn->close();
    exit;
}

$statement = $conn->prepare('SELECT name, password FROM felhasznalok WHERE email = ? LIMIT 1');
$statement->bind_param('s', $email);
$statement->execute();
$statement->bind_result($userName, $hashedPassword);
$userFound = $statement->fetch();

if(!$userFound || !password_verify($password, $hashedPassword)){
    http_response_code(401);
    exit(json_encode(['success' => false, 'message' => 'Hibás email vagy jelszó']));
}

echo json_encode(['success' => true, 'message' => 'Üdv ' . $userName]);
$statement->close();
$conn->close();
?>
