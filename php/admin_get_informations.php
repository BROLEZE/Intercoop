<?php
require_once 'config.php';
require_once 'admin_auth.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    $stmt = $pdo->query("
        SELECT i.id, i.informacoes, i.data_criacao, c.nome as cliente_nome, i.email
        FROM informacoes i
        JOIN clientes c ON i.email = c.email
        ORDER BY i.data_criacao DESC
    ");
    $informations = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'informations' => $informations
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar informações: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>