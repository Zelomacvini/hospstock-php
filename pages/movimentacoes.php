<?php
// pages/movimentacoes.php
session_start();
require '../config/conexao.php';

// Verifica se está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

$mensagem = '';

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $material_id = $_POST['material_id'];
    $tipo = $_POST['tipo'];
    $quantidade = (int) $_POST['quantidade'];
    $usuario_id = $_SESSION['usuario_id']; // Pega quem está logado

    if (!empty($material_id) && !empty($tipo) && $quantidade > 0) {
        try {
            // Inicia a transação (garante que tudo dê certo ou nada seja feito)
            $pdo->beginTransaction();

            // 1. Verifica o saldo atual se for uma saída
            if ($tipo == 'saida') {
                $stmt_saldo = $pdo->prepare("SELECT quantidade, nome FROM materiais WHERE id = :id");
                $stmt_saldo->execute(['id' => $material_id]);
                $material = $stmt_saldo->fetch(PDO::FETCH_ASSOC);

                if ($material['quantidade'] < $quantidade) {
                    throw new Exception("Estoque insuficiente para {$material['nome']}. Saldo atual: {$material['quantidade']}");
                }
            }

            // 2. Registra a movimentação
            $sql_mov = "INSERT INTO movimentacoes (material_id, usuario_id, tipo, quantidade) 
                        VALUES (:material_id, :usuario_id, :tipo, :quantidade)";
            $stmt_mov = $pdo->prepare($sql_mov);
            $stmt_mov->execute([
                'material_id' => $material_id,
                'usuario_id' => $usuario_id,
                'tipo' => $tipo,
                'quantidade' => $quantidade
            ]);

            // 3. Atualiza o saldo na tabela de materiais
            if ($tipo == 'entrada') {
                $sql_update = "UPDATE materiais SET quantidade = quantidade + :qtd WHERE id = :id";
            } else {
                $sql_update = "UPDATE materiais SET quantidade = quantidade - :qtd WHERE id = :id";
            }
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute(['qtd' => $quantidade, 'id' => $material_id]);

            // Confirma a transação
            $pdo->commit();
            $mensagem = "<div class='alert alert-success'>Movimentação registrada com sucesso!</div>";

        } catch (Exception $e) {
            // Se algo deu errado (como falta de estoque), desfaz tudo
            $pdo->rollBack();
            $mensagem = "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
        }
    } else {
        $mensagem = "<div class='alert alert-warning'>Preencha todos os campos com valores válidos.</div>";
    }
}

// Busca materiais para o formulário
$materiais = $pdo->query("SELECT id, nome, quantidade FROM materiais ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);

// Busca o histórico de movimentações para a tabela
$sql_historico = "SELECT mov.*, mat.nome as material_nome, usr.nome as usuario_nome 
                  FROM movimentacoes mov
                  JOIN materiais mat ON mov.material_id = mat.id
                  JOIN usuarios usr ON mov.usuario_id = usr.id
                  ORDER BY mov.data_registro DESC LIMIT 50";
$historico = $pdo->query($sql_historico)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Movimentações - Controle Hospitalar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: #2c3e50; color: white; }
        .nav-link { color: rgba(255,255,255,.8); }
        .nav-link:hover { color: white; background: rgba(255,255,255,.1); }
        .nav-link.active { background: #3498db; color: white; }
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
                    <a class="nav-link mb-2 rounded" href="materiais.php">
                        <i class="bi bi-box-seam me-2"></i> Estoque
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active mb-2 rounded" href="movimentacoes.php">
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
            <h1 class="h2 mb-4">Entradas e Saídas</h1>

            <?php echo $mensagem; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Registrar Movimentação</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Material *</label>
                                <select name="material_id" class="form-select" required>
                                    <option value="">Selecione o material...</option>
                                    <?php foreach ($materiais as $mat): ?>
                                        <option value="<?php echo $mat['id']; ?>">
                                            <?php echo htmlspecialchars($mat['nome']) . " (Saldo: " . $mat['quantidade'] . ")"; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipo de Movimentação *</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="entrada">Entrada (Adicionar)</option>
                                    <option value="saida">Saída (Retirar)</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label">Quantidade *</label>
                                <input type="number" name="quantidade" class="form-control" required min="1" value="1">
                            </div>
                            <div class="col-md-3 mb-3">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i> Registrar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Histórico Recente</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Data e Hora</th>
                                    <th>Material</th>
                                    <th>Tipo</th>
                                    <th>Quantidade</th>
                                    <th>Responsável</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($historico) > 0): ?>
                                    <?php foreach ($historico as $mov): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($mov['data_registro'])); ?></td>
                                            <td><?php echo htmlspecialchars($mov['material_nome']); ?></td>
                                            <td>
                                                <?php if ($mov['tipo'] == 'entrada'): ?>
                                                    <span class="badge bg-success"><i class="bi bi-arrow-down-circle"></i> Entrada</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-arrow-up-circle"></i> Saída</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $mov['quantidade']; ?></td>
                                            <td><?php echo htmlspecialchars($mov['usuario_nome']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-3">Nenhuma movimentação registrada ainda.</td>
                                    </tr>
                                <?php endif; ?>
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