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
    $nome = sanitize($_POST['nome'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($id) || empty($nome) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'ID, nome e email são obrigatórios']);
        exit;
    }
    
    if (!isValidEmail($email)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit;
    }
    
    // Verificar se cliente existe
    $stmt = $pdo->prepare("SELECT email FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    $currentClient = $stmt->fetch();
    
    if (!$currentClient) {
        echo json_encode(['success' => false, 'message' => 'Cliente não encontrado']);
        exit;
    }
    
    // Verificar se email já existe em outro cliente
    if ($currentClient['email'] !== $email) {
        $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email já está sendo usado por outro cliente']);
            exit;
        }
    }
    
    // Dados opcionais
    $empresa = sanitize($_POST['empresa'] ?? '');
    $telefone = sanitize($_POST['telefone'] ?? '');
    
    // Atualizar cliente
    $sql = "UPDATE clientes SET nome = ?, email = ?, empresa = ?, telefone = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$nome, $email, $empresa, $telefone, $id]);
    
    if ($result) {
        // Log da atividade
        logAdminActivity('Cliente atualizado', "Cliente {$nome} (ID: {$id}) foi atualizado pelo administrador");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Cliente atualizado com sucesso'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar cliente']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao atualizar cliente: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>