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
    $status = $input['status'] ?? false;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID é obrigatório']);
        exit;
    }
    
    $pdo = getConnection();
    
    // Verificar se cliente existe
    $stmt = $pdo->prepare("SELECT nome FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    $client = $stmt->fetch();
    
    if (!$client) {
        echo json_encode(['success' => false, 'message' => 'Cliente não encontrado']);
        exit;
    }
    
    // Atualizar status
    $sql = "UPDATE clientes SET ativo = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$status ? 1 : 0, $id]);
    
    if ($result) {
        $action = $status ? 'ativado' : 'desativado';
        
        // Log da atividade
        logAdminActivity('Status do cliente alterado', "Cliente {$client['nome']} (ID: {$id}) foi {$action} pelo administrador");
        
        echo json_encode([
            'success' => true, 
            'message' => "Cliente {$action} com sucesso"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao alterar status do cliente']);
    }
    
} catch (Exception $e) {
    error_log("Erro ao alterar status do cliente: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>