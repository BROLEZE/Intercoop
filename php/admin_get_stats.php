<?php
require_once 'config.php';
require_once 'admin_auth.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    // Total de clientes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes WHERE ativo = 1");
    $totalClientes = $stmt->fetch()['total'];
    
    // Total de projetos ativos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM projetos WHERE status = 'ativo'");
    $totalProjetos = $stmt->fetch()['total'];
    
    // Total de contatos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM contatos");
    $totalContatos = $stmt->fetch()['total'];
    
    // Novos clientes este mês
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes WHERE MONTH(data_cadastro) = MONTH(CURRENT_DATE()) AND YEAR(data_cadastro) = YEAR(CURRENT_DATE())");
    $novosMes = $stmt->fetch()['total'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_clientes' => $totalClientes,
            'total_projetos' => $totalProjetos,
            'total_contatos' => $totalContatos,
            'novos_mes' => $novosMes
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>