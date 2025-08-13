<?php
include '../BD/conexao.php';

// Consulta as máquinas
$sql = "SELECT id, nome_maquina, setor FROM maquina";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Máquinas</title>
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

<h1>Máquinas Cadastradas</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome da Máquina</th>
            <th>Setor</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nome_maquina']) ?></td>
                <td><?= htmlspecialchars($row['setor']) ?></td>
                <td>
                    <a class="button" href="editar_maquina.php?id=<?= $row['id'] ?>">Editar</a>
                    <a class="button delete" href="excluir_maquina.php?id=<?= $row['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir esta máquina?')">Excluir</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Nenhuma máquina cadastrada.</td></tr>
        <?php endif; ?>
        <button type="button" onclick="window.location.href='../index.html'">Voltar</button>
    </tbody>
</table>

</body>
</html>
