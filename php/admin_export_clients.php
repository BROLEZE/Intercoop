<?php
require_once 'config.php';
require_once 'admin_auth.php';

try {
    $pdo = getConnection();
    
    // Buscar todos os clientes
    $stmt = $pdo->query("
        SELECT id, nome, email, empresa, telefone, endereco, cidade, estado, pais, cep, 
               data_cadastro, CASE WHEN ativo = 1 THEN 'Ativo' ELSE 'Inativo' END as status
        FROM clientes 
        ORDER BY data_cadastro DESC
    ");
    $clients = $stmt->fetchAll();
    
    // Configurar headers para download CSV
    $filename = 'clientes_' . date('Y-m-d_H-i-s') . '.csv';
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
        'Nome',
        'Email',
        'Empresa',
        'Telefone',
        'Endereço',
        'Cidade',
        'Estado',
        'País',
        'CEP',
        'Data Cadastro',
        'Status'
    ], ';');
    
    // Dados
    foreach ($clients as $client) {
        fputcsv($output, [
            $client['id'],
            $client['nome'],
            $client['email'],
            $client['empresa'],
            $client['telefone'],
            $client['endereco'],
            $client['cidade'],
            $client['estado'],
            $client['pais'],
            $client['cep'],
            date('d/m/Y H:i:s', strtotime($client['data_cadastro'])),
            $client['status']
        ], ';');
    }
    
    fclose($output);
    
    // Log da atividade
    logAdminActivity('Relatório exportado', 'Relatório de clientes foi exportado pelo administrador');
    
} catch (PDOException $e) {
    error_log("Erro ao exportar clientes: " . $e->getMessage());
    die('Erro ao gerar relatório');
}
?>