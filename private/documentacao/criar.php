<?php
declare(strict_types=1);

// 1. Variável para o header saber recuar até à raiz e carregar o CSS unificado
$prefixo = '../../';

// 2. Includes obrigatórios do sistema
require_once '../../includes/auth.php'; 
require_once '../../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Novo Documento';
$modulo_ativo  = 'documentacao';

$erro_mensagem = '';

// ── Tipos de documento válidos (Igual ao que definimos na listagem) ──────────
const TIPOS_DOCUMENTO = [
    'manual'      => 'Manual Técnico',
    'certificado' => 'Certificado / Calibração',
    'fatura'      => 'Fatura / Compra',
    'outro'       => 'Outro Documento',
];

// ── Query: Carregar Equipamentos para o Dropdown do formulário ───────────────
try {
    $stmt_eq = $pdo->query("SELECT id, codigo, designacao FROM equipamentos ORDER BY codigo ASC");
    $equipamentos_lista = $stmt_eq->fetchAll();
} catch (PDOException $e) {
    $equipamentos_lista = [];
}

// ── Processamento do Formulário (Quando o utilizador clica em Guardar) ────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo         = trim($_POST['titulo'] ?? '');
    $tipo           = trim($_POST['tipo'] ?? '');
    $id_equipamento = (int)($_POST['id_equipamento'] ?? 0);
    $data_doc       = !empty($_POST['data_documento']) ? $_POST['data_documento'] : null;
    $data_val       = !empty($_POST['data_validade']) ? $_POST['data_validade'] : null;
    $observacoes    = trim($_POST['observacoes'] ?? '');
    
    $nome_ficheiro_final = null;

    // Validação simples dos campos obrigatórios
    if ($titulo === '' || $tipo === '' || $id_equipamento === 0) {
        $erro_mensagem = 'Por favor, preencha todos os campos obrigatórios (Título, Tipo e Equipamento).';
    } else {
        // Lógica de Upload do Ficheiro 
        if (isset($_FILES['ficheiro']) && $_FILES['ficheiro']['error'] === UPLOAD_ERR_OK) {
            $ficheiro_nome_orig = $_FILES['ficheiro']['name'];
            $ficheiro_extensao  = strtolower(pathinfo($ficheiro_nome_orig, PATHINFO_EXTENSION));
            
            // Extensões permitidas para segurança académica (PDF, PNG, JPG, DOCX)
            $extensoes_permitidas = ['pdf', 'png', 'jpg', 'jpeg', 'docx'];
            
            if (!in_array($ficheiro_extensao, $extensoes_permitidas, true)) {
                $erro_mensagem = 'Erro: Apenas são permitidos ficheiros PDF, Imagens ou DOCX.';
            } else {
                // Cria um nome único para o ficheiro para evitar que se apague informação com o mesmo nome
                $nome_ficheiro_final = uniqid('doc_', true) . '.' . $ficheiro_extensao;
                $caminho_upload = '../../assets/docs/' . $nome_ficheiro_final;
                
                // Move o ficheiro da pasta temporária para a tua pasta assets/docs/
                if (!move_uploaded_file($_FILES['ficheiro']['tmp_name'], $caminho_upload)) {
                    $nome_ficheiro_final = null;
                    $erro_mensagem = 'Aviso: O documento foi guardado, mas o ficheiro anexado falhou no upload.';
                }
            }
        }

        // Se não houver erros de validação nem de ficheiro, grava no MySQL
        if ($erro_mensagem === '') {
            try {
                $sql = 'INSERT INTO documentacao (id_equipamento, titulo, tipo, data_documento, data_validade, ficheiro, observacoes) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id_equipamento, $titulo, $tipo, $data_doc, $data_val, $nome_ficheiro_final, $observacoes]);

                header('Location: index.php?sucesso=criado');
                exit;
            } catch (PDOException $e) {
                $erro_mensagem = 'Não foi possível registar o documento. Por favor, tente novamente.';
            }
        }
    }
}

require_once '../../includes/header.php';
?>

<!-- ════════════ CONTEÚDO HTML ════════════ -->
<main class="pagina-documentacao container-fluid py-4">

  <!-- Cabeçalho do Formulário -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-white">Registar Novo Documento</h1>
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

  <!-- Card do Formulário  -->
  <div class="card text-white p-4">
    <!-- enctype permite o upload de ficheiros para o PHP ler -->
    <form method="POST" action="criar.php" enctype="multipart/form-data">
      <div class="row g-3">
        
        <!-- Campo: Título do Documento -->
        <div class="col-md-8">
          <label for="titulo" class="form-label">Título do Documento / Nome <span class="text-danger">*</span></label>
          <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ex: Manual de Operação - Monitor Multiparamétrico" required>
        </div>

        <!-- Campo: Tipo de Documento -->
        <div class="col-md-4">
          <label for="tipo" class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
          <select id="tipo" name="tipo" class="form-select" required>
            <option value="" disabled selected>Escolha uma opção...</option>
            <?php foreach (TIPOS_DOCUMENTO as $valor => $label): ?>
              <option value="<?php echo $valor; ?>"><?php echo $label; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo Dinâmico: Seleção de Equipamento (Lê da BD) -->
        <div class="col-md-6">
          <label for="id_equipamento" class="form-label">Equipamento Associado <span class="text-danger">*</span></label>
          <select id="id_equipamento" name="id_equipamento" class="form-select" required>
            <option value="" disabled selected>Escolha o aparelho...</option>
            <?php foreach ($equipamentos_lista as $eq): ?>
              <option value="<?php echo $eq['id']; ?>">
                <?php echo htmlspecialchars($eq['codigo'] . ' — ' . $eq['designacao'], ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Campo: Upload do Ficheiro -->
        <div class="col-md-6">
          <label for="ficheiro" class="form-label">Anexar Ficheiro (PDF, Imagem ou DOCX)</label>
          <input type="file" id="ficheiro" name="ficheiro" class="form-control">
        </div>

        <!-- Campo: Data do Documento -->
        <div class="col-md-6">
          <label for="data_documento" class="form-label">Data de Emissão / Documento</label>
          <input type="date" id="data_documento" name="data_documento" class="form-control">
        </div>

        <!-- Campo: Data de Validade -->
        <div class="col-md-6">
          <label for="data_validade" class="form-label">Data de Validade / Calibração</label>
          <input type="date" id="data_validade" name="data_validade" class="form-control">
        </div>

        <!-- Campo: Observações -->
        <div class="col-12">
          <label for="observacoes" class="form-label">Observações Adicionais</label>
          <textarea id="observacoes" name="observacoes" class="form-control" rows="3" placeholder="Notas sobre revisões, número de contrato ou detalhes importantes..."></textarea>
        </div>

      </div>

      <!-- Botões de Ação -->
      <div class="mt-4 d-flex gap-2 justify-content-end">
        <button type="reset" class="btn btn-outline-secondary text-white border-secondary">
          <i class="bi bi-eraser"></i> Limpar
        </button>
        <button type="submit" class="btn btn-success px-4">
          <i class="bi bi-check-lg"></i> Guardar Documento
        </button>
      </div>

    </form>
  </div>

</main>
<?php include '../../includes/footer.php'; ?>