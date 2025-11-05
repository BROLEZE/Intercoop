<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $pdo = getConnection();
    
    $email = sanitize($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        echo json_encode(['success' => false, 'message' => 'Email e senha são obrigatórios']);
        exit;
    }
    
    if (!isValidEmail($email)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit;
    }
    
    // Buscar usuário
    $stmt = $pdo->prepare("SELECT id, nome, email, senha, ativo FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email ou senha incorretos']);
        exit;
    }
    
    if (!$user['ativo']) {
        echo json_encode(['success' => false, 'message' => 'Conta desativada. Entre em contato conosco']);
        exit;
    }
    
    // Verificar senha
    if (!verifyPassword($senha, $user['senha'])) {
        echo json_encode(['success' => false, 'message' => 'Email ou senha incorretos']);
        exit;
    }
    
    // Criar sessão
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['nome'];
    
    echo json_encode([
        'success' => true, 
        'message' => 'Login realizado com sucesso',
        'redirect' => 'dashboard.html'
    ]);
    
} catch (PDOException $e) {
    error_log("Erro no login: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>