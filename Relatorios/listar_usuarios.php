<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

include '../BD/conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT nivel_acesso FROM usuario WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if ($usuario['nivel_acesso'] > 2) {
    echo "<script>alert('Você não tem permissão para acessar essa página!'); window.location.href='../pagina_principal.php';</script>";    
}

$sql = "SELECT id, nome, usuario, senha, nivel_acesso FROM usuario ORDER BY nome ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=0.4">
    <meta charset="UTF-8">
    <title>Lista de Usuários</title>
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
            max-width: 170px;
            word-wrap: break-word;
            white-space: normal;
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
            padding: 8px 20px;
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

<h1>Usuários Cadastrados</h1>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Usuario</th>
            <th>Permissões</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()):
                if ($row['nivel_acesso'] == '1') {
                    $row['nivel_acesso'] = 'Administrador';

                } else if ($row['nivel_acesso'] == '2') {
                    $row['nivel_acesso'] = 'Manutenção';

                } else {
                    $row['nivel_acesso'] = 'Usuário';
                }
            ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nome']) ?></td>
                <td><?= htmlspecialchars($row['usuario']) ?></td>
                <td><?= $row['nivel_acesso'] ?></td>
                <td>
                    <a class="button" href="editar_usuario.php?id=<?= $row['id'] ?>">Editar</a>
                    <a class="button delete" href="excluir_usuario.php?id=<?= $row['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">Nenhum usuário cadastrado.</td></tr>
        <?php endif; ?>
        <a class="button-voltar" href="../pagina_principal.php">Voltar</a>
    </tbody>
</table>

</body>
</html>
