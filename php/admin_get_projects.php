<?php
require_once 'config.php';
require_once 'admin_auth.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    $stmt = $pdo->query("
        SELECT p.id, p.codigo_projeto, p.nome_projeto, p.descricao_projeto, 
               p.anexos, p.data_criacao, p.status, c.nome as cliente_nome, p.email
        FROM projetos p
        JOIN clientes c ON p.email = c.email
        ORDER BY p.data_criacao DESC
    ");
    $projects = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'projects' => $projects
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar projetos: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>