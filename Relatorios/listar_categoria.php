<?php
include '../BD/conexao.php';

$sql = "SELECT id, categoria FROM categoria_chamado";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.7">
    <meta charset="UTF-8">
    <title>Lista de Categorias</title>
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

<h1>Categorias Cadastradas</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Categoria</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['categoria']) ?></td>
                <td>
                    <a class="button" href="editar_categoria.php?id=<?= $row['id'] ?>">Editar</a>
                    <a class="button delete" href="excluir_categoria.php?id=<?= $row['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir esta Categoria?')">Excluir</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Nenhuma Categoria cadastrada.</td></tr>
        <?php endif; ?>
        <a class="button-voltar" href="../pagina_principal.php">Voltar</a>
    </tbody>
</table>

</body>
</html>
