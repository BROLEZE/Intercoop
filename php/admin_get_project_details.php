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
        SELECT p.*, c.nome as cliente_nome
        FROM projetos p
        JOIN clientes c ON p.email = c.email
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
    
    if ($project) {
        echo json_encode([
            'success' => true,
            'project' => $project
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao buscar detalhes do projeto: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>