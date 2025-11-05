<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $pdo = getConnection();
    
    $nome = sanitize($_POST['nome'] ?? '');
    $telefone = sanitize($_POST['telefone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $assunto = sanitize($_POST['assunto'] ?? '');
    $descricao = sanitize($_POST['descricao'] ?? '');
    
    // Validações
    if (empty($nome) || empty($telefone) || empty($email) || empty($assunto) || empty($descricao)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
        exit;
    }
    
    if (!isValidEmail($email)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit;
    }
    
    // Inserir contato no banco
    $sql = "INSERT INTO contatos (nome, telefone, email, assunto, descricao) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$nome, $telefone, $email, $assunto, $descricao]);
    
    if ($result) {
        // Enviar email (opcional - requer configuração SMTP)
        $emailSent = sendContactEmail($nome, $telefone, $email, $assunto, $descricao);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Mensagem enviada com sucesso! Entraremos em contato em breve.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao enviar mensagem']);
    }
    
} catch (PDOException $e) {
    error_log("Erro ao enviar contato: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}

function sendContactEmail($nome, $telefone, $email, $assunto, $descricao) {
    try {
        // Configurar headers do email
        $to = SMTP_FROM;
        $subject = "Novo contato do site - " . $assunto;
        
        $message = "
        <html>
        <head>
            <title>Novo Contato - Intercoop</title>
        </head>
        <body>
            <h2>Novo contato recebido pelo site</h2>
            <p><strong>Nome:</strong> {$nome}</p>
            <p><strong>Telefone:</strong> {$telefone}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Assunto:</strong> {$assunto}</p>
            <p><strong>Descrição:</strong></p>
            <p>{$descricao}</p>
            <hr>
            <p><small>Enviado em: " . date('d/m/Y H:i:s') . "</small></p>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $email . "\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        
        return mail($to, $subject, $message, $headers);
        
    } catch (Exception $e) {
        error_log("Erro ao enviar email: " . $e->getMessage());
        return false;
    }
}
?>