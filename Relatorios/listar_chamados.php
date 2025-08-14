<?php
include '../BD/conexao.php';

// Consulta as máquinas
$sql = "SELECT id, id_funcionario, id_maquina, categoria, data_abertura, data_fechamento, problema, fechamento FROM chamado";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
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
            padding: 12px 15px;
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
    </style>
</head>
<body>

<h1>Chamados Cadastrados</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>ID_funcionario</th>
            <th>ID_maquina</th>
            <th>Categoria</th>
            <th>Data_abertura</th>
            <th>Data_fechamento</th>
            <th>Problema</th>
            <th>Fechamento</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['id_funcionario']) ?></td>
                <td><?= htmlspecialchars($row['id_maquina']) ?></td>
                <td><?= htmlspecialchars($row['categoria']) ?></td>
                <td><?= htmlspecialchars($row['data_abertura']) ?></td>
                <td><?= htmlspecialchars($row['data_fechamento']) ?></td>
                <td><?= htmlspecialchars($row['problema']) ?></td>
                <td><?= htmlspecialchars($row['fechamento']) ?></td>
                <td>
                    <a class="button" href="editar_chamado.php?id=<?= $row['id'] ?>">Editar</a>
                    <a class="button delete" href="excluir_chamado.php?id=<?= $row['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este chamado?')">Excluir</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Nenhum chamado cadastrado.</td></tr>
        <?php endif; ?>
        <button type="button" onclick="window.location.href='../pagina_principal.html'">Voltar</button>
    </tbody>
</table>

</body>
</html>