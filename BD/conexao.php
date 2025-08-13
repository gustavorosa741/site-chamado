
<?php
$servidor = "localhost";
$usuario = "root";          
$senha = "1234#abcd";                
$banco = "chamados";

$conn = new mysqli($servidor, $usuario, $senha, $banco);

// Verifica conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>
