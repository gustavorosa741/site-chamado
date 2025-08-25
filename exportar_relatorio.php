<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

include 'BD/conexao.php';

// Verificar se é uma solicitação de exportação
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Obter parâmetros de filtro
    $periodo = $_GET['periodo'] ?? '30';
    $setor = $_GET['setor'] ?? '';
    
    // Validar período
    $periodos_permitidos = ['7', '30', '90', '365'];
    if (!in_array($periodo, $periodos_permitidos)) {
        $periodo = '30';
    }
    
    // Construir a consulta SQL com base nos filtros
    $sql = "SELECT 
                c.id,
                u.nome as funcionario,
                m.nome_maquina as maquina,
                M.setor,
                cat.categoria,
                c.data_abertura,
                c.data_fechamento,
                c.problema,
                c.solucao,
                c.progresso,
                CASE 
                    WHEN c.data_fechamento IS NOT NULL THEN 'Fechado'
                    WHEN c.progresso = 100 THEN 'Resolvido'
                    WHEN c.progresso > 0 THEN 'Em andamento'
                    ELSE 'Aberto'
                END as status
            FROM chamado c
            LEFT JOIN usuario u ON c.id_funcionario = u.id
            LEFT JOIN maquina m ON c.id_maquina = m.id
            LEFT JOIN categoria_chamado cat ON c.categoria = cat.id
            WHERE c.data_abertura >= DATE_SUB(NOW(), INTERVAL $periodo DAY)";
    
    // Aplicar filtro de setor
    if (!empty($setor)) {
        $setor_escape = $conn->real_escape_string($setor);
        $sql .= " AND s.setor = '$setor_escape'";
    }
    
    $sql .= " ORDER BY c.data_abertura DESC";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        // Definir headers para download do Excel
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="relatorio_chamados_' . date('Y-m-d') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Início da tabela HTML (que o Excel reconhece)
        echo "<table border='1'>";
        echo "<tr>
                <th colspan='11' style='font-size: 16px; background-color: #2c3e50; color: white;'>Relatório de Chamados - Período: $periodo dias</th>
              </tr>";
        echo "<tr>
                <th>ID</th>
                <th>Funcionário</th>
                <th>Máquina</th>
                <th>Setor</th>
                <th>Categoria</th>
                <th>Data Abertura</th>
                <th>Data Fechamento</th>
                <th>Problema</th>
                <th>Solução</th>
                <th>Progresso</th>
                <th>Status</th>
              </tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['funcionario'] . "</td>";
            echo "<td>" . $row['maquina'] . "</td>";
            echo "<td>" . $row['setor'] . "</td>";
            echo "<td>" . $row['categoria'] . "</td>";
            echo "<td>" . $row['data_abertura'] . "</td>";
            echo "<td>" . ($row['data_fechamento'] ? $row['data_fechamento'] : 'Em aberto') . "</td>";
            echo "<td>" . $row['problema'] . "</td>";
            echo "<td>" . ($row['solucao'] ? $row['solucao'] : 'Não resolvido') . "</td>";
            echo "<td>" . $row['progresso'] . "%</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        exit;
    } else {
        echo "<script>alert('Nenhum dado encontrado para exportar!'); window.history.back();</script>";
    }
} else {
    header("Location: ../pagina_principal.php");
    exit;
}
?>