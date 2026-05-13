<?php
// pages/usuarios.php
session_start();
require '../config/conexao.php';

// Verificação 1: Está logado?
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// Verificação 2: É Administrador? (Se não for, volta pro Dashboard)
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$mensagem = '';

// Se o formulário foi enviado para cadastrar novo usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $nivel_acesso = $_POST['nivel_acesso'];

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        $senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso) VALUES (:nome, :email, :senha, :nivel_acesso)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'senha' => $senha_criptografada,
                'nivel_acesso' => $nivel_acesso
            ]);
            $mensagem = "<div class='alert alert-success'>Usuário cadastrado com sucesso!</div>";
        } catch (PDOException $e) {
            // Se o e-mail já existir no banco, vai dar erro (pois colocamos UNIQUE na tabela)
            $mensagem = "<div class='alert alert-danger'>Erro ao cadastrar. Verifique se o e-mail já está em uso.</div>";
        }
    } else {
        $mensagem = "<div class='alert alert-warning'>Preencha todos os campos obrigatórios.</div>";
    }
}

// Busca a lista de usuários
$usuarios = $pdo->query("SELECT id, nome, email, nivel_acesso FROM usuarios ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Usuários - Controle Hospitalar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
        }

        .nav-link {
            color: rgba(255, 255, 255, .8);
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, .1);
        }

        .nav-link.active {
            background: #3498db;
            color: white;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3">
                <h4 class="text-center mb-4">HospStock</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link mb-2 rounded" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>
                            Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-2 rounded" href="materiais.php"><i class="bi bi-box-seam me-2"></i>
                            Estoque</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-2 rounded" href="movimentacoes.php"><i
                                class="bi bi-arrow-left-right me-2"></i> Movimentações</a>
                    </li>

                    <?php if ($_SESSION['nivel_acesso'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link active mb-2 rounded" href="usuarios.php"><i class="bi bi-people me-2"></i>
                                Usuários</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item border-top mt-3 pt-3">
                        <a class="nav-link text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>
                            Sair</a>
                    </li>
                </ul>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-4">Gerenciar Usuários</h1>

                <?php echo $mensagem; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Novo Usuário</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Nome Completo *</label>
                                    <input type="text" name="nome" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">E-mail (Login) *</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Senha Inicial *</label>
                                    <input type="password" name="senha" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Nível de Acesso *</label>
                                    <select name="nivel_acesso" class="form-select" required>
                                        <option value="usuario">Usuário Comum</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>
                                Cadastrar Usuário</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Usuários do Sistema</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Nível</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usr): ?>
                                    <tr>
                                        <td><?php echo $usr['id']; ?></td>
                                        <td><?php echo htmlspecialchars($usr['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($usr['email']); ?></td>
                                        <td>
                                            <?php if ($usr['nivel_acesso'] == 'admin'): ?>
                                                <span class="badge bg-primary">Administrador</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Usuário</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>