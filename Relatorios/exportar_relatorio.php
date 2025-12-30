<?php
/**
 * Arquivo responsável por exportar um relatório de chamados em formato Excel (.xls),
 * aplicando filtros de período e setor, garantindo autenticação do usuário e
 * compatibilidade com caracteres UTF-8 no Excel.
 */

session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Inclui a conexão com o banco de dados
include '../BD/conexao.php';

// Define codificação UTF-8 para evitar problemas com acentuação
header('Content-Type: text/html; charset=utf-8');
$conn->set_charset("utf8");

// Verifica se a requisição é para exportação em Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {

    // Obtém e valida os filtros
    $periodo = isset($_GET['periodo']) ? intval($_GET['periodo']) : 30;
    $setor = isset($_GET['setor']) ? $_GET['setor'] : '';

    $periodos_permitidos = [7, 30, 90, 365];
    if (!in_array($periodo, $periodos_permitidos)) {
        $periodo = 30;
    }

    // Monta a consulta com os relacionamentos necessários
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

    if (!empty($setor)) {
        $setor_escape = $conn->real_escape_string($setor);
        $sql .= " AND m.setor = '$setor_escape'";
    }

    $sql .= " ORDER BY c.data_abertura DESC";
    $result = $conn->query($sql);

    // Gera o arquivo Excel se houver dados
    if ($result && $result->num_rows > 0) {

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio_chamados_' . date('Y-m-d') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";
        echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";
        echo "<table border='1'>";
        echo "<tr>
                <th colspan='11'>Relatório de Chamados - Período: $periodo dias</th>
              </tr>";
        echo "<tr>
                <th>ID</th>
                <th>Solicitante</th>
                <th>Máquina</th>
                <th>Setor</th>
                <th>Categoria</th>
                <th>Data Abertura</th>
                <th>Urgência</th>
                <th>Data Fechamento</th>
                <th>Problema</th>
                <th>Solução</th>
                <th>Progresso</th>
              </tr>";

        while ($row = $result->fetch_assoc()) {
            $status = $row['data_fechamento'] ? 'Fechado' : ($row['progresso'] ? 'Em andamento' : 'Aberto');

            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>" . htmlentities($row['funcionario'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlentities($row['maquina'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlentities($row['setor'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . htmlentities($row['categoria'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>{$row['data_abertura']}</td>
                    <td>{$row['urgencia']}</td>
                    <td>" . ($row['data_fechamento'] ?: 'Em aberto') . "</td>
                    <td>" . htmlentities($row['problema'], ENT_QUOTES, 'UTF-8') . "</td>
                    <td>" . ($row['solucao'] ? htmlentities($row['solucao'], ENT_QUOTES, 'UTF-8') : 'Não resolvido') . "</td>
                    <td>{$row['progresso']} ($status)</td>
                  </tr>";
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
