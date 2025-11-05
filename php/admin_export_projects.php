<?php
require_once 'config.php';
require_once 'admin_auth.php';

try {
    $pdo = getConnection();
    
    // Buscar todos os projetos
    $stmt = $pdo->query("
        SELECT p.id, p.codigo_projeto, p.nome_projeto, p.descricao_projeto, 
               p.anexos, p.data_criacao, p.status, c.nome as cliente_nome, p.email
        FROM projetos p
        JOIN clientes c ON p.email = c.email
        ORDER BY p.data_criacao DESC
    ");
    $projects = $stmt->fetchAll();
    
    // Configurar headers para download CSV
    $filename = 'projetos_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Criar arquivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalhos
    fputcsv($output, [
        'ID',
        'Código Projeto',
        'Nome Projeto',
        'Descrição',
        'Cliente',
        'Email Cliente',
        'Status',
        'Anexos',
        'Data Criação'
    ], ';');
    
    // Dados
    foreach ($projects as $project) {
        fputcsv($output, [
            $project['id'],
            $project['codigo_projeto'],
            $project['nome_projeto'],
            $project['descricao_projeto'],
            $project['cliente_nome'],
            $project['email'],
            $project['status'],
            $project['anexos'],
            date('d/m/Y H:i:s', strtotime($project['data_criacao']))
        ], ';');
    }
    
    fclose($output);
    
    // Log da atividade
    logAdminActivity('Relatório exportado', 'Relatório de projetos foi exportado pelo administrador');
    
} catch (PDOException $e) {
    error_log("Erro ao exportar projetos: " . $e->getMessage());
    die('Erro ao gerar relatório');
}
?>