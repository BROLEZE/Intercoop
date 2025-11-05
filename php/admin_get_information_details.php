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
        SELECT i.*, c.nome as cliente_nome
        FROM informacoes i
        JOIN clientes c ON i.email = c.email
        WHERE i.id = ?
    ");
    $stmt->execute([$id]);
    $information = $stmt->fetch();
    
    if ($information) {
        echo json_encode([
            'success' => true,
            'information' => $information
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Informação não encontrada']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao buscar detalhes da informação: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>