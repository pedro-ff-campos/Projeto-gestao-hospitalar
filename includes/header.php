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

  <link rel="preconnect" href="https://googleapis.com">
  <link rel="preconnect" href="https://gstatic.com" crossorigin>
  <link href="https://googleapis.com/css2?family=DM+Serif+Display&display=swap" rel="stylesheet">


  <!-- CSS próprio identificado com número de aluno -->
  <link rel="stylesheet" href="<?php echo $prefixo ?? '../'; ?>assets/css/1240773.css"/>

  <!-- Javascript próprio identificado com número de aluno -->
  <script src="<?php echo $prefixo ?? '../'; ?>assets/js/1240773.js"></script>
</head>
<body>

  <!-- ── BARRA LATERAL VERTICAL (SIDEBAR) ── -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <h1>Med<span>Invent</span></h1>
      <!-- Substitui o texto estático pela variável dinâmica do hospital do utilizador -->
      <span><?php echo htmlspecialchars($_SESSION['user_hospital'] ?? 'Hospital Geral', ENT_QUOTES, 'UTF-8'); ?></span>
    </div>


    <div class="sidebar-links-wrapper">
      <div class="nav-section">Principal</div>
       <a href="/private/dashboard.php">
        <i class="bi bi-pie-chart-fill"></i> Dashboard
      </a>
      <a href="/private/equipamentos/index.php">
        <i class="bi bi-wrench-adjustable"></i> Equipamentos
      </a>
      <a href="/private/localizacoes/index.php">
        <i class="bi bi-geo-alt-fill"></i> Localizações
      </a>
      <div class="nav-section">Gestão</div>
      <a href="/private/fornecedores/index.php">
        <i class="bi bi-truck"></i> Fornecedores
      </a>
      <a href="/private/documentacao/index.php">
        <i class="bi bi-file-earmark-text"></i> Documentação
      </a>
      <a href="/private/garantias/index.php">
        <i class="bi bi-shield-check"></i> Garantias
      </a>
      <a href="/private/contratos/index.php">
        <i class="bi bi-file-earmark-lock"></i> Contratos
      </a>
      
      <div class="nav-section">Sistema</div>
      <a href="/private/pesquisa.php">
        <i class="bi bi-search-heart"></i> Pesquisa Avançada
      </a>
      <a href="/private/relatorios.php">
        <i class="bi bi-graph-up-arrow"></i> Relatórios
      </a>
      <a href="/private/configuracoes.php">
        <i class="bi bi-gear"></i> Configurações
      </a>
      <a href="/private/historico.php">
        <i class="bi bi-clock-history"></i> Histórico
      </a>
    </div>

    <!-- Rodapé do Utilizador com Botão de Seta Independente -->
    <div class="sidebar-footer">
  
      <div class="user-info">
        <!-- Mostra o nome real do utilizador logado -->
        <strong><?php echo htmlspecialchars($_SESSION['user_nome'] ?? 'Utilizador', ENT_QUOTES, 'UTF-8'); ?></strong>
        <small>Administrador</small>
      </div>

      <div class="dropdown">
        <!-- Botão do Bootstrap para abrir o menu -->
        <button class="btn-seta-dropdown dropdown-toggle" type="button" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
          <i class="bi bi-chevron-down"></i>
        </button>
        
        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow" aria-labelledby="dropdownUser">
          <li><a class="dropdown-item" href="<?php echo $prefixo; ?>perfil.php"><i class="bi bi-person me-2"></i> O meu Perfil</a></li>
          <li><a class="dropdown-item" href="<?php echo $prefixo; ?>configuracoes_conta.php"><i class="bi bi-gear me-2"></i> Configurações</a></li>
          
          <li><hr class="dropdown-divider border-secondary"></li>
          
          <!-- O logout recua até à raiz correta e limpa a sessão com segurança -->
          <li><a class="dropdown-item text-danger" href="<?php echo $prefixo; ?>../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Terminar Sessão</a></li>
        </ul>
      </div>

    </div>

  </aside>

  <!-- ── ABRE A CLASSE MAIN ── -->
  <main class="main">
