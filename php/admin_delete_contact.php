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
    
    // Buscar informações do contato antes de deletar
    $stmt = $pdo->prepare("SELECT nome, email, assunto FROM contatos WHERE id = ?");
    $stmt->execute([$id]);
    $contact = $stmt->fetch();
    
    if (!$contact) {
        echo json_encode(['success' => false, 'message' => 'Contato não encontrado']);
        exit;
    }
    
    // Deletar contato
    $stmt = $pdo->prepare("DELETE FROM contatos WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        // Log da atividade
        logAdminActivity('Contato excluído', "Contato de {$contact['nome']} ({$contact['email']}) - '{$contact['assunto']}' foi excluído pelo administrador");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Contato excluído com sucesso'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir contato']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao excluir contato: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>