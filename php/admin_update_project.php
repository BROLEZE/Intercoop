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
    
    $id = intval($_POST['id'] ?? 0);
    $email = sanitize($_POST['email'] ?? '');
    $codigo_projeto = sanitize($_POST['codigo_projeto'] ?? '');
    $nome_projeto = sanitize($_POST['nome_projeto'] ?? '');
    $descricao_projeto = sanitize($_POST['descricao_projeto'] ?? '');
    $anexos = sanitize($_POST['anexos'] ?? '');
    
    if (empty($id) || empty($email) || empty($codigo_projeto) || empty($nome_projeto)) {
        echo json_encode(['success' => false, 'message' => 'ID, email, código e nome do projeto são obrigatórios']);
        exit;
    }
    
    // Verificar se projeto existe
    $stmt = $pdo->prepare("SELECT codigo_projeto, email FROM projetos WHERE id = ?");
    $stmt->execute([$id]);
    $currentProject = $stmt->fetch();
    
    if (!$currentProject) {
        echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
        exit;
    }
    
    // Verificar se cliente existe
    $stmt = $pdo->prepare("SELECT nome FROM clientes WHERE email = ? AND ativo = 1");
    $stmt->execute([$email]);
    $client = $stmt->fetch();
    
    if (!$client) {
        echo json_encode(['success' => false, 'message' => 'Cliente não encontrado ou inativo']);
        exit;
    }
    
    // Verificar se código do projeto já existe para outro projeto do mesmo cliente
    if ($currentProject['codigo_projeto'] !== $codigo_projeto || $currentProject['email'] !== $email) {
        $stmt = $pdo->prepare("SELECT id FROM projetos WHERE email = ? AND codigo_projeto = ? AND id != ?");
        $stmt->execute([$email, $codigo_projeto, $id]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Código do projeto já existe para este cliente']);
            exit;
        }
    }
    
    // Atualizar projeto
    $sql = "UPDATE projetos SET email = ?, codigo_projeto = ?, nome_projeto = ?, descricao_projeto = ?, anexos = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$email, $codigo_projeto, $nome_projeto, $descricao_projeto, $anexos, $id]);
    
    if ($result) {
        // Log da atividade
        logAdminActivity('Projeto atualizado', "Projeto {$nome_projeto} (ID: {$id}) foi atualizado pelo administrador");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Projeto atualizado com sucesso'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar projeto']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao atualizar projeto: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>