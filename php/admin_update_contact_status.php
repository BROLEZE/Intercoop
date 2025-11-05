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
    $status = sanitize($input['status'] ?? '');
    
    if (empty($id) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'ID e status são obrigatórios']);
        exit;
    }
    
    // Validar status
    $validStatuses = ['novo', 'lido', 'respondido'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Status inválido']);
        exit;
    }
    
    $pdo = getConnection();
    
    // Verificar se contato existe
    $stmt = $pdo->prepare("SELECT nome, assunto FROM contatos WHERE id = ?");
    $stmt->execute([$id]);
    $contact = $stmt->fetch();
    
    if (!$contact) {
        echo json_encode(['success' => false, 'message' => 'Contato não encontrado']);
        exit;
    }
    
    // Atualizar status
    $sql = "UPDATE contatos SET status = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$status, $id]);
    
    if ($result) {
        // Log da atividade
        logAdminActivity('Status do contato alterado', "Contato de {$contact['nome']} - '{$contact['assunto']}' foi marcado como {$status}");
        
        echo json_encode([
            'success' => true, 
            'message' => "Contato marcado como {$status} com sucesso"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status do contato']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao atualizar status do contato: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>