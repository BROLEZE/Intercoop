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
    
    // Buscar informações do projeto antes de deletar
    $stmt = $pdo->prepare("SELECT nome_projeto, codigo_projeto FROM projetos WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
    
    if (!$project) {
        echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
        exit;
    }
    
    // Deletar projeto
    $stmt = $pdo->prepare("DELETE FROM projetos WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if ($result) {
        // Log da atividade
        logAdminActivity('Projeto excluído', "Projeto {$project['nome_projeto']} ({$project['codigo_projeto']}) foi excluído pelo administrador");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Projeto excluído com sucesso'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir projeto']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao excluir projeto: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>