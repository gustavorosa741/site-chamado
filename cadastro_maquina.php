<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'conexao.php';

    $nome = $_POST['nome'];
    $setor = $_POST['setor'];

    $sql = "INSERT INTO maquina (nome_maquina, setor) VALUES (?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nome, $setor);

    if ($stmt->execute()) {
        echo "<script>alert('Máquina cadastrada com sucesso!'); window.location.href='cadastros/cadastro_maquina.html';</script>";
    } else {
        echo "Erro: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
