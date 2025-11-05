<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $usuario = sanitize($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($usuario) || empty($senha)) {
        echo json_encode(['success' => false, 'message' => 'Usuário e senha são obrigatórios']);
        exit;
    }
    
    // Verificar credenciais do admin (você pode mudar estas credenciais)
    $adminUsers = [
        'admin' => 'admin123',
        'intercoop' => 'intercoop2025'
    ];
    
    if (isset($adminUsers[$usuario]) && $adminUsers[$usuario] === $senha) {
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_user'] = $usuario;
        $_SESSION['admin_login_time'] = time();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login realizado com sucesso'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário ou senha incorretos']);
    }
    
} catch (Exception $e) {
    error_log("Erro no login admin: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>