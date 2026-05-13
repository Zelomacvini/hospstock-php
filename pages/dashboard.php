<?php
// pages/dashboard.php
session_start();
require '../config/conexao.php';

// Verificação de segurança: Se não houver sessão, volta para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

// ==========================================
// 1. CONSULTAS DOS CARDS SUPERIORES
// ==========================================
$totalMateriais = $pdo->query("SELECT COUNT(*) FROM materiais")->fetchColumn();
$estoqueBaixo = $pdo->query("SELECT COUNT(*) FROM materiais WHERE quantidade <= estoque_minimo")->fetchColumn();
$totalCategorias = $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();

// ==========================================
// 2. CONSULTAS PARA OS GRÁFICOS
// ==========================================

$sql_cat = "SELECT c.nome, COUNT(m.id) as total 
            FROM categorias c 
            LEFT JOIN materiais m ON c.id = m.categoria_id 
            GROUP BY c.id";
$dados_cat = $pdo->query($sql_cat)->fetchAll(PDO::FETCH_ASSOC);

$cat_nomes = [];
$cat_totais = [];
foreach ($dados_cat as $row) {
    $cat_nomes[] = $row['nome'];
    $cat_totais[] = $row['total'];
}

$sql_top = "SELECT nome, SUM(quantidade) as quantidade FROM materiais GROUP BY nome ORDER BY quantidade DESC LIMIT 5";
$dados_top = $pdo->query($sql_top)->fetchAll(PDO::FETCH_ASSOC);

$top_nomes = [];
$top_quantidades = [];
foreach ($dados_top as $row) {
    $top_nomes[] = $row['nome'];
    $top_quantidades[] = $row['quantidade'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Controle Hospitalar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .card-icon {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            right: 10px;
            bottom: 10px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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
                        <a class="nav-link active mb-2 rounded" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-2 rounded" href="materiais.php">
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
                    <h1 class="h2">Bem-vindo, <?php echo $_SESSION['usuario_nome']; ?>!</h1>
                    <div class="text-muted">Nível: <?php echo ucfirst($_SESSION['nivel_acesso']); ?></div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3 shadow">
                            <div class="card-body">
                                <h5 class="card-title">Materiais Cadastrados</h5>
                                <p class="card-text fs-2 fw-bold"><?php echo $totalMateriais; ?></p>
                                <i class="bi bi-box-seam card-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-danger mb-3 shadow">
                            <div class="card-body">
                                <h5 class="card-title">Estoque Crítico</h5>
                                <p class="card-text fs-2 fw-bold"><?php echo $estoqueBaixo; ?></p>
                                <i class="bi bi-exclamation-triangle card-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3 shadow">
                            <div class="card-body">
                                <h5 class="card-title">Categorias</h5>
                                <p class="card-text fs-2 fw-bold"><?php echo $totalCategorias; ?></p>
                                <i class="bi bi-tags card-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Distribuição por Categoria</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center">
                                <div class="chart-container">
                                    <canvas id="graficoCategorias"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Top 5 - Maior Volume em Estoque</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="graficoEstoque"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Convertendo os dados do PHP (Arrays) para Javascript (JSON)
        const catNomes = <?php echo json_encode($cat_nomes); ?>;
        const catTotais = <?php echo json_encode($cat_totais); ?>;

        const topNomes = <?php echo json_encode($top_nomes); ?>;
        const topQuantidades = <?php echo json_encode($top_quantidades); ?>;

        // Configuração do Gráfico de Pizza (Categorias)
        const ctxCat = document.getElementById('graficoCategorias').getContext('2d');
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: catNomes,
                datasets: [{
                    data: catTotais,
                    backgroundColor: ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6', '#34495e'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        const ctxTop = document.getElementById('graficoEstoque').getContext('2d');
        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: topNomes,
                datasets: [{
                    label: 'Quantidade em Estoque',
                    data: topQuantidades,
                    backgroundColor: '#3498db',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
</body>

</html>