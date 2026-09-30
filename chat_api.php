<?php
require_once __DIR__ . '/db.php';

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

start_app_session();
$action = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['action'] ?? '')
    : ($_GET['action'] ?? 'messages');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $cookie['path'],
            'domain' => $cookie['domain'],
            'secure' => $cookie['secure'],
            'httponly' => $cookie['httponly'],
            'samesite' => 'Lax',
        ]);
    }
    session_destroy();
    respond(200, ['success' => true]);
}

if (empty($_SESSION['user_id']) || empty($_SESSION['user_name'])) {
    respond(401, ['success' => false, 'message' => 'Jelentkezz be a chat használatához.']);
}

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    respond(405, ['success' => false, 'message' => 'Nem engedélyezett kérés.']);
}

try {
    $connection = connect_database();
} catch (RuntimeException $error) {
    respond(500, ['success' => false, 'message' => $error->getMessage()]);
}

$schemaReady = $connection->query(
    'CREATE TABLE IF NOT EXISTS chat_messages (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        user_name VARCHAR(100) NOT NULL,
        message VARCHAR(1000) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_chat_messages_created (created_at),
        CONSTRAINT fk_chat_messages_user
            FOREIGN KEY (user_id) REFERENCES felhasznalok (id)
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci'
);

if (!$schemaReady) {
    $connection->close();
    respond(500, ['success' => false, 'message' => 'Nem sikerült előkészíteni az üzenetek adatbázistábláját. Ellenőrizd a login_db adatbázist és a felhasznalok táblát.']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'messages') {
    $afterId = max(0, (int) ($_GET['after'] ?? 0));

    if ($afterId > 0) {
        $statement = $connection->prepare(
            'SELECT id, user_id, user_name, message, created_at
             FROM chat_messages WHERE id > ? ORDER BY id ASC LIMIT 100'
        );
        if ($statement) {
            $statement->bind_param('i', $afterId);
        }
    } else {
        $statement = $connection->prepare(
            'SELECT id, user_id, user_name, message, created_at
             FROM (SELECT id, user_id, user_name, message, created_at
                   FROM chat_messages ORDER BY id DESC LIMIT 50) recent
             ORDER BY id ASC'
        );
    }

    if (!$statement || !$statement->execute()) {
        $connection->close();
        respond(500, ['success' => false, 'message' => 'Az üzenetek betöltése nem sikerült.']);
    }

    $result = $statement->get_result();
    $messages = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $statement->close();
    $connection->close();

    respond(200, [
        'success' => true,
        'user' => [
            'id' => (int) $_SESSION['user_id'],
            'name' => (string) $_SESSION['user_name'],
        ],
        'messages' => $messages,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'send') {
    $text = trim($_POST['message'] ?? '');

    if ($text === '' || mb_strlen($text, 'UTF-8') > 1000) {
        respond(400, ['success' => false, 'message' => 'Az üzenet 1 és 1000 karakter közötti lehet.']);
    }

    $userId = (int) $_SESSION['user_id'];
    $userName = (string) $_SESSION['user_name'];
    $statement = $connection->prepare(
        'INSERT INTO chat_messages (user_id, user_name, message) VALUES (?, ?, ?)'
    );
    if (!$statement) {
        $connection->close();
        respond(500, ['success' => false, 'message' => 'Az üzenetek adatbázistáblája nem érhető el.']);
    }
    $statement->bind_param('iss', $userId, $userName, $text);

    if (!$statement->execute()) {
        respond(500, ['success' => false, 'message' => 'Az üzenet elküldése nem sikerült.']);
    }

    $messageId = $statement->insert_id;
    $statement->close();
    $connection->close();
    respond(201, ['success' => true, 'id' => $messageId]);
}

$connection->close();
respond(400, ['success' => false, 'message' => 'Ismeretlen művelet.']);
