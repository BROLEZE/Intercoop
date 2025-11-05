<?php
require_once 'config.php';
require_once 'admin_auth.php';

header('Content-Type: application/json');

try {
    $id = intval($_GET['id'] ?? 0);
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID é obrigatório']);
        exit;
    }
    
    $pdo = getConnection();
    
    $stmt = $pdo->prepare("
        SELECT id, nome, telefone, email, assunto, descricao, data_envio, status
        FROM contatos
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $contact = $stmt->fetch();
    
    if ($contact) {
        echo json_encode([
            'success' => true,
            'contact' => $contact
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Contato não encontrado']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao buscar detalhes do contato: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>