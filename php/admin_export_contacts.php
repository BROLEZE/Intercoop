<?php
require_once 'config.php';
require_once 'admin_auth.php';

try {
    $pdo = getConnection();
    
    // Buscar todos os contatos
    $stmt = $pdo->query("
        SELECT id, nome, telefone, email, assunto, descricao, data_envio, status
        FROM contatos
        ORDER BY data_envio DESC
    ");
    $contacts = $stmt->fetchAll();
    
    // Configurar headers para download CSV
    $filename = 'contatos_' . date('Y-m-d_H-i-s') . '.csv';
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
        'Telefone',
        'Email',
        'Assunto',
        'Descrição',
        'Data Envio',
        'Status'
    ], ';');
    
    // Dados
    foreach ($contacts as $contact) {
        fputcsv($output, [
            $contact['id'],
            $contact['nome'],
            $contact['telefone'],
            $contact['email'],
            $contact['assunto'],
            $contact['descricao'],
            date('d/m/Y H:i:s', strtotime($contact['data_envio'])),
            $contact['status']
        ], ';');
    }
    
    fclose($output);
    
    // Log da atividade
    logAdminActivity('Relatório exportado', 'Relatório de contatos foi exportado pelo administrador');
    
} catch (PDOException $e) {
    error_log("Erro ao exportar contatos: " . $e->getMessage());
    die('Erro ao gerar relatório');
}
?>