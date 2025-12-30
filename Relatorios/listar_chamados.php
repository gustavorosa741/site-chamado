<?php
// Inicia a sessão para controle de autenticação
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Inclui o arquivo de conexão com o banco de dados
include '../BD/conexao.php';

// ==============================
// BUSCA DO NÍVEL DE ACESSO
// ==============================

// ID do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Consulta para obter o nível de acesso do usuário
$sql_usuario = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $usuario_id);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();

// Se o usuário for encontrado, armazena o nível de acesso
if ($result_usuario->num_rows > 0) {
    $usuario = $result_usuario->fetch_assoc();
    $nivel_acesso = $usuario['nivel_acesso'];
} else {
    // Valor padrão de segurança (usuário comum)
    $nivel_acesso = 3;
}

// Fecha o statement
$stmt_usuario->close();

// ==============================
// CONSULTA DE CHAMADOS
// ==============================

// Admin (1) e Gerente (2) veem todos os chamados
if ($nivel_acesso <= 2) {

    $sql = "SELECT c.*, 
                   m.nome_maquina, 
                   m.setor, 
                   a.categoria, 
                   u.nome
            FROM chamado c
            LEFT JOIN maquina m ON c.id_maquina = m.id
            LEFT JOIN categoria_chamado a ON c.categoria = a.id
            LEFT JOIN usuario u ON c.id_funcionario = u.id
            ORDER BY c.data_abertura DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

} else {
    // Usuário comum (3) vê apenas seus próprios chamados
    $sql = "SELECT c.*, 
                   m.nome_maquina, 
                   m.setor, 
                   a.categoria, 
                   u.nome
            FROM chamado c
            LEFT JOIN maquina m ON c.id_maquina = m.id
            LEFT JOIN categoria_chamado a ON c.categoria = a.id
            LEFT JOIN usuario u ON c.id_funcionario = u.id
            WHERE c.id_funcionario = ?
            ORDER BY c.data_abertura DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
}

// Resultado final da consulta
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Configurações básicas da página -->
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <meta charset="UTF-8">
    <title>Lista de Chamados</title>

    <!-- CSS da página de listagem -->
    <link rel="stylesheet" href="../assets/css/listas.css">
</head>

<body>

<h1>Chamados Cadastrados</h1>

<!-- Botão para retornar à página principal -->
<a class="button-voltar1" href="../pagina_principal.php">Voltar</a>

<!-- Exibe informação conforme nível de acesso -->
<?php if ($nivel_acesso > 2): ?>
    <div class="info-nivel">
        <strong>Visualizando apenas seus chamados</strong> (Usuário comum)
    </div>
<?php else: ?>
    <div class="info-nivel">
        <strong>Visualizando todos os chamados</strong>
        (<?= $nivel_acesso == 1 ? 'Administrador' : 'Gerente' ?>)
    </div>
<?php endif; ?>

<!-- Tabela de chamados -->
<table id="tabelaChamados">
    <thead>
        <tr>
            <!-- Cabeçalhos com suporte a ordenação -->
            <th data-sort="id" data-type="number">ID</th>
            <th data-sort="nome" data-type="text">Solicitante</th>
            <th data-sort="nome_maquina" data-type="text">Máquina</th>
            <th data-sort="categoria" data-type="text">Categoria</th>
            <th data-sort="data_abertura" data-type="date">Data Abertura</th>
            <th data-sort="data_fechamento" data-type="date">Data Fechamento</th>
            <th data-sort="problema" data-type="text">Problema</th>
            <th data-sort="solucao" data-type="text">Solução</th>
            <th data-sort="progresso" data-type="text">Progresso</th>
            <th data-sort="urgencia" data-type="text">Urgência</th>
            <th>Ações</th>
        </tr>
    </thead>

    <tbody>
        <!-- Verifica se existem chamados -->
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nome']) ?></td>
                <td><?= htmlspecialchars($row['nome_maquina']) ?></td>
                <td><?= htmlspecialchars($row['categoria']) ?></td>

                <!-- Datas com valor auxiliar para ordenação -->
                <td data-sort-value="<?= strtotime($row['data_abertura']) ?>">
                    <?= date('d/m/Y', strtotime($row['data_abertura'])) ?>
                </td>

                <td data-sort-value="<?= $row['data_fechamento'] ? strtotime($row['data_fechamento']) : 0 ?>">
                    <?= $row['data_fechamento']
                        ? date('d/m/Y', strtotime($row['data_fechamento']))
                        : 'Em aberto' ?>
                </td>

                <td><?= htmlspecialchars($row['problema']) ?></td>
                <td><?= htmlspecialchars($row['solucao']) ?></td>
                <td><?= htmlspecialchars($row['progresso']) ?></td>
                <td><?= htmlspecialchars($row['urgencia']) ?></td>

                <td>
                    <!-- Botão de edição disponível para todos -->
                    <p>
                        <a class="button" href="editar_chamado.php?id=<?= $row['id'] ?>">
                            Editar
                        </a>
                    </p>

                    <!-- Botão de exclusão apenas para Admin e Gerente -->
                    <?php if ($nivel_acesso <= 2): ?>
                        <a class="button delete"
                           href="excluir_chamado.php?id=<?= $row['id'] ?>"
                           onclick="return confirm('Tem certeza que deseja excluir este chamado?')">
                            Excluir
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <!-- Mensagem quando não há chamados -->
            <tr>
                <td colspan="11" style="text-align: center;">
                    <?= $nivel_acesso > 2
                        ? 'Você não possui chamados cadastrados.'
                        : 'Nenhum chamado cadastrado.' ?>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br>

<script>
// ==============================
// SCRIPT DE ORDENAÇÃO DA TABELA
// ==============================
document.addEventListener('DOMContentLoaded', function () {

    const tabela = document.getElementById('tabelaChamados');
    const thead = tabela.querySelector('thead');
    const tbody = tabela.querySelector('tbody');
    const ths = thead.querySelectorAll('th[data-sort]');

    // Estado atual da ordenação
    let currentSort = {
        column: null,
        direction: 'none' // none | asc | desc
    };

    // Evento de clique nos cabeçalhos
    ths.forEach(th => {
        th.addEventListener('click', function () {

            const column = this.getAttribute('data-sort');
            const type = this.getAttribute('data-type');

            // Remove indicadores visuais de ordenação
            ths.forEach(header => header.classList.remove('asc', 'desc'));

            // Alterna a direção da ordenação
            if (currentSort.column === column) {
                if (currentSort.direction === 'asc') {
                    currentSort.direction = 'desc';
                    this.classList.add('desc');
                } else if (currentSort.direction === 'desc') {
                    resetSort();
                    return;
                } else {
                    currentSort.direction = 'asc';
                    this.classList.add('asc');
                }
            } else {
                currentSort.column = column;
                currentSort.direction = 'asc';
                this.classList.add('asc');
            }

            // Executa a ordenação
            sortTable(column, currentSort.direction, type);
        });
    });

    // Função de ordenação da tabela
    function sortTable(column, direction, type) {
        if (direction === 'none') return;

        const rows = Array.from(tbody.querySelectorAll('tr'));
        const colIndex = Array.from(ths)
            .findIndex(th => th.getAttribute('data-sort') === column);

        rows.sort((a, b) => {
            let aValue = a.cells[colIndex].getAttribute('data-sort-value') || a.cells[colIndex].textContent.trim();
            let bValue = b.cells[colIndex].getAttribute('data-sort-value') || b.cells[colIndex].textContent.trim();

            if (type === 'number' || type === 'date') {
                aValue = parseFloat(aValue) || 0;
                bValue = parseFloat(bValue) || 0;
            }

            if (aValue < bValue) return direction === 'asc' ? -1 : 1;
            if (aValue > bValue) return direction === 'asc' ? 1 : -1;
            return 0;
        });

        // Atualiza a tabela
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
    }

    // Restaura a ordem original
    function resetSort() {
        location.reload();
    }
});
</script>

</body>
</html>
