<?php
// Verifica se há um ID na URL para modo de edição
$editando = isset($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header("Content-Type: application/json");

    try {
        $db = new PDO('sqlite:database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $db->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL
        )");

        $action = $_GET['action'];

        switch($action) {
            case 'read':
                $stmt = $db->query("SELECT * FROM usuarios");
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                break;

            case 'delete':
                $id = $_GET['id'] ?? null;
                $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => 'Registro excluído com sucesso!']);
                break;

            default:
                throw new Exception('Ação inválida');
        }
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");

    try {
        $db = new PDO('sqlite:database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $action = $_GET['action'] ?? '';
        $id = $_POST['id'] ?? null;
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';

        if(empty($nome) || empty($email)) {
            throw new Exception('Nome e email são obrigatórios');
        }

        if($action === 'create') {
            $stmt = $db->prepare("INSERT INTO usuarios (nome, email) VALUES (?, ?)");
            $stmt->execute([$nome, $email]);
            $mensagem = 'Dados registrados com sucesso!';
        } else {
            $stmt = $db->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $id]);
            $mensagem = 'Dados atualizados com sucesso!';
        }

        echo json_encode(['success' => $mensagem]);
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD PHP+SQLite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        form { background: #f9f9f9; padding: 20px; border-radius: 5px; }
        input { padding: 8px; margin: 5px 0; width: 300px; }
        button, .btn { 
            padding: 8px 15px; 
            margin-right: 10px; 
            cursor: pointer; 
            border: 1px solid #ccc;
            background: #f0f0f0;
            border-radius: 4px;
        }
        button:hover, .btn:hover { background: #e0e0e0; }
        #cancelBtn { display: none; background: #ffecec; border-color: #ffb3b3; }
        #cancelBtn:hover { background: #ffdddd; }
        .editing-form { border: 2px solid #4CAF50; background-color: #f8fff8; }
        #mensagem {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            display: none;
        }
        .sucesso { background-color: #dff0d8; color: #3c763d; }
        .erro { background-color: #f2dede; color: #a94442; }
    </style>
</head>
<body>
    <h2>CRUD com PHP e SQLite</h2>
    
    <div id="mensagem"></div>

    <form id="crudForm">
        <input type="hidden" id="id">
        <div>
            <label for="nome">Nome:</label><br>
            <input type="text" id="nome" required>
        </div>
        <div>
            <label for="email">Email:</label><br>
            <input type="email" id="email" required>
        </div>
        <div style="margin-top: 15px;">
            <input type="submit" id="submitBtn" value="Salvar">
            <button type="button" id="cancelBtn">Cancelar</button>
            <button type="button" onclick="clearForm()">Limpar</button>
        </div>
    </form>

    <h3>Registros</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="dataTable"></tbody>
    </table>

    <script>
        document.getElementById('crudForm').addEventListener('submit', function(e) {
            e.preventDefault();
            saveData();
        });

        document.getElementById('cancelBtn').addEventListener('click', clearForm);

        function loadData() {
            fetch('?action=read')
                .then(response => response.json())
                .then(data => {
                    const table = document.getElementById('dataTable');
                    table.innerHTML = '';
                    data.forEach(row => {
                        table.innerHTML += `
                        <tr>
                            <td>${row.id}</td>
                            <td>${row.nome}</td>
                            <td>${row.email}</td>
                            <td>
                                <button onclick="editData(${row.id}, '${escapeHtml(row.nome)}', '${escapeHtml(row.email)}')">Editar</button>
                                <button onclick="deleteData(${row.id})">Excluir</button>
                            </td>
                        </tr>`;
                    });
                });
        }

        function saveData() {
            const formData = new FormData();
            formData.append('id', document.getElementById('id').value);
            formData.append('nome', document.getElementById('nome').value);
            formData.append('email', document.getElementById('email').value);

            const action = document.getElementById('id').value ? 'update' : 'create';

            fetch(`?action=${action}`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.success || data.error, data.success ? 'sucesso' : 'erro');
                loadData();
                if (data.success) clearForm();
            });
        }

        function editData(id, nome, email) {
            document.getElementById('id').value = id;
            document.getElementById('nome').value = nome;
            document.getElementById('email').value = email;
            
            document.getElementById('submitBtn').value = 'Atualizar';
            document.getElementById('cancelBtn').style.display = 'inline-block';
            document.getElementById('crudForm').classList.add('editing-form');
        }

        function deleteData(id) {
            if(confirm('Tem certeza que deseja excluir este registro?')) {
                fetch(`?action=delete&id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        showMessage(data.success || data.error, data.success ? 'sucesso' : 'erro');
                        loadData();
                    });
            }
        }

        function clearForm() {
            document.getElementById('id').value = '';
            document.getElementById('nome').value = '';
            document.getElementById('email').value = '';
            
            document.getElementById('submitBtn').value = 'Salvar';
            document.getElementById('cancelBtn').style.display = 'none';
            document.getElementById('crudForm').classList.remove('editing-form');
        }

        function showMessage(text, type) {
            const msgDiv = document.getElementById('mensagem');
            msgDiv.textContent = text;
            msgDiv.className = type;
            msgDiv.style.display = 'block';
            
            setTimeout(() => {
                msgDiv.style.display = 'none';
            }, 3000);
        }

        function escapeHtml(str) {
            return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }

        // Carrega os dados quando a página é aberta
        window.onload = loadData;
    </script>
</body>
</html>