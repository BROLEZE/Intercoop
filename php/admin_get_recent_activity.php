<?php
require_once 'config.php';
require_once 'admin_auth.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    // Buscar atividades recentes (últimos 10 registros)
    $activities = [];
    
    // Novos clientes (últimos 5)
    $stmt = $pdo->query("
        SELECT nome, data_cadastro, 'cliente' as tipo 
        FROM clientes 
        ORDER BY data_cadastro DESC 
        LIMIT 5
    ");
    $newClients = $stmt->fetchAll();
    
    foreach ($newClients as $client) {
        $activities[] = [
            'titulo' => 'Novo Cliente',
            'descricao' => "Cliente {$client['nome']} se cadastrou",
            'data' => $client['data_cadastro'],
            'tipo' => 'cliente'
        ];
    }
    
    // Novos contatos (últimos 5)
    $stmt = $pdo->query("
        SELECT nome, assunto, data_envio 
        FROM contatos 
        ORDER BY data_envio DESC 
        LIMIT 5
    ");
    $newContacts = $stmt->fetchAll();
    
    foreach ($newContacts as $contact) {
        $activities[] = [
            'titulo' => 'Novo Contato',
            'descricao' => "Contato de {$contact['nome']} - {$contact['assunto']}",
            'data' => $contact['data_envio'],
            'tipo' => 'contato'
        ];
    }
    
    // Novos projetos (últimos 5)
    $stmt = $pdo->query("
        SELECT p.nome_projeto, c.nome as cliente_nome, p.data_criacao 
        FROM projetos p 
        JOIN clientes c ON p.email = c.email 
        ORDER BY p.data_criacao DESC 
        LIMIT 5
    ");
    $newProjects = $stmt->fetchAll();
    
    foreach ($newProjects as $project) {
        $activities[] = [
            'titulo' => 'Novo Projeto',
            'descricao' => "Projeto {$project['nome_projeto']} adicionado para {$project['cliente_nome']}",
            'data' => $project['data_criacao'],
            'tipo' => 'projeto'
        ];
    }
    
    // Ordenar por data (mais recente primeiro)
    usort($activities, function($a, $b) {
        return strtotime($b['data']) - strtotime($a['data']);
    });
    
    // Pegar apenas os 10 mais recentes
    $activities = array_slice($activities, 0, 10);
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar atividades: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>