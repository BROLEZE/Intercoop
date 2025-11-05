<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $pdo = getConnection();
    
    // Validar dados obrigatórios
    $nome = sanitize($_POST['nome'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($nome) || empty($email) || empty($senha)) {
        echo json_encode(['success' => false, 'message' => 'Nome, email e senha são obrigatórios']);
        exit;
    }
    
    if (!isValidEmail($email)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit;
    }
    
    if (strlen($senha) < 6) {
        echo json_encode(['success' => false, 'message' => 'Senha deve ter pelo menos 6 caracteres']);
        exit;
    }
    
    // Verificar se email já existe
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email já cadastrado']);
        exit;
    }
    
    // Dados opcionais
    $empresa = sanitize($_POST['empresa'] ?? '');
    $telefone = sanitize($_POST['telefone'] ?? '');
    $endereco = sanitize($_POST['endereco'] ?? '');
    $cidade = sanitize($_POST['cidade'] ?? '');
    $estado = sanitize($_POST['estado'] ?? '');
    $pais = sanitize($_POST['pais'] ?? 'Brasil');
    $cep = sanitize($_POST['cep'] ?? '');
    
    // Hash da senha
    $senhaHash = hashPassword($senha);
    
    // Inserir cliente
    $sql = "INSERT INTO clientes (nome, email, senha, empresa, telefone, endereco, cidade, estado, pais, cep) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $nome, $email, $senhaHash, $empresa, $telefone, 
        $endereco, $cidade, $estado, $pais, $cep
    ]);
    
    if ($result) {
        // Fazer login automático
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $nome;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Cadastro realizado com sucesso',
            'redirect' => 'dashboard.html'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao realizar cadastro']);
    }
    
} catch (PDOException $e) {
    error_log("Erro no cadastro: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>