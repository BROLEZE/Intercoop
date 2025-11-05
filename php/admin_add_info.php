<?php
// Script para administrador adicionar informações aos clientes
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getConnection();
        
        $email = sanitize($_POST['email'] ?? '');
        $informacoes = sanitize($_POST['informacoes'] ?? '');
        
        // Verificar se cliente existe
        $stmt = $pdo->prepare("SELECT email FROM clientes WHERE email = ?");
        $stmt->execute([$email]);
        
        if (!$stmt->fetch()) {
            echo "Cliente não encontrado!";
            exit;
        }
        
        // Inserir informação
        $sql = "INSERT INTO informacoes (email, informacoes) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$email, $informacoes]);
        
        if ($result) {
            echo "Informação adicionada com sucesso!";
        } else {
            echo "Erro ao adicionar informação!";
        }
        
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Informação - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #2c5530; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #1e3a21; }
    </style>
</head>
<body>
    <h2>Adicionar Informação ao Cliente</h2>
    
    <form method="POST">
        <div class="form-group">
            <label for="email">Email do Cliente:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="informacoes">Informações:</label>
            <textarea id="informacoes" name="informacoes" rows="6" required></textarea>
        </div>
        
        <button type="submit">Adicionar Informação</button>
    </form>
</body>
</html>