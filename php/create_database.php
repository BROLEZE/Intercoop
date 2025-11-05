<?php
require_once 'config.php';

try {
    $pdo = getConnection();
    
    // Criar tabela de clientes
    $sql_clientes = "
    CREATE TABLE IF NOT EXISTS clientes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        endereco VARCHAR(255),
        cidade VARCHAR(100),
        estado VARCHAR(50),
        pais VARCHAR(100) DEFAULT 'Brasil',
        cep VARCHAR(20),
        telefone VARCHAR(20),
        empresa VARCHAR(255),
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ativo BOOLEAN DEFAULT TRUE
    )";
    
    // Criar tabela de informações
    $sql_informacoes = "
    CREATE TABLE IF NOT EXISTS informacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        informacoes TEXT NOT NULL,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (email) REFERENCES clientes(email) ON DELETE CASCADE
    )";
    
    // Criar tabela de projetos
    $sql_projetos = "
    CREATE TABLE IF NOT EXISTS projetos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        codigo_projeto VARCHAR(100) NOT NULL,
        nome_projeto VARCHAR(255) NOT NULL,
        descricao_projeto TEXT,
        anexos TEXT,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status ENUM('ativo', 'concluido', 'pausado') DEFAULT 'ativo',
        FOREIGN KEY (email) REFERENCES clientes(email) ON DELETE CASCADE,
        UNIQUE KEY unique_codigo_email (codigo_projeto, email)
    )";
    
    // Criar tabela de contatos (para formulário de contato)
    $sql_contatos = "
    CREATE TABLE IF NOT EXISTS contatos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        telefone VARCHAR(20) NOT NULL,
        email VARCHAR(255) NOT NULL,
        assunto VARCHAR(255) NOT NULL,
        descricao TEXT NOT NULL,
        data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('novo', 'lido', 'respondido') DEFAULT 'novo'
    )";
    
    // Executar as queries
    $pdo->exec($sql_clientes);
    echo "Tabela 'clientes' criada com sucesso.<br>";
    
    $pdo->exec($sql_informacoes);
    echo "Tabela 'informacoes' criada com sucesso.<br>";
    
    $pdo->exec($sql_projetos);
    echo "Tabela 'projetos' criada com sucesso.<br>";
    
    $pdo->exec($sql_contatos);
    echo "Tabela 'contatos' criada com sucesso.<br>";
    
    echo "<br>Banco de dados configurado com sucesso!";
    
} catch (PDOException $e) {
    echo "Erro ao criar tabelas: " . $e->getMessage();
}
?>