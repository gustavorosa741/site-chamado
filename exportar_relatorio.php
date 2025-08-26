<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

include 'BD/conexao.php';

// Definir codificação para UTF-8
header('Content-Type: text/html; charset=utf-8');
$conn->set_charset("utf8");

// Verificar se é uma solicitação de exportação
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Obter parâmetros de filtro
    $periodo = isset($_GET['periodo']) ? intval($_GET['periodo']) : 30;
    $setor = isset($_GET['setor']) ? $_GET['setor'] : '';
    
    // Validar período
    $periodos_permitidos = [7, 30, 90, 365];
    if (!in_array($periodo, $periodos_permitidos)) {
        $periodo = 30;
    }
    
    // Construir a consulta SQL com a estrutura correta do banco
    $sql = "SELECT 
                c.id,
                u.nome as funcionario,
                m.nome_maquina as maquina,
                m.setor,
                cat.categoria,
                c.data_abertura,
                c.data_fechamento,
                c.problema,
                c.solucao,
                c.progresso,
                c.urgencia
            FROM chamado c
            LEFT JOIN usuario u ON c.id_funcionario = u.id
            LEFT JOIN maquina m ON c.id_maquina = m.id
            LEFT JOIN categoria_chamado cat ON c.categoria = cat.id
            WHERE c.data_abertura >= DATE_SUB(NOW(), INTERVAL $periodo DAY)";
    
    // Aplicar filtro de setor
    if (!empty($setor)) {
        $setor_escape = $conn->real_escape_string($setor);
        $sql .= " AND m.setor = '$setor_escape'";
    }
    
    $sql .= " ORDER BY c.data_abertura DESC";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        // Definir headers para download do Excel com UTF-8 BOM
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio_chamados_' . date('Y-m-d') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Output BOM for UTF-8 (importante para Excel)
        echo "\xEF\xBB\xBF";
        
        // Início da tabela HTML (que o Excel reconhece)
        echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
        echo "<table border='1'>";
        echo "<tr>
                <th colspan='10' style='font-size: 16px; background-color: #2c3e50; color: white;'>Relatório de Chamados - Período: $periodo dias</th>
              </tr>";
        echo "<tr>
                <th>ID</th>
                <th>Funcionário</th>
                <th>Máquina</th>
                <th>Setor</th>
                <th>Categoria</th>
                <th>Data Abertura</th>
                <th>Urgencia</th>
                <th>Data Fechamento</th>
                <th>Problema</th>
                <th>Solução</th>
                <th>Progresso</th>
              </tr>";
        
        while ($row = $result->fetch_assoc()) {
            $status = $row['data_fechamento'] ? 'Fechado' : ($row['progresso'] > 0 ? 'Em andamento' : 'Aberto');
            
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlentities($row['funcionario'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlentities($row['maquina'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlentities($row['setor'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . htmlentities($row['categoria'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . $row['data_abertura'] . "</td>";
            echo "<td>" . ($row['data_fechamento'] ? $row['data_fechamento'] : 'Em aberto') . "</td>";
            echo "<td>" . htmlentities($row['problema'], ENT_QUOTES, 'UTF-8') . "</td>";
            echo "<td>" . ($row['solucao'] ? htmlentities($row['solucao'], ENT_QUOTES, 'UTF-8') : 'Não resolvido') . "</td>";
            echo "<td>" . $row['progresso'] . "% ($status)</td>";
            echo "<td>" . htmlentities($row['urgencia'], ENT_QUOTES, 'UTF-8') . "</td>";
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