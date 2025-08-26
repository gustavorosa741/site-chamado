<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../BD/conexao.php';

// Obter nível de acesso do usuário
$usuario_id = $_SESSION['usuario_id'];
$sql_usuario = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $usuario_id);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();

if ($result_usuario->num_rows > 0) {
    $usuario = $result_usuario->fetch_assoc();
    $nivel_acesso = $usuario['nivel_acesso'];
} else {
    $nivel_acesso = 3; // Valor padrão se não encontrar
}
$stmt_usuario->close();

// Construir a consulta SQL baseada no nível de acesso
if ($nivel_acesso <= 2) {
    // Admin (1) e Gerente (2) veem todos os chamados
    $sql = "SELECT c.*, m.nome_maquina, m.setor, a.categoria, u.nome
            FROM chamado c
            LEFT JOIN maquina m ON c.id_maquina = m.id
            LEFT JOIN categoria_chamado a ON c.categoria = a.id
            LEFT JOIN usuario u ON c.id_funcionario = u.id
            ORDER BY c.data_abertura DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
} else {
    // Usuário comum (3) vê apenas seus próprios chamados
    $sql = "SELECT c.*, m.nome_maquina, m.setor, a.categoria, u.nome
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

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <meta charset="UTF-8">
    <title>Lista de Chamados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            color: #003366;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #3399ff;
            color: white;
        }
        tr:hover {
            background-color: #e6f0ff;
        }
        a.button {
            background-color: #3399ff;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            margin-right: 5px;
        }
        a.button.delete {
            background-color: #cc3333;
        }
        a.button-voltar {
            background-color: #3399ff;
            color: white;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            margin-right: 5px;
            display: inline-block;
            float: right;
        }
        .info-nivel {
            background-color: #e6f0ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #3399ff;
        }
    </style>
</head>
<body>

<h1>Chamados Cadastrados </h1>
<a class="button-voltar" href="../pagina_principal.php">Voltar</a>

<?php if ($nivel_acesso > 2): ?>
    <div class="info-nivel">
        <strong>Visualizando apenas seus chamados</strong> (Usuário comum)
    </div>
<?php else: ?>
    <div class="info-nivel">
        <strong>Visualizando todos os chamados</strong> (<?= $nivel_acesso == 1 ? 'Administrador' : 'Gerente' ?>)
    </div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Solicitante</th>
            <th>Maquina</th>
            <th>Categoria</th>
            <th>Data Abertura</th>
            <th>Data Fechamento</th>
            <th>Problema</th>
            <th>Solução</th>
            <th>Progresso</th>
            <th>Urgencia</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nome']) ?></td>
                <td><?= htmlspecialchars($row['nome_maquina']) ?></td>
                <td><?= htmlspecialchars($row['categoria']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['data_abertura'])) ?></td>
                <td><?= $row['data_fechamento'] ? date('d/m/Y H:i', strtotime($row['data_fechamento'])) : 'Em aberto' ?></td>
                <td><?= htmlspecialchars($row['problema']) ?></td>
                <td><?= htmlspecialchars($row['solucao']) ?></td>
                <td><?= htmlspecialchars($row['progresso']) ?></td>
                <td><?= htmlspecialchars($row['urgencia']) ?></td>
                <td>
                    <p><a class="button" href="editar_chamado.php?id=<?= $row['id'] ?>">Editar</a></p>
                    <?php if ($nivel_acesso <= 2): ?>
                        <a class="button delete" href="excluir_chamado.php?id=<?= $row['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este chamado?')">Excluir</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="11" style="text-align: center;">
                    <?= $nivel_acesso > 2 ? 'Você não possui chamados cadastrados.' : 'Nenhum chamado cadastrado.' ?>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<br>

</body>
</html>