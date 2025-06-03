<?php
$db = new SQLite3('banco.db');

$db->exec("CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    idade INTEGER NOT NULL,
    email TEXT NOT NULL
)");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $idade = intval($_POST['idade']);
    $email = $_POST['email'];

    $stmt = $db->prepare("INSERT INTO usuarios (nome, idade, email) VALUES (:nome, :idade, :email)");
    $stmt->bindValue(':nome', $nome, SQLITE3_TEXT);
    $stmt->bindValue(':idade', $idade, SQLITE3_INTEGER);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);

    if ($stmt->execute()) {
        $mensagem = "Dados salvos com sucesso!";
    } else {
        $mensagem = "Erro ao salvar dados";
    }
}

$resultados = $db->query("SELECT * FROM usuarios");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuários</title>
</head>
<body>
    <?php if (!empty($mensagem)) : ?>
        <p style="color: green;"><?= $mensagem ?></p>
    <?php endif; ?>

    <h1>Cadastrar Usuário</h1>
    <form method="POST">
        <label>Nome:</label>
        <input type="text" name="nome" required><br>

        <label>Idade:</label>
        <input type="number" name="idade" required><br>

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <button type="submit">Salvar</button>
    </form>

    <h2>Usuários Cadastrados</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Idade</th>
            <th>Email</th>
        </tr>
        <?php while ($row = $resultados->fetchArray()) : ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['nome'] ?></td>
            <td><?= $row['idade'] ?></td>
            <td><?= $row['email'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>