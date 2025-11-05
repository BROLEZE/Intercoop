<?php
require_once 'config.php';

header('Content-Type: application/json');

requireLogin();

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT informacoes, data_criacao
        FROM informacoes 
        WHERE email = ? 
        ORDER BY data_criacao DESC
    ");
    $stmt->execute([$_SESSION['user_email']]);
    $informations = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'informations' => $informations]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar informações: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>