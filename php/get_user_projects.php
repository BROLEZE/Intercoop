<?php
require_once 'config.php';

header('Content-Type: application/json');

requireLogin();

try {
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT codigo_projeto, nome_projeto, descricao_projeto, anexos, 
               data_criacao, status
        FROM projetos 
        WHERE email = ? 
        ORDER BY data_criacao DESC
    ");
    $stmt->execute([$_SESSION['user_email']]);
    $projects = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'projects' => $projects]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar projetos: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>