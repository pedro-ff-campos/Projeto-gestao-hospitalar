<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz e carregar o teu CSS unificado
$prefixo = '../../';

// 2. Includes obrigatórios do sistema
// require_once '../../includes/auth.php'; // Ativas quando o login estiver operacional
require_once '../../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Editar Documento';
$modulo_ativo  = 'documentacao';

$erro_mensagem = '';

// ── 1. Validar e Obter o ID do Documento a Editar ───────────────────────────
$id = max(0, (int)($_GET['id'] ?? 0));

if ($id === 0) {
    header('Location: index.php');
    exit;
}

// ── Tipos de documento válidos ───────────────────────────────────────────────
const TIPOS_DOCUMENTO = [
    'manual'      => 'Manual Técnico',
    'certificado' => 'Certificado / Calibração',
    'fatura'      => 'Fatura / Compra',
    'outro'       => 'Outro Documento',
];

// ── 2. Query: Carregar Equipamentos para o Dropdown ───────────────────────────
try {
    $stmt_eq = $pdo->query("SELECT id, codigo, designacao FROM equipamentos ORDER BY codigo ASC");
    $equipamentos_lista = $stmt_eq->fetchAll();
} catch (PDOException $e) {
    $equipamentos_lista = [];
}

// ── 3. Query: Obter os dados atuais do Documento para preencher o form ────────
try {
    $stmt_doc = $pdo->prepare("SELECT * FROM documentacao WHERE id = ?");
    $stmt_doc->execute([$id]);
    $documento = $stmt_doc->fetch();

    if (!$documento) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

// ── 4. Processamento do Formulário (Quando o utilizador clica em Guardar) ──────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo         = trim($_POST['titulo'] ?? '');
    $tipo           = trim($_POST['tipo'] ?? '');
    $id_equipamento = (int)($_POST['id_equipamento'] ?? 0);
    $data_doc       = !empty($_POST['data_documento']) ? $_POST['data_documento'] : null;
    $data_val       = !empty($_POST['data_validade']) ? $_POST['data_validade'] : null;
    $observacoes    = trim($_POST['observacoes'] ?? '');
    
    // Mantém o nome do ficheiro antigo por padrão
    $nome_ficheiro_final = $documento['ficheiro'];

    if ($titulo === '' || $tipo === '' || $id_equipamento === 0) {
        $erro_mensagem = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        // Lógica de Upload (Apenas se o utilizador enviar um novo ficheiro)
        if (isset($_FILES['ficheiro']) && $_FILES['ficheiro']['error'] === UPLOAD_ERR_OK) {
            $ficheiro_nome_orig = $_FILES['ficheiro']['name'];
            $ficheiro_extensao  = strtolower(pathinfo($ficheiro_nome_orig, PATHINFO_EXTENSION));
            
            $extensoes_permitidas = ['pdf', 'png', 'jpg', 'jpeg', 'docx'];
            
            if (!in_array($ficheiro_extensao, $extensoes_permitidas, true)) {
                $erro_mensagem = 'Erro: Apenas são permitidos ficheiros PDF, Imagens ou DOCX.';
            } else {
                $nome_ficheiro_final = uniqid('doc_', true) . '.' . $ficheiro_extensao;
                $caminho_upload = '../../assets/docs/' . $nome_ficheiro_final;
                
                if (!move_uploaded_file($_FILES['ficheiro']['tmp_name'], $caminho_upload)) {
                    $nome_ficheiro_final = $documento['ficheiro'];
                    $erro_mensagem = 'Aviso: Falhou o upload do novo ficheiro.';
                } else {
                    // Opcional: Apagar o ficheiro antigo do servidor se o upload do novo correu bem
                    if (!empty($documento['ficheiro']) && file_exists('../../assets/docs/' . $documento['ficheiro'])) {
                        unlink('../../assets/docs/' . $documento['ficheiro']);
                    }
                }
            }
        }

        if ($erro_mensagem === '') {
            try {
                $sql = 'UPDATE documentacao 
                        SET id_equipamento = ?, titulo = ?, tipo = ?, data_documento = ?, data_validade = ?, ficheiro = ?, observacoes = ? 
                        WHERE id = ?';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id_equipamento, $titulo, $tipo, $data_doc, $data_val, $nome_ficheiro_final, $observacoes, $id]);

                header('Location: index.php?sucesso=editado');
                exit;
            } catch (PDOException $e) {
                $erro_mensagem = 'Não foi possível atualizar o documento. Tente novamente.';
            }
        }
    }
}

// ── Incluir o header (abre a sidebar e o layout automaticamente) ──────────────
require_once '../../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->
<main class="pagina-documentacao container-fluid py-4">

  <!-- Cabeçalho do Formulário -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Editar Documento</h1>
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

  <!-- Card do Formulário (Usa o teu estilo escuro herdado automaticamente) -->
  <div class="card text-white p-4">
    <form method="POST" action="editar.php?id=<?php echo $id; ?>" enctype="multipart/form-data">
      <div class="row g-3">
        
        <!-- Campo: Título do Documento -->
        <div class="col-md-8">
          <label for="titulo" class="form-label">Título do Documento / Nome <span class="text-danger">*</span></label>
          <input type="text" id="titulo" name="titulo" class="form-control" value="<?php echo htmlspecialchars($documento['titulo'], ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <!-- Campo: Tipo de Documento -->
        <div class="col-md-4">
          <label for="tipo" class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
          <select id="tipo" name="tipo" class="form-select" required>
            <option value="" disabled>Escolha uma opção...</option>
            <?php foreach (TIPOS_DOCUMENTO as $valor => $label): ?>
              <option value="<?php echo $valor; ?>" <?php echo $documento['tipo'] === $valor ? 'selected' : ''; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo Dinâmico: Seleção de Equipamento -->
        <div class="col-md-6">
          <label for="id_equipamento" class="form-label">Equipamento Associado <span class="text-danger">*</span></label>
          <select id="id_equipamento" name="id_equipamento" class="form-select" required>
            <option value="" disabled>Escolha o aparelho...</option>
            <?php foreach ($equipamentos_lista as $eq): ?>
              <option value="<?php echo $eq['id']; ?>" <?php echo $documento['id_equipamento'] === $eq['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($eq['codigo'] . ' — ' . $eq['designacao'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo: Upload do Ficheiro -->
        <div class="col-md-6">
          <label for="ficheiro" class="form-label">Substituir Ficheiro (Deixa vazio para manter o atual)</label>
          <input type="file" id="ficheiro" name="ficheiro" class="form-control">
          <?php if (!empty($documento['ficheiro'])): ?>
            <div class="form-text text-muted small mt-1">
              <i class="bi bi-file-earmark-check"></i> Ficheiro atual: <?php echo htmlspecialchars($documento['ficheiro'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Campo: Data do Documento -->
        <div class="col-md-6">
          <label for="data_documento" class="form-label">Data de Emissão / Documento</label>
          <input type="date" id="data_documento" name="data_documento" class="form-control" value="<?php echo $documento['data_documento']; ?>">
        </div>

        <!-- Campo: Data de Validade -->
        <div class="col-md-6">
          <label for="data_validade" class="form-label">Data de Validade / Calibração</label>
          <input type="date" id="data_validade" name="data_validade" class="form-control" value="<?php echo $documento['data_validade']; ?>">
        </div>

        <!-- Campo: Observações -->
        <div class="col-12">
          <label for="observacoes" class="form-label">Observações Adicionais</label>
          <textarea id="observacoes" name="observacoes" class="form-control" rows="3"><?php echo htmlspecialchars($documento['observacoes'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

      </div>

      <!-- Botões de Ação -->
      <div class="mt-4 d-flex gap-2 justify-content-end">
        <a href="index.php" class="btn btn-outline-secondary text-white border-secondary">
          <i class="bi bi-x-lg"></i> Cancelar
        </a>
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-save"></i> Atualizar Documento
        </button>
      </div>

    </form>
  </div>

</main>
<?php include '../../includes/footer.php'; ?>