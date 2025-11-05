<?php
// Script para administrador adicionar projetos aos clientes
require_once 'config.php';

// ATENÇÃO: Este arquivo deve ser protegido ou removido após uso
// Apenas para demonstração - implementar autenticação de admin

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getConnection();
        
        $email = sanitize($_POST['email'] ?? '');
        $codigo_projeto = sanitize($_POST['codigo_projeto'] ?? '');
        $nome_projeto = sanitize($_POST['nome_projeto'] ?? '');
        $descricao_projeto = sanitize($_POST['descricao_projeto'] ?? '');
        $anexos = sanitize($_POST['anexos'] ?? '');
        
        // Verificar se cliente existe
        $stmt = $pdo->prepare("SELECT email FROM clientes WHERE email = ?");
        $stmt->execute([$email]);
        
        if (!$stmt->fetch()) {
            echo "Cliente não encontrado!";
            exit;
        }
        
        // Inserir projeto
        $sql = "INSERT INTO projetos (email, codigo_projeto, nome_projeto, descricao_projeto, anexos) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$email, $codigo_projeto, $nome_projeto, $descricao_projeto, $anexos]);
        
        if ($result) {
            echo "Projeto adicionado com sucesso!";
        } else {
            echo "Erro ao adicionar projeto!";
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
    <title>Adicionar Projeto - Admin</title>
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
    <h2>Adicionar Projeto ao Cliente</h2>
    
    <form method="POST">
        <div class="form-group">
            <label for="email">Email do Cliente:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="codigo_projeto">Código do Projeto:</label>
            <input type="text" id="codigo_projeto" name="codigo_projeto" required>
        </div>
        
        <div class="form-group">
            <label for="nome_projeto">Nome do Projeto:</label>
            <input type="text" id="nome_projeto" name="nome_projeto" required>
        </div>
        
        <div class="form-group">
            <label for="descricao_projeto">Descrição do Projeto:</label>
            <textarea id="descricao_projeto" name="descricao_projeto" rows="4"></textarea>
        </div>
        
        <div class="form-group">
            <label for="anexos">Anexos:</label>
            <input type="text" id="anexos" name="anexos" placeholder="URLs ou nomes dos arquivos">
        </div>
        
        <button type="submit">Adicionar Projeto</button>
    </form>
</body>
</html>