<?php
require_once 'config.php';
require_once 'admin_auth.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    
    // Buscar cadastros dos últimos 12 meses
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(data_cadastro, '%Y-%m') as mes,
            COUNT(*) as total
        FROM clientes 
        WHERE data_cadastro >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(data_cadastro, '%Y-%m')
        ORDER BY mes ASC
    ");
    
    $data = $stmt->fetchAll();
    
    // Gerar array dos últimos 12 meses
    $labels = [];
    $values = [];
    
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-{$i} months"));
        $monthName = date('M/Y', strtotime("-{$i} months"));
        
        $labels[] = $monthName;
        
        // Procurar dados para este mês
        $found = false;
        foreach ($data as $row) {
            if ($row['mes'] === $month) {
                $values[] = intval($row['total']);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $values[] = 0;
        }
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'values' => $values
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar dados do gráfico: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>