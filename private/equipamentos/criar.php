<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz e carregar o teu CSS unificado
$prefixo = '../../';

// 2. Includes obrigatórios do sistema
// require_once '../../includes/auth.php'; // Ativas quando o login estiver operacional
require_once '../../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Novo Equipamento';
$modulo_active  = 'equipamentos';

$erro_mensagem = '';

// ── Query: Carregar Localizações para o Dropdown do formulário ───────────────
try {
    // Busca todas as localizações para o utilizador escolher uma no formulário
    $stmt_loc = $pdo->query("SELECT id, edificio, piso, servico, sala FROM localizacoes ORDER BY edificio ASC, servico ASC");
    $localizacoes_lista = $stmt_loc->fetchAll();
} catch (PDOException $e) {
    $localizacoes_lista = [];
}

// ── Query: Carregar Fornecedores para o Dropdown do formulário ───────────────
try {
    // Busca todos os fornecedores registados
    $stmt_forn = $pdo->query("SELECT id, nome FROM fornecedores ORDER BY nome ASC");
    $fornecedores_lista = $stmt_forn->fetchAll();
} catch (PDOException $e) {
    $fornecedores_lista = [];
}

// ── Processamento do Formulário (Quando o utilizador clica em Guardar) ────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recolha e limpeza básica dos dados estruturados conforme a tua base de dados
    $codigo_interno = trim($_POST['codigo_interno'] ?? '');
    $designacao     = trim($_POST['designacao'] ?? '');
    $marca          = trim($_POST['marca'] ?? '');
    $modelo         = trim($_POST['modelo'] ?? '');
    $numero_serie   = trim($_POST['numero_serie'] ?? '');
    $estado         = trim($_POST['estado'] ?? '');
    $criticidade    = trim($_POST['criticidade'] ?? '');
    $id_localizacao = (int)($_POST['id_localizacao'] ?? 0);
    $id_fornecedor  = $_POST['id_fornecedor'] !== '' ? (int)$_POST['id_fornecedor'] : null;

    // Validação de campos obrigatórios
    if ($codigo_interno === '' || $designacao === '' || $estado === '' || $id_localizacao === 0) {
        $erro_mensagem = 'Por favor, preencha todos os campos obrigatórios (Código, Designação, Estado e Localização).';
    } else {
        try {
            // Inserção segura respeitando as colunas exatas do teu SQL
            $sql = 'INSERT INTO equipamentos (codigo, designacao, marca, modelo, numero_serie, estado, criticidade, id_localizacao, id_fornecedor) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$codigo_interno, $designacao, $marca, $modelo, $numero_serie, $estado, $criticidade, $id_localizacao, $id_fornecedor]);

            // Redireciona de volta para a listagem de equipamentos (ou dashboard) com sucesso
            header('Location: index.php?sucesso=criado');
            exit;

        } catch (PDOException $e) {
            // Verifica se o erro é de código duplicado (chave única do código interno)
            if ($e->getCode() === '23000') {
                $erro_mensagem = 'Erro: Já existe um equipamento registado com este Código Interno.';
            } else {
                $erro_mensagem = 'Não foi possível registar o equipamento. Por favor, tente novamente.';
            }
        }
    }
}

// ── Incluir o header (abre a barra lateral e o layout automaticamente) ────────
require_once '../../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->
<!-- Usamos a classe de contexto para reaproveitar os teus estilos escuros unificados -->
<main class="pagina-equipamentos container-fluid py-4">

  <!-- Cabeçalho do Formulário -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Registar Novo Equipamento</h1>
    <a href="index.php" class="btn btn-outline-light">
      <i class="bi bi-arrow-left"></i> Voltar à Lista
    </a>
  </div>

  <!-- Mensagem de Erro Visual -->
  <?php if ($erro_mensagem !== ''): ?>
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <div><?php echo htmlspecialchars($erro_mensagem, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
  <?php endif; ?>

  <!-- Card do Formulário -->
  <div class="card text-white p-4">
    <form method="POST" action="criar.php">
      <div class="row g-3">
        
        <!-- Campo: Código Interno -->
        <div class="col-md-4">
          <label for="codigo_interno" class="form-label">Código Interno / Identificador <span class="text-danger">*</span></label>
          <input type="text" id="codigo_interno" name="codigo_interno" class="form-control" placeholder="Ex: EQ-0126" required>
        </div>

        <!-- Campo: Designação -->
        <div class="col-md-8">
          <label for="designacao" class="form-label">Designação / Nome do Equipamento <span class="text-danger">*</span></label>
          <input type="text" id="designacao" name="designacao" class="form-control" placeholder="Ex: Ventilador de Alta Frequência" required>
        </div>

        <!-- Campo: Marca -->
        <div class="col-md-4">
          <label for="marca" class="form-label">Marca</label>
          <input type="text" id="marca" name="marca" class="form-control" placeholder="Ex: Dräger">
        </div>

        <!-- Campo: Modelo -->
        <div class="col-md-4">
          <label for="modelo" class="form-label">Modelo</label>
          <input type="text" id="modelo" name="modelo" class="form-control" placeholder="Ex: Babylog VN500">
        </div>

        <!-- Campo: Número de Série -->
        <div class="col-md-4">
          <label for="numero_serie" class="form-label">Número de Série</label>
          <input type="text" id="numero_serie" name="numero_serie" class="form-control" placeholder="Ex: SN-987654321">
        </div>

        <!-- Campo Dinâmico: Seleção de Localização (Lê da BD) -->
        <div class="col-md-6">
          <label for="id_localizacao" class="form-label">Localização no Hospital <span class="text-danger">*</span></label>
          <select id="id_localizacao" name="id_localizacao" class="form-select" required>
            <option value="" disabled selected>Escolha a sala / serviço...</option>
            <?php foreach ($localizacoes_lista as $loc): ?>
              <option value="<?php echo $loc['id']; ?>">
                <?php echo htmlspecialchars($loc['edificio'] . ' — ' . $loc['servico'] . ' (Sala ' . $loc['sala'] . ')', ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo Dinâmico: Seleção de Fornecedor (Lê da BD) -->
        <div class="col-md-6">
          <label for="id_fornecedor" class="form-label">Fornecedor Associado</label>
          <select id="id_fornecedor" name="id_fornecedor" class="form-select">
            <option value="">Nenhum fornecedor associado (Opcional)</option>
            <?php foreach ($fornecedores_lista as $forn): ?>
              <option value="<?php echo $forn['id']; ?>">
                <?php echo htmlspecialchars($forn['nome'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo: Estado -->
        <div class="col-md-6">
          <label for="estado" class="form-label">Estado Operacional <span class="text-danger">*</span></label>
          <select id="estado" name="estado" class="form-select" required>
            <option value="" disabled selected>Escolha o estado...</option>
            <option value="Ativo">Ativo (Em funcionamento)</option>
            <option value="Manutenção">Em Manutenção (Oficina/Técnico)</option>
            <option value="Inativo">Inativo (Fora de serviço)</option>
          </select>
        </div>

        <!-- Campo: Criticidade -->
        <div class="col-md-6">
          <label for="criticidade" class="form-label">Criticidade</label>
          <select id="criticidade" name="criticidade" class="form-select">
            <option value="BAIXA">BAIXA</option>
            <option value="MÉDIA" selected>MÉDIA</option>
            <option value="ALTA">ALTA</option>
          </select>
        </div>

      </div>

      <!-- Botões de Ação -->
      <div class="mt-4 d-flex gap-2 justify-content-end">
        <button type="reset" class="btn btn-outline-secondary text-white border-secondary">
          <i class="bi bi-eraser"></i> Limpar
        </button>
        <button type="submit" class="btn btn-success px-4">
          <i class="bi bi-check-lg"></i> Guardar Equipamento
        </button>
      </div>

    </form>
  </div>

</main>
<?php include '../../includes/footer.php'; ?>