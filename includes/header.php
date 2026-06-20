<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MedInvent — Gestão Hospitalar</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>

  <!-- CSS próprio identificado com número de aluno -->
  <link rel="stylesheet" href="../assets/css/1240773.css"/>
</head>
<body>

  <!-- ── BARRA LATERAL VERTICAL (SIDEBAR) ── -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <h1>MedInvent</h1>
      <span>Hospital de S. João</span>
    </div>
    
    <!-- SUBSTITUÍDO: Mudámos de <nav> para <div class="sidebar-links-wrapper"> para bloquear o Bootstrap -->
    <div class="sidebar-links-wrapper">
      <div class="nav-section">Principal</div>
      <a href="dashboard.php" class="active">
        <i class="bi bi-pie-chart-fill"></i> Dashboard
      </a>
      <a href="equipamentos.php">
        <i class="bi bi-wrench-adjustable"></i> Equipamentos
      </a>
      
      <div class="nav-section">Gestão</div>
      <a href="fornecedores.php">
        <i class="bi bi-truck"></i> Fornecedores
      </a>
      <a href="documentacao.php">
        <i class="bi bi-file-earmark-text"></i> Documentação
      </a>
      <a href="garantias.php">
        <i class="bi bi-shield-check"></i> Garantias
      </a>
      <a href="contratos.php">
        <i class="bi bi-file-earmark-lock"></i> Contratos
      </a>
      
      <div class="nav-section">Sistema</div>
      <a href="pesquisa.php">
        <i class="bi bi-search-heart"></i> Pesquisa Avançada
      </a>
      <a href="relatorios.php">
        <i class="bi bi-graph-up-arrow"></i> Relatórios
      </a>
      <a href="configuracoes.php">
        <i class="bi bi-gear"></i> Configurações
      </a>
    </div>
  </div>

    <!-- Rodapé do Utilizador no Fundo da Sidebar -->
    <div class="sidebar-footer">
      <div class="user-info">
        <strong>Nome do Utilizador</strong>
        <small>Administrador</small>
      </div>
    </div>
  </aside>

  <!-- ── ABRE A CLASSE MAIN ── -->
  <main class="main">
