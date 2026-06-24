<?php
declare(strict_types=1);

// 1. Define o prefixo de caminhos para recuar até à pasta private/
$prefixo = '../../';

// 2. Inclui o ficheiro de autenticação (Garante a segurança e o l minúsculo)
require_once '../../includes/auth.php';

// 3. Inclui a ligação à base de dados local
require_once '../../includes/db.php';

// Inicialização de variáveis de feedback
$erro = '';
$sucesso = false;

// Processamento do Formulário via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura e limpeza básica de dados (Tratamento de Strings)
    $nome = trim($_POST['nome'] ?? '');
    $nif = trim($_POST['nif'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $morada = trim($_POST['morada'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $pessoa_contacto = trim($_POST['pessoa_contacto'] ?? '');
    $telefone_contacto = trim($_POST['telefone_contacto'] ?? '');
    $tipo = trim($_POST['tipo'] ?? 'Fabricante');
    $observacoes = trim($_POST['observacoes'] ?? '');

    // Validação Obrigatória de Segurança e Regras de Negócio
    if (empty($nome) || empty($nif)) {
        $erro = "Os campos 'Nome do Fornecedor' e 'NIF' são estritamente obrigatórios.";
    } elseif (!is_numeric($nif) || strlen($nif) !== 9) {
        // Validação básica de NIF para o padrão português exigido no guião
        $erro = "O NIF introduzido deve conter exatamente 9 dígitos numéricos.";
    } else {
        try {
            // Prepared Statement para evitar SQL Injection (Boas práticas PDO) [INDEX]
            $sql = "INSERT INTO fornecedores (nome, nif, telefone, email, morada, website, pessoa_contacto, telefone_contacto, tipo, observacoes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $nif, $telefone, $email, $morada, $website, $pessoa_contacto, $telefone_contacto, $tipo, $observacoes]);
            
            // Obtém o ID gerado para documentar no log
            $novo_id = $pdo->lastInsertId();

            // REGISTO DE AUDITORIA: Grava a ação para conformidade ISO 13485 [INDEX]
            $user_id = (int)($_SESSION['user_id'] ?? 0);
            $log_stmt = $pdo->prepare('INSERT INTO logs (utilizador_id, acao, detalhes, criado_at) VALUES (?, ?, ?, NOW())');
            $log_stmt->execute([$user_id, 'CRIAR_FORNECEDOR', "O utilizador registou o fornecedor '$nome' (ID: $novo_id) na base de dados."]);

            // Define sucesso e limpa os campos para o formulário resetar
            $sucesso = true;
            
            // Redireciona de volta à listagem com feedback verde de sucesso
            header('Location: index.php?sucesso=criado');
            exit;

        } catch (PDOException $e) {
            // Tratamento de erros duplicados (ex: NIF já existente)
            if ($e->getCode() === '23000') {
                $erro = "Erro: Já existe um fornecedor registado com este NIF ou E-mail.";
            } else {
                $erro = "Erro técnico na base de dados: " . $e->getMessage();
            }
        }
    }
}
require_once '../../includes/header.php';
?>
<!-- Contentor Principal: O layout adapta-se automaticamente ao Header -->
<div class="main-content pagina-criar-fornecedor">
    
    <!-- Barra de Topo / Cabeçalho do Formulário -->
    <div class="form-header-bar">
        <div class="header-titles">
            <h1>Registar Novo Fornecedor</h1>
            <small>Adicionar parceiro comercial ou fabricante de tecnologia médica</small>
        </div>
        <!-- Botão Voltar para a Listagem -->
        <a href="index.php" class="btn-voltar-listagem">
            <i class="fa-solid fa-arrow-left"></i> Voltar à Listagem
        </a>
    </div>

    <!-- Mensagens de Alerta e Feedback Técnico do PHP -->
    <?php if (!empty($erro)): ?>
        <div class="alert-feedback-erro">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div><?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    <?php endif; ?>

    <!-- Formulário de Criação em Card Dark -->
    <div class="card-formulario-dark">
        <form method="POST" action="criar.php">
            
            <!-- SECÇÃO 1: DADOS INSTITUCIONAIS -->
            <h3 class="secção-titulo-biomedico">
                Dados da Empresa / Instituição
            </h3>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label label-custom">Nome do Fornecedor *</label>
                    <input type="text" name="nome" class="form-control input-custom" placeholder="Ex: Philips Medical Portugal, Lda" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label label-custom">NIF (Contribuinte) *</label>
                    <input type="text" name="nif" class="form-control input-custom" placeholder="9 dígitos numéricos" maxlength="9" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label label-custom">Tipo de Parceiro</label>
                    <select name="tipo" class="form-select select-custom">
                        <option value="Fabricante">Fabricante / Marca</option>
                        <option value="Distribuidor">Distribuidor Oficial</option>
                        <option value="Prestador de Serviços">Assistência Técnico-Clínica</option>
                        <option value="Outro">Outro Técnico</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label label-custom">Telefone Geral</label>
                    <input type="text" name="telefone" class="form-control input-custom" placeholder="Ex: +351 220 000 000">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label label-custom">E-mail Geral</label>
                    <input type="email" name="email" class="form-control input-custom" placeholder="Ex: engenharia@philips.pt">
                </div>

                <div class="col-md-4">
                    <label class="form-label label-custom">Website Oficial</label>
                    <input type="url" name="website" class="form-control input-custom" placeholder="Ex: https://philips.pt">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label label-custom">Morada / Sede Social</label>
                <textarea name="morada" rows="2" class="form-control textarea-custom" placeholder="Rua, Zona Industrial, Código Postal, Cidade"></textarea>
            </div>

            <!-- SECÇÃO 2: GESTOR DE CONTA -->
            <h3 class="secção-titulo-biomedico mt-5">
                Contacto Direto do Gestor de Conta / Especialista Técnico
            </h3>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label label-custom">Nome do Ponto de Contacto</label>
                    <input type="text" name="pessoa_contacto" class="form-control input-custom" placeholder="Ex: Eng. Carlos Rocha">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label label-custom">Telefone Direto / Móvel</label>
                    <input type="text" name="telefone_contacto" class="form-control input-custom" placeholder="Ex: 910 000 000">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label label-custom">Observações Clínicas ou Notas de SLA</label>
                <textarea name="observacoes" rows="3" class="form-control textarea-custom" placeholder="Notas sobre tempos de resposta acordados, contratos de assistência preventiva ou histórico técnico..."></textarea>
            </div>

            <!-- Botões de Ação do Fundo -->
            <div class="form-footer-actions">
                <button type="reset" class="btn-limpar-campos">
                    Limpar Campos
                </button>
                <button type="submit" class="btn-gravar-dados">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Gravar Fornecedor
                </button>
            </div>

        </form>
    </div>

</div>


<?php include '../../includes/footer.php' ?>