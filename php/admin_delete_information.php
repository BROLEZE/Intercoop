<?php
require_once 'config.php';
require_once 'admin_auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID é obrigatório']);
        exit;
    }
    
    $pdo = getConnection();
    
    // Buscar informações antes de deletar
    $stmt = $pdo->prepare("
        SELECT i.informacoes, c.nome as cliente_nome 
        FROM informacoes i
        JOIN clientes c ON i.email = c.email
        WHERE i.id = ?
    ");
    $stmt->execute([$id]);
    $info = $stmt->fetch();
    
    if (!$info) {
        echo json_encode(['success' => false, 'message' => 'Informação não encontrada']);
        exit;
    }
    
    // Deletar informação
    $stmt = $pdo->prepare("DELETE FROM informacoes WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        // Log da atividade
        $preview = substr($info['informacoes'], 0, 50) . '...';
        logAdminActivity('Informação excluída', "Informação '{$preview}' do cliente {$info['cliente_nome']} foi excluída pelo administrador");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Informação excluída com sucesso'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir informação']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao excluir informação: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>