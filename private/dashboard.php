<!--Comentário- Ficheiro php com o índice do site a criar, de gestão hospitalar -->

<?php include '../includes/header.php'; ?>


<!-- ==== CONTEÚDO PRINCIPAL ==== -->
<main class="main">
    
    <!-- ── BARRA SUPERIOR (TOPBAR) ── -->
    <header class="topbar">
        <div class="topbar-title">
            <h2>Dashboard</h2>
            <p>Hospital de S. João — Demo</p>
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
            <!-- Botão de Notificações com a Bolinha de Alerta -->
            <button class="icon-btn">
                <i class="fa-solid fa-bell"></i>
                <span class="badge-dot"></span>
            </button>
        </div>
    </header>

    <!-- ── ÁREA DE CONTEÚDO PRINCIPAL ── -->
    <div class="content">

        <!-- Secção de indicadores (KPIs) -->
        <section class="kpi-grid">
            <article class="kpi-card">
                <div class="kpi-label">Total Equipamentos</div>
                <div class="kpi-value"><!-- PHP -->1,240</div>
                <div class="kpi-sub">Registados no sistema</div>
                <div class="kpi-icon"><i class="fa-solid fa-box"></i></div>
            </article>

            <article class="kpi-card" style="border-top-color: #10b981;">
                <div class="kpi-label">Ativos</div>
                <div class="kpi-value"><!-- PHP -->980</div>
                <div class="kpi-sub">Em funcionamento</div>
                <div class="kpi-icon"><i class="fa-solid fa-check"></i></div>
            </article>

            <article class="kpi-card" style="border-top-color: #f59e0b;">
                <div class="kpi-label">Em Manutenção</div>
                <div class="kpi-value"><!-- PHP -->45</div>
                <div class="kpi-sub">Na oficina/técnico</div>
                <div class="kpi-icon"><i class="fa-solid fa-wrench"></i></div>
            </article>

            <article class="kpi-card" style="border-top-color: #ef4444;">
                <div class="kpi-label">Inativos</div>
                <div class="kpi-value"><!-- PHP -->12</div>
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
                            <th>Criticalidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Exemplo de linha gerada pelo PHP -->
                        <tr>
                            <td>#EQ-0124</td>
                            <td>Monitor Sinais Vitais</td>
                            <td>Philips</td>
                            <td>Goldway G30E</td>
                            <td>
                                <!-- Bolinha criada com o span vazio para evitar pseudo-elementos -->
                                <span class="badge-status s-ativo">
                                    <span style="width:5px; height:5px; border-radius:50%; background:currentColor; display:inline-block;"></span>
                                    Ativo
                                </span>
                            </td>
                            <td><span class="crit-badge c-alta">Alta</span></td>
                        </tr>
                        <tr>
                            <td>#EQ-0125</td>
                            <td>Bomba de Infusão</td>
                            <td>Braun</td>
                            <td>Infusomat P</td>
                            <td>
                                <span class="badge-status s-manut">
                                    <span style="width:5px; height:5px; border-radius:50%; background:currentColor; display:inline-block;"></span>
                                    Manutenção
                                </span>
                            </td>
                            <td><span class="crit-badge c-media">Média</span></td>
                        </tr>
                    </tbody>
                </table>
                <!-- Coloque isto no seu dashboard.php, logo após o </table> -->
                <div class="panel-footer-action">
                    <a href="equipamentos.php" class="btn-view-all">
                        Ver Todos os Equipamentos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </section>

            <!-- Coluna da Direita: Alertas e Gráficos -->
            <div class="side-panels">
                
                <!-- Bloco de Alertas -->
                <!-- PAINEL DE ALERTAS ATUALIZADO -->
                <section class="panel">
                    <div class="panel-header">
                        <h3>Alertas</h3>
                        <span class="pill pill-muted">8 novos</span>
                    </div>
                    
                    <div class="alert-list">
                        <!-- Alerta 1: Garantia Expirada (Vermelho) -->
                        <article class="alert-item">
                            <div class="alert-icon ai-danger">
                                <i class="bi bi-shield-x"></i>
                            </div>
                            <div class="alert-text">
                                <strong>Garantia expirada</strong>
                                <span>Ventilador Dräger — UCI — há 3 dias</span>
                            </div>
                        </article>

                        <!-- Alerta 2: Garantia a Expirar (Laranja) -->
                        <article class="alert-item">
                            <div class="alert-icon ai-warn">
                                <i class="bi bi-shield-exclamation"></i>
                            </div>
                            <div class="alert-text">
                                <strong>Garantia a expirar</strong>
                                <span>Monitor Philips MP5 — em 12 dias</span>
                            </div>
                        </article>

                        <!-- Alerta 3: Documentação em Falta (Amarelo/Laranja) -->
                        <article class="alert-item">
                            <div class="alert-icon ai-warn">
                                <i class="bi bi-file-earmark-x"></i>
                            </div>
                            <div class="alert-text">
                                <strong>Documentação em falta</strong>
                                <span>5 equipamentos sem manual de serviço</span>
                            </div>
                        </article>

                        <!-- Alerta 4: Manutenção Preventiva (Azul) -->
                        <article class="alert-item">
                            <div class="alert-icon ai-info">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="alert-text">
                                <strong>Manutenção preventiva</strong>
                                <span>Ecógrafo GE — agendada para amanhã</span>
                            </div>
                        </article>
                    </div>
                </section>


                <!-- Bloco do Gráfico de Barras -->
                <section class="panel">
                    <div class="panel-header">
                        <h3>Equipamentos por Serviço</h3>
                    </div>
                    <div class="bar-chart">
                        <!-- Linha do Gráfico -->
                        <div class="bar-row">
                            <span class="bar-label">Cardiologia</span>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: 80%;"></div>
                            </div>
                            <span class="bar-val">42</span>
                        </div>
                        <div class="bar-row">
                            <span class="bar-label">Pediatria</span>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: 45%;"></div>
                            </div>
                            <span class="bar-val">21</span>
                        </div>
                    </div>
                </section>

<?php include '../includes/footer.php'; ?>