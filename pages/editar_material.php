<?php
// pages/editar_material.php
session_start();
require '../config/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: materiais.php");
    exit;
}

// Busca os dados atuais do material
$stmt = $pdo->prepare("SELECT * FROM materiais WHERE id = ?");
$stmt->execute([$id]);
$material = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$material) {
    header("Location: materiais.php");
    exit;
}

// Busca categorias para o select
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $categoria_id = $_POST['categoria_id'];
    $estoque_minimo = $_POST['estoque_minimo'];
    $validade = $_POST['validade']; // Corrigido para validade

    // Corrigido o nome da coluna no UPDATE
    $sql = "UPDATE materiais SET nome = ?, categoria_id = ?, estoque_minimo = ?, validade = ? WHERE id = ?";
    $pdo->prepare($sql)->execute([$nome, $categoria_id, $estoque_minimo, $validade, $id]);

    header("Location: materiais.php?sucesso=editado");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Editar Material - HospStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Editar Material #<?php echo $id; ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nome do Material</label>
                        <input type="text" name="nome" class="form-control"
                            value="<?php echo htmlspecialchars($material['nome']); ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categoria</label>
                            <select name="categoria_id" class="form-select">
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $material['categoria_id']) ? 'selected' : ''; ?>>
                                        <?php echo $cat['nome']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Estoque Mínimo</label>
                            <input type="number" name="estoque_minimo" class="form-control"
                                value="<?php echo $material['estoque_minimo']; ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Validade</label>
                            <input type="date" name="validade" class="form-control"
                                value="<?php echo $material['validade']; ?>">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">Salvar Alterações</button>
                        <a href="materiais.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>