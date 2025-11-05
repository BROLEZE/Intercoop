<?php
require_once 'config.php';

header('Content-Type: application/json');

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $pdo = getConnection();
    
    $nome = sanitize($_POST['nome'] ?? '');
    $empresa = sanitize($_POST['empresa'] ?? '');
    $telefone = sanitize($_POST['telefone'] ?? '');
    $endereco = sanitize($_POST['endereco'] ?? '');
    $cidade = sanitize($_POST['cidade'] ?? '');
    $estado = sanitize($_POST['estado'] ?? '');
    $pais = sanitize($_POST['pais'] ?? '');
    $cep = sanitize($_POST['cep'] ?? '');
    
    if (empty($nome)) {
        echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
        exit;
    }
    
    $sql = "UPDATE clientes SET 
            nome = ?, empresa = ?, telefone = ?, endereco = ?, 
            cidade = ?, estado = ?, pais = ?, cep = ?
            WHERE email = ?";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $nome, $empresa, $telefone, $endereco, 
        $cidade, $estado, $pais, $cep, $_SESSION['user_email']
    ]);
    
    if ($result) {
        $_SESSION['user_name'] = $nome;
        echo json_encode(['success' => true, 'message' => 'Perfil atualizado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar perfil']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao atualizar perfil: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>