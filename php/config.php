<?php
// Configurações do banco de dados
define('DB_HOST', '');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');

// Configurações de email
define('SMTP_HOST', '');
define('SMTP_PORT', 'numeroaqui');
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_FROM', '');
define('SMTP_FROM_NAME', 'nomeaqui');

// Configurações gerais
define('SITE_URL', 'https://site.com.br');
define('UPLOAD_PATH', 'pasta');

// Iniciar sessão
session_start();

// Função para conectar ao banco de dados
function getConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erro de conexão: " . $e->getMessage());
        die("Erro de conexão com o banco de dados");
    }
}

// Função para verificar se o usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_email']);
}

// Função para redirecionar se não estiver logado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.html');
        exit;
    }
}

// Função para sanitizar dados
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Função para validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para gerar hash de senha
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Função para verificar senha
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>