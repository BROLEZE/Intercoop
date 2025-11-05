<?php
// Verificar se é um administrador
function isAdmin() {
    return isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
}

// Requerer login de admin
function requireAdmin() {
    if (!isAdmin()) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // Requisição AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Acesso negado', 'redirect' => 'admin_login.html']);
        } else {
            // Requisição normal
            header('Location: admin_login.html');
        }
        exit;
    }
}

// Verificar se está logado como admin
requireAdmin();

// Função para log de atividades do admin
function logAdminActivity($titulo, $descricao) {
    try {
        $pdo = getConnection();
        $sql = "INSERT INTO admin_activities (titulo, descricao, admin_user, data_atividade) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $descricao, $_SESSION['admin_user'] ?? 'admin']);
    } catch (Exception $e) {
        error_log("Erro ao registrar atividade admin: " . $e->getMessage());
    }
}
?>