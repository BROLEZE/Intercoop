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
    $codigo_projeto = sanitize($_POST['codigo_projeto'] ?? '');
    $nome_projeto = sanitize($_POST['nome_projeto'] ?? '');
    $descricao_projeto = sanitize($_POST['descricao_projeto'] ?? '');
    $anexos = sanitize($_POST['anexos'] ?? '');
    
    if (empty($email) || empty($codigo_projeto) || empty($nome_projeto)) {
        echo json_encode(['success' => false, 'message' => 'Email, código e nome do projeto são obrigatórios']);
        exit;
    }
    
    // Verificar se cliente existe
    $stmt = $pdo->prepare("SELECT email FROM clientes WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Cliente não encontrado ou inativo']);
        exit;
    }
    
    // Verificar se código do projeto já existe para este cliente
    $stmt = $pdo->prepare("SELECT id FROM projetos WHERE email = ? AND codigo_projeto = ?");
    $stmt->execute([$email, $codigo_projeto]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Código do projeto já existe para este cliente']);
        exit;
    }
    
    // Inserir projeto
    $sql = "INSERT INTO projetos (email, codigo_projeto, nome_projeto, descricao_projeto, anexos) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$email, $codigo_projeto, $nome_projeto, $descricao_projeto, $anexos]);
    
    if ($result) {
        // Log da atividade
        logAdminActivity('Projeto adicionado', "Projeto {$nome_projeto} ({$codigo_projeto}) foi adicionado para {$email}");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Projeto adicionado com sucesso'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar projeto']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao adicionar projeto: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>