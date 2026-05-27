<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MedInvent — Gestão de Inventário Hospitalar</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>

  <!-- CSS próprio -->
  <link rel="stylesheet" href="../assets/css/1240773.css"/>
</head>
<body>
    <!-- NAVBAR do Front Office -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">

            <!-- Logo -->
            <a class="navbar-brand" href="index.php">MedInvent</a>

            <!-- Botão mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPublic">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Links de navegação pública -->
            <div class="collapse navbar-collapse" id="navPublic">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#sobre">Sobre Nós</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#funcionalidades">Funcionalidades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#porque">Porquê Nós</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contacto">Contacto</a>
                    </li>
                </ul>
                <!-- Botão de acesso ao sistema -->
                <a href="../login.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-box-arrow-in-right"></i> Aceder ao Sistema
                </a>
            </div>
        </div>
    </nav>

    <!-- Espaço para compensar a navbar fixa -->
  <div style="height: 70px;"></div>
