<?php
require_once 'config.php';
require_once 'admin_auth.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    $stmt = $pdo->query("
        SELECT id, nome, telefone, email, assunto, descricao, data_envio, status
        FROM contatos
        ORDER BY data_envio DESC
    ");
    $contacts = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'contacts' => $contacts
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar contatos: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>