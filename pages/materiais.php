<?php
// pages/materiais.php
session_start();
require '../config/conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $categoria_id = $_POST['categoria_id'];
    $quantidade = $_POST['quantidade'];
    $validade = $_POST['validade'];
    $estoque_minimo = $_POST['estoque_minimo'];

    if (!empty($nome) && !empty($categoria_id)) {
        try {
            $sql = "INSERT INTO materiais (nome, categoria_id, quantidade, validade, estoque_minimo) 
                    VALUES (:nome, :categoria_id, :quantidade, :validade, :estoque_minimo)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nome' => $nome,
                'categoria_id' => $categoria_id,
                'quantidade' => $quantidade,
                'validade' => $validade ?: null, 
                'estoque_minimo' => $estoque_minimo
            ]);

            $mensagem = "<div class='alert alert-success'>Material cadastrado com sucesso!</div>";
        } catch (PDOException $e) {
            $mensagem = "<div class='alert alert-danger'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensagem = "<div class='alert alert-warning'>Nome e Categoria são obrigatórios.</div>";
    }
}

// Busca as categorias para preencher o formulário (caixa de seleção)
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

// Busca os materiais cadastrados para exibir na tabela, juntando com o nome da categoria
$sql_lista = "SELECT m.*, c.nome as categoria_nome 
              FROM materiais m 
              LEFT JOIN categorias c ON m.categoria_id = c.id 
              ORDER BY m.nome ASC";
$materiais = $pdo->query($sql_lista)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque - Controle Hospitalar</title>
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
                        <a class="nav-link mb-2 rounded" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active mb-2 rounded" href="materiais.php">
                            <i class="bi bi-box-seam me-2"></i> Estoque
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-2 rounded" href="movimentacoes.php">
                            <i class="bi bi-arrow-left-right me-2"></i> Movimentações
                        </a>
                    </li>
                    <?php if ($_SESSION['nivel_acesso'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link mb-2 rounded" href="usuarios.php">
                                <i class="bi bi-people me-2"></i> Usuários
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item border-top mt-3 pt-3">
                        <a class="nav-link text-danger" href="../logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i> Sair
                        </a>
                    </li>
                </ul>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gerenciar Materiais</h1>
                </div>

                <?php echo $mensagem; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Cadastrar Novo Material</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nome do Material *</label>
                                    <input type="text" name="nome" class="form-control" required
                                        placeholder="Ex: Seringa 5ml">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Categoria *</label>
                                    <select name="categoria_id" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($categorias as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nome']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Qtd Inicial</label>
                                    <input type="number" name="quantidade" class="form-control" value="0" min="0">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Validade</label>
                                    <input type="date" name="validade" class="form-control">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Estoque Mínimo</label>
                                    <input type="number" name="estoque_minimo" class="form-control" value="10" min="1">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Salvar
                                Material</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Materiais Cadastrados</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Categoria</th>
                                        <th>Quantidade</th>
                                        <th>Estoque Mínimo</th>
                                        <th>Validade</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($materiais as $item): ?>
                                        <tr>
                                            <td><?php echo $item['id']; ?></td>
                                            <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($item['categoria_nome']); ?></td>
                                            <td>
                                                <span
                                                    class="badge <?php echo ($item['quantidade'] <= $item['estoque_minimo']) ? 'bg-danger' : 'bg-success'; ?>">
                                                    <?php echo $item['quantidade']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $item['estoque_minimo']; ?></td>

                                            <td><?php echo ($item['validade']) ? date('d/m/Y', strtotime($item['validade'])) : '---'; ?>
                                            </td>

                                            <td>
                                                <div class="btn-group">
                                                    <a href="editar_material.php?id=<?php echo $item['id']; ?>"
                                                        class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="excluir_material.php?id=<?php echo $item['id']; ?>"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Tem certeza que deseja excluir este material?')"
                                                        title="Excluir">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>