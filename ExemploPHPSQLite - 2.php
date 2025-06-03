<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário com Validação JavaScript</title>
    <script>
        function validarFormulario() {
            var nome = document.forms["formulario"]["nome"].value;
            var email = document.forms["formulario"]["email"].value;

            if (nome == "" || email == "") {
                alert("Todos os campos devem ser preenchidos!");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <h2>Formulário de Cadastro</h2>
    <form name="formulario" method="POST" onsubmit="return validarFormulario()">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required><br><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <input type="submit" value="Enviar">
    </form>

    <?php
    try {
        $pdo = new PDO('sqlite:database.db');
        $sql = 'CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL
        )';
        $pdo->exec($sql);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nome = $_POST['nome'];
            $email = $_POST['email'];

            $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email) VALUES (:nome, :email)');
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            echo '<p>Dados cadastrados com sucesso!</p>';
        }
    } catch (PDOException $e) {
        echo '<p>Erro: ' . $e->getMessage() . '</p>';
    }
    ?>
</body>
</html>

<?php
try {
    $pdo = new PDO('sqlite:database.db');

    $sql = 'CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT NOT NULL
    )';
    $pdo->exec($sql);

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $nome = $_POST['nome'];
        $email = $_POST['email'];

        $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email) VALUES (:nome, :email)');
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        echo 'Dados inseridos com sucesso';
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>