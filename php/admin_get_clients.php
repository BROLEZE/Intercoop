<?php
require_once 'config.php';
require_once 'admin_auth.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    $stmt = $pdo->query("
        SELECT id, nome, email, empresa, telefone, endereco, cidade, estado, pais, cep, 
               data_cadastro, ativo 
        FROM clientes 
        ORDER BY data_cadastro DESC
    ");
    $clients = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'clients' => $clients
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar clientes: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>