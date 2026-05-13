<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HospStock - Controle de Estoque Hospitalar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 100px 0;
        }

        .feature-icon {
            font-size: 3rem;
            color: #3498db;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="bi bi-hospital me-2"></i>HospStock
            </a>
            <div class="d-flex">
                <a href="login.php" class="btn btn-outline-primary me-2">Entrar</a>
            </div>
        </div>
    </nav>

    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Gestão Inteligente de Insumos Hospitalares</h1>
            <p class="lead mb-5">Um sistema simples, rápido e seguro para controlar a entrada, saída e validade de
                materiais no ambiente hospitalar.</p>
            <a href="login.php" class="btn btn-light btn-lg px-5 rounded-pill fw-bold text-primary shadow">
                Acessar o Sistema <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container py-4">
            <div class="row text-center g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="card-body">
                            <i class="bi bi-box-seam feature-icon"></i>
                            <h4 class="card-title fw-bold">Controle Rigoroso</h4>
                            <p class="card-text text-muted">Cadastre medicamentos, EPIs e materiais com alertas
                                automáticos de estoque mínimo.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="card-body">
                            <i class="bi bi-arrow-left-right feature-icon"></i>
                            <h4 class="card-title fw-bold">Rastreabilidade</h4>
                            <p class="card-text text-muted">Registre todas as entradas e saídas, sabendo exatamente quem
                                movimentou o quê e quando.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="card-body">
                            <i class="bi bi-speedometer2 feature-icon"></i>
                            <h4 class="card-title fw-bold">Dashboard Intuitivo</h4>
                            <p class="card-text text-muted">Acompanhe os dados críticos do seu estoque em tempo real
                                através de uma interface clara.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> HospStock - Projeto Acadêmico.</p>
            <p class="text-muted small mb-0">Desenvolvido por: [Nome dos Alunos do Grupo]</p>
        </div>
    </footer>

</body>

</html>