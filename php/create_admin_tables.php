<?php
require_once 'config.php';

try {
    $pdo = getConnection();
    
    // Criar tabela de atividades do admin
    $sql_admin_activities = "
    CREATE TABLE IF NOT EXISTS admin_activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        descricao TEXT NOT NULL,
        admin_user VARCHAR(100) NOT NULL,
        data_atividade TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql_admin_activities);
    echo "Tabela 'admin_activities' criada com sucesso.<br>";
    
    echo "<br>Tabelas administrativas configuradas com sucesso!";
    
} catch (PDOException $e) {
    echo "Erro ao criar tabelas administrativas: " . $e->getMessage();
}
?>