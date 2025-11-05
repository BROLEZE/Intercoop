<?php
require_once 'config.php';

// Destruir variáveis de sessão do admin
unset($_SESSION['admin_logged']);
unset($_SESSION['admin_user']);
unset($_SESSION['admin_login_time']);

// Redirecionar para login
header('Location: ../admin/admin_login.html');
exit;
?>