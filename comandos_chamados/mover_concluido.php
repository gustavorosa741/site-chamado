<?php
include '../BD/conexao.php';

if (!isset($_POST['solucao'])) {
    $id = intval($_GET['id'] ?? 0);
    
    $stmt = $conn->prepare("SELECT id FROM chamado WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        die("ID de chamado inválido ou não encontrado");
    }
    $stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Concluir Chamado</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f8ff;
            color: #003366;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #253236;
            border-radius: 5px;
            min-height: 150px;
            margin-bottom: 20px;
        }
        .button {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #06D6A0;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .button:hover {
            background-color: #05b386;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Concluir Chamado #<?= $id ?></h1>
        <form method="post" action="mover_concluido.php">
            <input type="hidden" name="id" value="<?= $id ?>">
            <label for="solucao">Descreva a solução:</label>
            <textarea id="solucao" name="solucao" required></textarea>
            <button type="submit" class="button">Confirmar Conclusão</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $solucao = trim($_POST['solucao']);
    
    if (empty($solucao)) {
        die("A descrição da solução é obrigatória");
    }

    $stmt = $conn->prepare("UPDATE chamado SET progresso = 'Concluido', data_fechamento = ?, solucao = ? WHERE id = ?");
    $data_fechamento = date('Y-m-d');
    $stmt->bind_param("ssi", $data_fechamento, $solucao, $id);
    
    if ($stmt->execute()) {
        header("Location: ../pagina_principal.php?success=1");
        exit;
    } else {
        die("Erro ao atualizar o chamado: " . $conn->error);
    }
}

$conn->close();
?>