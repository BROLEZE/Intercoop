<?php
require_once 'config.php';
require_once 'admin_auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $pdo = getConnection();
    
    $email = sanitize($_POST['email'] ?? '');
    $informacoes = sanitize($_POST['informacoes'] ?? '');
    
    if (empty($email) || empty($informacoes)) {
        echo json_encode(['success' => false, 'message' => 'Email e informações são obrigatórios']);
        exit;
    }
    
    // Verificar se cliente existe
    $stmt = $pdo->prepare("SELECT nome FROM clientes WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $cliente = $stmt->fetch();
    
    if (!$cliente) {
        echo json_encode(['success' => false, 'message' => 'Cliente não encontrado ou inativo']);
        exit;
    }
    
    // Inserir informação
    $sql = "INSERT INTO informacoes (email, informacoes) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$email, $informacoes]);
    
    if ($result) {
        // Log da atividade
        logAdminActivity('Informação adicionada', "Nova informação foi adicionada para {$cliente['nome']} ({$email})");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Informação adicionada com sucesso'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar informação']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao adicionar informação: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>