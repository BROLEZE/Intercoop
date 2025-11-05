<?php
require_once 'config.php';

header('Content-Type: application/json');

requireLogin();

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT nome, email, empresa, telefone, endereco, cidade, estado, pais, cep 
        FROM clientes 
        WHERE email = ?
    ");
    $stmt->execute([$_SESSION['user_email']]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao buscar dados do usuário: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>