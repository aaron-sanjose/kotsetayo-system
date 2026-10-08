<?php
/**
 * KotseTayo - Central Database Connection
 *
 * Uses PDO with prepared statements.
 * NOTE: For XAMPP default configuration user is 'root' with an empty password.
 * Update these values if your MySQL setup differs.
 */

// Do not expose raw errors/warnings to users; log them instead.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

define('DB_HOST', 'localhost');
define('DB_NAME', 'kotsetayo');
define('DB_USER', 'root');
define('DB_PASS', '');

define('DB_CHARSET', 'utf8mb4');

// Site base URL (used when generating absolute paths). Auto-detected.
function base_url() {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme  = $isHttps ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir     = rtrim(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME'] ?? '/'), '/');
    return $scheme . '://' . $host . $dir;
}

/**
 * Absolute URI for the project root (e.g. "/kotsetayo").
 * Used so asset/image paths work from any folder depth (root, admin, etc.).
 */
function project_uri(): string {
    static $uri = null;
    if ($uri === null) {
        $projectDir = str_replace('\\', '/', realpath(dirname(__DIR__))); // project root
        $docRoot    = rtrim(str_replace('\\', '/', (string)($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
        if ($docRoot !== '' && strpos($projectDir, $docRoot) === 0) {
            $uri = '/' . ltrim(substr($projectDir, strlen($docRoot)), '/');
        } else {
            $uri = '';
        }
    }
    return $uri;
}

// Ensure output escaping everywhere.
function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Reusable global PDO connection (singleton).
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Do NOT expose the real error to the user.
            http_response_code(500);
            die('We are unable to reach the database right now. Please check your connection settings and try again.');
        }
    }
    return $pdo;
}

/**
 * Shortcut for running a prepared statement.
 * @return PDOStatement
 */
function query(string $sql, array $params = []): PDOStatement {
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log('SQL Error: ' . $e->getMessage());
        // Friendly message only.
        die('Something went wrong while processing your request. Please try again.');
    }
}

function fetch_all(string $sql, array $params = []): array {
    return query($sql, $params)->fetchAll();
}

function fetch_one(string $sql, array $params = []): ?array {
    $row = query($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function insert_id(): int {
    return (int)db()->lastInsertId();
}