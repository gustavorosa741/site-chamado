<?php
include '../BD/conexao.php';

// Consulta as máquinas
$sql =  "SELECT c.*, m.nome_maquina, m.setor, a.categoria
        FROM chamado c
        LEFT JOIN maquina m ON c.id_maquina = m.id
        LEFT JOIN categoria_chamado a ON c.categoria = a.id
        ORDER BY c.data_abertura";
        
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.3">
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

        a.button-voltar {
            background-color: #3399ff;
            color: white;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            margin-right: 5px;
            margin-top: 20px;
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
            <th>Solução</th>
            <th>Progresso</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['id_funcionario']) ?></td>
                <td><?= htmlspecialchars($row['nome_maquina']) ?></td>
                <td><?= htmlspecialchars($row['categoria']) ?></td>
                <td><?= htmlspecialchars($row['data_abertura']) ?></td>
                <td><?= htmlspecialchars($row['data_fechamento']) ?></td>
                <td><?= htmlspecialchars($row['problema']) ?></td>
                <td><?= htmlspecialchars($row['solucao']) ?></td>
                <td><?= htmlspecialchars($row['progresso']) ?></td>
                <td>
                    <a class="button" href="editar_chamado.php?id=<?= $row['id'] ?>">Editar</a>
                    <a class="button delete" href="excluir_chamado.php?id=<?= $row['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este chamado?')">Excluir</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Nenhum chamado cadastrado.</td></tr>
        <?php endif; ?>
        <a class="button-voltar" href="../pagina_principal.php">Voltar</a>
    </tbody>
</table>

</body>
</html>