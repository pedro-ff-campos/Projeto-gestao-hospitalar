<?php
declare(strict_types=1);

// 1. Variável para o header saber carregar o teu CSS a partir desta subpasta
$prefixo = '../';

// 2. Includes obrigatórios do sistema
// require_once '../includes/auth.php'; // Ativas quando o login estiver operacional
require_once '../includes/db.php';     

// ── Variáveis para o cabeçalho do site ───────────────────────────────────────
$titulo_pagina = 'Dashboard';
$modulo_ativo  = 'dashboard';

$hoje = (new DateTimeImmutable('today'))->format('Y-m-d');
$hoje_dt = new DateTimeImmutable('today'); // 


// ── QUERY 1: Contagens Reais para os 4 Blocos Superiores ─────────────────────
try {
    $total_equipamentos = (int) $pdo->query("SELECT COUNT(*) FROM equipamentos")->fetchColumn();
    $total_ativos       = (int) $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE estado = 'Ativo'")->fetchColumn();
    $total_manutencao   = (int) $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE estado = 'Manutenção'")->fetchColumn();
    $total_inativos     = (int) $pdo->query("SELECT COUNT(*) FROM equipamentos WHERE estado = 'Inativo'")->fetchColumn();
} catch (PDOException $e) {
    $total_equipamentos = $total_ativos = $total_manutencao = $total_inativos = 0;
}

// ── QUERY 2: Obter os 5 Equipamentos mais recentes inseridos na BD ───────────
try {
    $stmt_rec = $pdo->query("
        SELECT id, codigo, designacao, marca, modelo, estado, criticidade 
        FROM equipamentos 
        ORDER BY id DESC 
        LIMIT 5
    ");
    $equipamentos_recentes = $stmt_rec->fetchAll();
} catch (PDOException $e) {
    $equipamentos_recentes = [];
}

// ── QUERY 3: Contar Equipamentos por Serviço Hospitalar (Para as Barras) ─────
try {
    $stmt_serv = $pdo->query("
        SELECT l.servico, COUNT(e.id) AS total_maquinas
        FROM localizacoes l
        LEFT JOIN equipamentos e ON e.id_localizacao = l.id
        WHERE l.servico IS NOT NULL AND l.servico != ''
        GROUP BY l.id
        ORDER BY total_maquinas DESC
        LIMIT 5
    ");
    $servicos_dados = $stmt_serv->fetchAll();
} catch (PDOException $e) {
    $servicos_dados = [];
}
// ── QUERY 4: Procurar Garantias Expiradas ou a Expira (Máximo 2 para não encher) ──
try {
    $stmt_alertas_garantias = $pdo->query("
        SELECT g.referencia, g.data_fim, e.designacao, e.codigo, l.servico AS nome_servico
        FROM garantias g
        INNER JOIN equipamentos e ON e.id = g.id_equipamento
        INNER JOIN localizacoes l ON l.id = e.id_localizacao
        WHERE g.data_fim <= DATE_ADD('$hoje', INTERVAL 30 DAY)
        ORDER BY g.data_fim ASC
        LIMIT 2
    ");
    $alertas_garantias = $stmt_alertas_garantias->fetchAll();
} catch (PDOException $e) {
    $alertas_garantias = [];
}

// ── QUERY 5: Contar quantos Manuais/Certificados estão fora da validade ──────
try {
    $docs_fora_validade = (int) $pdo->query("
        SELECT COUNT(*) 
        FROM documentacao 
        WHERE data_validade < '$hoje'
    ")->fetchColumn();
} catch (PDOException $e) {
    $docs_fora_validade = 0;
}

// ── QUERY 6: Procurar Contratos de Manutenção Preventiva Urgentes ────────────
try {
    $stmt_alertas_contratos = $pdo->query("
        SELECT c.numero_contrato, c.data_fim, e.designacao
        FROM contratos c
        INNER JOIN equipamentos e ON e.id = c.id_equipamento
        WHERE c.tipo = 'manutencao_preventiva' AND c.data_fim BETWEEN '$hoje' AND DATE_ADD('$hoje', INTERVAL 15 DAY)
        ORDER BY c.data_fim ASC
        LIMIT 1
    ");
    $alertas_contratos = $stmt_alertas_contratos->fetchAll();
} catch (PDOException $e) {
    $alertas_contratos = [];
}

// Calcula se a soma de qualquer um dos problemas biomédicos reais é maior que zero
$tem_alertas_urgentes = (count($alertas_garantias) + $docs_fora_validade + count($alertas_contratos)) > 0;

include '../includes/header.php'; ?>


<!-- ==== CONTEÚDO PRINCIPAL ==== -->
<main class="main">
    
    <!-- ── BARRA SUPERIOR (TOPBAR) ── -->
    <header class="topbar">
        <div class="topbar-title">
            <h2>Dashboard</h2>
            <!-- Substitui o texto estático pela variável dinâmica do hospital do utilizador -->
            <p><?php echo htmlspecialchars($_SESSION['user_hospital'] ?? 'Hospital Geral', ENT_QUOTES, 'UTF-8'); ?> — Painel Técnico</p>
        </div>


        <div class="topbar-right">

            <div class="topbar-search">
                <i class="bi bi-search search-icon"></i>
                <input type="text" placeholder="Pesquisar equipamento..." name="search" />
            </div>
            <!-- Botão Principal com Ícone -->
            <button class="btn-tbar">
                <i class="fa-solid fa-plus"></i> Novo Equipamento
            </button>
    
            <!-- Contentor de Notificações Hospitalares -->
            <div class="notificacoes-container-fix" style="position: relative; display: inline-block; vertical-align: middle;">
                
                
                <button class="icon-btn" id="btnSinoNotificacoes" type="button" style="cursor: pointer;" 
                        onclick="event.stopPropagation(); var m = document.getElementById('menuNotificacoes'); m.style.display = (m.style.display === 'none' || m.style.display === '') ? 'block' : 'none';">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($tem_alertas_urgentes): ?>
                        <span class="badge-dot"></span>
                    <?php endif; ?>
                </button>

                <!-- MENU FLUTUANTE -->
                <div class="notificacoes-dropdown" id="menuNotificacoes" style="display: none; position: absolute; top: 48px; right: 0; width: 290px; background-color: #111a2e; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 10px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6); z-index: 99999;">
                    
                    <div class="notif-dropdown-header" style="padding: 12px 14px; border-bottom: 1px solid rgba(255, 255, 255, 0.06); font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">
                        Notificações Urgentes
                    </div>
                    
                    <div class="notif-dropdown-body" style="max-height: 260px; overflow-y: auto;">
                        <?php if (!$tem_alertas_urgentes): ?>
                            <div class="notif-item-vazio" style="text-align: center; padding: 24px 14px; color: #94a3b8; font-size: 13px;">
                                <i class="fa-solid fa-circle-check" style="color: #00cc99; font-size: 1.4rem; margin-bottom: 8px; display: block; text-align: center;"></i>
                                <span style="color: #94a3b8 !important;">Nenhum alerta pendente.</span>
                            </div>
                        <?php else: ?>
                            
                            <!-- Alertas de Garantias -->
                            <?php foreach ($alertas_garantias as $alerta_g): ?>
                                <div class="notif-item" style="display: flex; align-items: start; gap: 12px; padding: 12px 14px; border-bottom: 1px solid rgba(255, 255, 255, 0.04);">
                                    <i class="fa-solid fa-shield-halved text-warning" style="font-size: 14px; margin-top: 3px;"></i>
                                    <div class="notif-item-content">
                                        <strong style="display: block; font-size: 13px; color: #ffffff; margin: 0;">Garantia em risco</strong>
                                        <span style="display: block; font-size: 11px; color: #94a3b8; margin-top: 2px;"><?php echo htmlspecialchars($alerta_g['designacao'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Alertas de Documentos -->
                            <?php if ($docs_fora_validade > 0): ?>
                                <div class="notif-item" style="display: flex; align-items: start; gap: 12px; padding: 12px 14px; border-bottom: 1px solid rgba(255, 255, 255, 0.04);">
                                    <i class="fa-solid fa-file-circle-exclamation text-danger" style="font-size: 14px; margin-top: 3px;"></i>
                                    <div class="notif-item-content">
                                        <strong style="display: block; font-size: 13px; color: #ffffff; margin: 0;">Manuais Expirados</strong>
                                        <span style="display: block; font-size: 11px; color: #94a3b8; margin-top: 2px;">Existem <?php echo $docs_fora_validade; ?> documentos fora de validade.</span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Alertas de Contratos -->
                            <?php foreach ($alertas_contratos as $alerta_c): ?>
                                <div class="notif-item" style="display: flex; align-items: start; gap: 12px; padding: 12px 14px; border-bottom: 1px solid rgba(255, 255, 255, 0.04);">
                                    <i class="fa-solid fa-calendar-check text-info" style="font-size: 14px; margin-top: 3px;"></i>
                                    <div class="notif-item-content">
                                        <strong style="display: block; font-size: 13px; color: #ffffff; margin: 0;">Preventiva Próxima</strong>
                                        <span style="display: block; font-size: 11px; color: #94a3b8; margin-top: 2px;"><?php echo htmlspecialchars($alerta_c['designacao'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <script>
            document.addEventListener('click', function(e) {
                var menu = document.getElementById('menuNotificacoes');
                var btn = document.getElementById('btnSinoNotificacoes');
                if (menu && btn && !menu.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
                    menu.style.display = 'none';
                }
            });
            </script>



        </div>
    </header>

    <!-- ── ÁREA DE CONTEÚDO PRINCIPAL ── -->
    <div class="content">

        <!-- Secção de indicadores (KPIs) -->
        <section class="kpi-grid">
            <!-- Bloco 1: Total -->
            <article class="kpi-card">
                <div class="kpi-label">Total Equipamentos</div>
                <div class="kpi-value">
                    <?php echo number_format($total_equipamentos, 0, ',', '.'); ?>
                </div>
                <div class="kpi-sub">Registados no sistema</div>
                <div class="kpi-icon"><i class="fa-solid fa-box"></i></div>
            </article>

            <!-- Bloco 2: Ativos -->
            <article class="kpi-card" style="border-top-color: #10b981;">
                <div class="kpi-label">Ativos</div>
                <div class="kpi-value">
                    <?php echo number_format($total_ativos, 0, ',', '.'); ?>
                </div>
                <div class="kpi-sub">Em funcionamento</div>
                <div class="kpi-icon"><i class="fa-solid fa-check"></i></div>
            </article>

            <!-- Bloco 3: Em Manutenção -->
            <article class="kpi-card" style="border-top-color: #f59e0b;">
                <div class="kpi-label">Em Manutenção</div>
                <div class="kpi-value">
                    <?php echo number_format($total_manutencao, 0, ',', '.'); ?>
                </div>
                <div class="kpi-sub">Na oficina/técnico</div>
                <div class="kpi-icon"><i class="fa-solid fa-wrench"></i></div>
            </article>

            <!-- Bloco 4: Inativos -->
            <article class="kpi-card" style="border-top-color: #ef4444;">
                <div class="kpi-label">Inativos</div>
                <div class="kpi-value">
                    <?php echo number_format($total_inativos, 0, ',', '.'); ?>
                </div>
                <div class="kpi-sub">Fora de serviço</div>
                <div class="kpi-icon"><i class="fa-solid fa-xmark"></i></div>
            </article>
        </section>


        <!-- ── GRELHA INFERIOR (TABELA + PAINEL LATERAL) ── -->
        <div class="grid-lower">
            
            <!-- Coluna da Esquerda: Tabela de Equipamentos -->
            <section class="panel">
                <div class="panel-header">
                    <h3>Equipamentos Recentes</h3>
                    <span class="pill pill-accent">Atualizado</span>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Designação</th>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Estado</th>
                            <th>Criticidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($equipamentos_recentes)): ?>
                            <!-- Estado vazio caso a base de dados não tenha equipamentos -->
                            <tr>
                                <td colspan="6" class="tabela-vazia text-center py-4" style="color: #94a3b8; font-size: 14px;">
                                    Nenhum equipamento registado recentemente.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($equipamentos_recentes as $eq): ?>
                                <?php
                                    // 1. Define a classe da bolinha de estado com base no valor da BD
                                    $classe_estado = 's-inativo'; // Padrão
                                    if ($eq['estado'] === 'Ativo') {
                                        $classe_estado = 's-ativo';
                                    } elseif ($eq['estado'] === 'Manutenção') {
                                        $classe_estado = 's-manut';
                                    }

                                    // 2. Define a classe da criticidade com base no valor da BD
                                    $classe_crit = 'c-baixa'; // Padrão
                                    if ($eq['criticidade'] === 'ALTA') {
                                        $classe_crit = 'c-alta';
                                    } elseif ($eq['criticidade'] === 'MÉDIA') {
                                        $classe_crit = 'c-media';
                                    }
                                ?>
                                <tr>
                                    <!-- Injetamos o cardinal antes do código para manter o teu padrão visual -->
                                    <td>#<?php echo htmlspecialchars($eq['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><strong><?php echo htmlspecialchars($eq['designacao'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($eq['marca'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($eq['modelo'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <span class="badge-status <?php echo $classe_estado; ?>">
                                            <span style="width:5px; height:5px; border-radius:50%; background:currentColor; display:inline-block;"></span>
                                            <?php echo htmlspecialchars($eq['estado'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="crit-badge <?php echo $classe_crit; ?>">
                                            <?php echo ucfirst(strtolower($eq['criticidade'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div class="panel-footer-action">
                    <!-- Ajustado o link para apontar para a tua pasta de listagem de equipamentos -->
                    <a href="equipamentos/index.php" class="btn-view-all">
                        Ver Todos os Equipamentos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </section>

            <!-- Coluna da Direita: Alertas e Gráficos -->
            <div class="side-panels">
                
                <!-- Bloco de Alertas -->
            
                <section class="panel">
                    <div class="panel-header">
                        <h3>Alertas</h3>
                        <?php 
                        // Contamos o total de alertas ativos para preencher o teu contador dinamicamente
                        $total_alertas_ativos = count($alertas_garantias) + count($alertas_contratos) + ($docs_fora_validade > 0 ? 1 : 0);
                        ?>
                        <span class="pill pill-muted"><?php echo $total_alertas_ativos; ?> novos</span>
                    </div>
                    
                    <div class="alert-list">
                        
                        <!-- 1. ALERTAS DE GARANTIAS (Lê os dados reais da Query 4) -->
                        <?php foreach ($alertas_garantias as $alerta_g): ?>
                            <?php 
                            $data_f = new DateTimeImmutable($alerta_g['data_fim']);
                            $expirado = $data_f < new DateTimeImmutable('today');
                            
                            // Calcula a diferença de dias para escrever "há X dias" ou "em X dias"
                            $intervalo = $hoje_dt->diff($data_f);
                            $dias = $intervalo->days;
                            $texto_prazo = $expirado ? "há $dias dias" : "em $dias dias";
                            ?>
                            <article class="alert-item">
                            
                                <div class="alert-icon <?php echo $expirado ? 'ai-danger' : 'ai-warn'; ?>">
                                    <i class="bi <?php echo $expirado ? 'bi-shield-x' : 'bi-shield-exclamation'; ?>"></i>
                                </div>
                                <div class="alert-text">
                                    <strong><?php echo $expirado ? 'Garantia expirada' : 'Garantia a expirar'; ?></strong>
                                    <span><?php echo htmlspecialchars($alerta_g['designacao'] . ' — ' . $alerta_g['nome_servico'] . ' — ' . $texto_prazo, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>

                        <!-- 2. ALERTA DE DOCUMENTAÇÃO (Lê os dados reais da Query 5) -->
                        <?php if ($docs_fora_validade > 0): ?>
                            <article class="alert-item">
                                <div class="alert-icon ai-warn">
                                    <i class="bi bi-file-earmark-x"></i>
                                </div>
                                <div class="alert-text">
                                    <strong>Documentação em falta</strong>
                                    <span><?php echo $docs_fora_validade; ?> equipamento(s) com manuais ou prazos fora da validade</span>
                                </div>
                            </article>
                        <?php endif; ?>

                        <!-- 3. ALERTA DE MANUTENÇÃO PREVENTIVA (Lê os dados reais da Query 6) -->
                        <?php foreach ($alertas_contratos as $alerta_c): ?>
                            <?php 
                            $data_f_c = new DateTimeImmutable($alerta_c['data_fim']);
                            $intervalo_c = $hoje_dt->diff($data_f_c);
                            $dias_c = $intervalo_c->days;
                            
                            $texto_contrato = ($dias_c === 0) ? "agendada para hoje" : (($dias_c === 1) ? "agendada para amanhã" : "agendada em $dias_c dias");
                            ?>
                            <article class="alert-item">
                                <div class="alert-icon ai-info">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <div class="alert-text">
                                    <strong>Manutenção preventiva</strong>
                                    <span><?php echo htmlspecialchars($alerta_c['designacao'] . ' — ' . $texto_contrato, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>

                        <!-- CASO O HOSPITAL NÃO TENHA NENHUM ALERTA ATIVO -->
                        <?php if ($total_alertas_ativos === 0): ?>
                            <div class="text-center py-4 text-muted small" style="font-size: 13px;">
                                <i class="bi bi-check-circle text-success d-block mb-1" style="font-size: 20px;"></i>
                                Nenhum alerta pendente no sistema.
                            </div>
                        <?php endif; ?>

                    </div>
                </section>



                <!-- Bloco do Gráfico de Barras -->
                <section class="panel">
                    <div class="panel-header">
                        <h3>Equipamentos por Serviço</h3>
                    </div>
                    <div class="bar-chart">
                        <?php if (empty($servicos_dados)): ?>
                            
                            <div class="text-center py-4 text-muted small" style="font-size: 13px;">
                                Nenhum dado de serviço disponível.
                            </div>
                        <?php else: ?>
                            <?php foreach ($servicos_dados as $serv): ?>
                                <?php 
                                    // Calcula a percentagem real para esticar a barra de preenchimento
                                    $percentagem = $total_equipamentos > 0 ? ($serv['total_maquinas'] / $total_equipamentos) * 100 : 0;
                                ?>
                                <!-- Linha do Gráfico Dinâmica -->
                                <div class="bar-row">
                                    <span class="bar-label"><?php echo htmlspecialchars($serv['servico'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: <?php echo $percentagem; ?>%;"></div>
                                    </div>
                                    <span class="bar-val"><?php echo $serv['total_maquinas']; ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

<?php include '../includes/footer.php'; ?>