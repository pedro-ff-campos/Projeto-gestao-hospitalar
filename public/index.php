<?php include '../includes/header_public.php' ?>

<!-- ==== HERO ==== -->
<section id="hero">
    <div>
        <!-- Etiqueta / tag -->
        <span>Gestão de Tecnologia Hospitalar</span>
 
        <!-- Título principal -->
        <h1>O inventário hospitalar que o seu hospital merece</h1>
 
        <!-- Descrição -->
        <p>
            A MedInvent desenvolve soluções web para a gestão centralizada
            de equipamentos médicos, fornecedores, documentação técnica,
            garantias e contratos.
        </p>

        <!-- Botões de ação -->
        <div>
            <a href="#funcionalidades">Ver Funcionalidades</a>
            <a href="login.php">Aceder ao Sistema</a>
        </div>

        <!-- Estatísticas -->
        <div class="hero-stats">
            <div>
                <strong>+1 500</strong>
                <span>Equipamentos geridos</span>
            </div>
            <div>
                <strong>7</strong>
                <span>Módulos disponíveis</span>
            </div>
            <div>
                <strong>100%</strong>
                <span>Web, sem instalação</span>
            </div>
        </div>
    </div>
</section>
<!-- ==== SOBRE NÓS ==== -->
<section id="sobre">
    <div class="sobre-grid">
        
        <!-- Coluna da Esquerda: Textos Principais e as Caixas de Valores -->
        <div class="sobre-conteudo">
            <span class="section-tag">Sobre Nós</span>
            <h2 class="section-titulo">Uma empresa focada na saúde digital</h2>
            <p class="section-sub">
                A MedInvent é uma empresa especializada no desenvolvimento
                de sistemas de informação para instituições de saúde. A nossa
                missão é simplificar a gestão tecnológica hospitalar, reduzindo
                erros, melhorando a rastreabilidade e apoiando a tomada de
                decisão clínica e administrativa.
            </p>
            
            <!-- Grelha interna 2x2 para os cards de valores -->
            <div class="sobre-valores">
                <article class="valor-card">
                    <i class="bi bi-shield-lock"></i> <!-- Ícone de Segurança -->
                    <h4>Segurança</h4>
                    <p>Dados protegidos com autenticação e controlo de acessos.</p>
                </article>
                
                <article class="valor-card">
                    <i class="bi bi-lightning-charge"></i> <!-- Ícone de Eficiência -->
                    <h4>Eficiência</h4>
                    <p>Interface rápida e intuitiva para uso diário em contexto hospitalar.</p>
                </article>
                
                <article class="valor-card">
                    <i class="bi bi-cpu"></i> <!-- Ícone de Fiabilidade -->
                    <h4>Fiabilidade</h4>
                    <p>Sistema estável e disponível sempre que necessário.</p>
                </article>
                
                <article class="valor-card">
                    <i class="bi bi-headset"></i> <!-- Ícone de Suporte -->
                    <h4>Suporte</h4>
                    <p>Acompanhamento técnico especializado na área da saúde.</p>
                </article>
            </div>
        </div>

        <!-- Coluna da Direita: Painel Escuro (Substitui imagem) -->
        <div class="sobre-imagem">
            
            <div class="sobre-imagem-item">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <strong>Inventário Centralizado</strong>
                    <span>Todos os equipamentos e documentação num único sistema organizado.</span>
                </div>
            </div>
            
            <div class="sobre-imagem-item">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <strong>Localização em tempo real</strong>
                    <span>Rastreie onde cada equipamento está dentro do hospital.</span>
                </div>
            </div>
            
            <div class="sobre-imagem-item">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <strong>Gestão documental</strong>
                    <span>Manuais, certificados, contratos e muito mais sempre acessíveis.</span>
                </div>
            </div>
            
            <div class="sobre-imagem-item">
                <i class="bi bi-check-circle-fill"></i>
                <div>
                    <strong>Alertas automáticos</strong>
                    <span>Notificações de garantias a expirar e manutenções pendentes.</span>
                </div>
            </div>
            
        </div>
    </div>
</section>


<!-- ==== FUNCIONALIDADES ==== -->
<section id="funcionalidades">
    <div class="func-container">
        
        <!-- Cabeçalho com as classes certas -->
        <div class="func-header">
            <span class="section-tag">Funcionalidades</span>
            <h2 class="section-titulo">Tudo o que precisa para gerir o seu parque tecnológico</h2>
            <p class="section-sub">O sistema MedInvent foi desenhado para cobrir todas as necessidades de gestão de inventário hospitalar.</p>
        </div>
        
        <!-- Contentor da Grelha em 3 colunas -->
        <div class="func-grid">
            
            <!-- Card 1 -->
            <article class="func-card">
                <div class="func-icon">
                    <i class="bi bi-box-se"></i> <!-- Ícone de caixa/inventário -->
                </div>
                <h3>Gestão de Equipamentos</h3>
                <p>Registe, consulte e atualize toda a informação dos equipamentos médicos — marca, modelo, número de série, estado e criticidade clínica.</p>
            </article>
            
            <!-- Card 2 -->
            <article class="func-card">
                <div class="func-icon">
                    <i class="bi bi-geo-alt"></i> <!-- Ícone de mapa/localização -->
                </div>
                <h3>Módulo de Localizações</h3>
                <p>Organize os equipamentos por edifício, piso, serviço e sala. Saiba sempre onde se encontra cada dispositivo médico.</p>
            </article>
            
            <!-- Card 3 -->
            <article class="func-card">
                <div class="func-icon">
                    <i class="bi bi-people"></i> <!-- Ícone de pessoas/fornecedores -->
                </div>
                <h3>Gestão de Fornecedores</h3>
                <p>Associe fabricantes, distribuidores e empresas de assistência técnica a cada equipamento de forma clara e estruturada.</p>
            </article>
            
            <!-- Card 4 -->
            <article class="func-card">
                <div class="func-icon">
                    <i class="bi bi-file-earmark-medical"></i> <!-- Ícone de documento médico -->
                </div>
                <h3>Gestão Documental</h3>
                <p>Armazene manuais, certificados de calibração, declarações de conformidade e relatórios técnicos associados a cada equipamento.</p>
            </article>
            
            <!-- Card 5 -->
            <article class="func-card">
                <div class="func-icon">
                    <i class="bi bi-shield-check"></i> <!-- Ícone de escudo/garantia -->
                </div>
                <h3>Garantias e Contratos</h3>
                <p>Controle as datas de garantia e os contratos de manutenção. Receba alertas quando uma garantia estiver prestes a expirar.</p>
            </article>
            
            <!-- Card 6 -->
            <article class="func-card">
                <div class="func-icon">
                    <i class="bi bi-graph-up-arrow"></i> <!-- Ícone de gráfico/dashboard -->
                </div>
                <h3>Dashboard e Relatórios</h3>
                <p>Visualize indicadores chave do parque tecnológico — equipamentos ativos, em manutenção, com garantia expirada e muito mais.</p>
            </article>
            
        </div>
    </div>
</section>

<!-- ==== PORQUÊ NÓS ==== -->
<section id="porque">
    <div class="porque-inner">
        
        <!-- Cabeçalho com classes -->
        <div class="porque-header">
            <span class="section-tag">Porquê Nós</span>
            <h2 class="section-titulo">Desenvolvido especificamente para hospitais</h2>
            <p class="section-sub">Não somos uma solução genérica. O MedInvent foi concebido para responder às necessidades reais da gestão tecnológica em saúde.</p>
        </div>
        
        <!-- Grelha com classes -->
        <div class="porque-grid">
            
            <article class="porque-card">
                <span>100% Web</span>
                <h3>Sem instalações</h3>
                <p>Acede a partir de qualquer computador com browser, dentro da rede hospitalar.</p>
            </article>
            
            <article class="porque-card">
                <span>Base para CMMS</span>
                <h3>Estrutura preparada</h3>
                <p>Estrutura preparada para evoluir para um sistema CMMS completo de gestão de manutenção.</p>
            </article>
            
            <article class="porque-card">
                <span>Pesquisa avançada</span>
                <h3>Filtre tudo</h3>
                <p>Filtre equipamentos por múltiplos critérios — serviço, estado, criticidade, fornecedor e mais.</p>
            </article>
            
            <article class="porque-card">
                <span>Pronto para auditorias</span>
                <h3>Documentação</h3>
                <p>Documentação centralizada e rastreável para suportar auditorias e processos de certificação.</p>
            </article>
            
        </div>
    </div>
</section>

<!-- ==== CTA ==== -->
<section id="cta">
    <h2>Pronto para melhorar a gestão do seu hospital?</h2>
    <p>Entre em contacto ou aceda diretamente ao sistema com as suas credenciais.</p>
    <a href="login.php" class="btn-cta">Aceder ao Sistema</a>
</section>

<!-- ==== CONTACTO ==== -->
<section id="contacto">
    <div class="contacto-grid">
        
        <!-- Coluna da Esquerda: Cabeçalho e Informações de Contacto -->
        <div class="contacto-info-bloco">
            <span class="section-tag">Contacto</span>
            <h2 class="section-titulo">Fale connosco</h2>
            <p class="section-sub">Tem alguma dúvida sobre o sistema ou pretende saber mais sobre os nossos serviços?</p>
    
            <div class="contacto-info">
                <div class="contacto-item">
                    <i class="bi bi-envelope"></i>
                    <div>
                        <strong>Email</strong>
                        <span>geral@medinvent.pt</span>
                    </div>
                </div>
                <div class="contacto-item">
                    <i class="bi bi-telephone"></i>
                    <div>
                        <strong>Telefone</strong>
                        <span>+351 220 000 000</span>
                    </div>
                </div>
                <div class="contacto-item">
                    <i class="bi bi-geo-alt"></i>
                    <div>
                        <strong>Morada</strong>
                        <span>Rua Dr. António Bernardino de Almeida, Porto</span>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Coluna da Direita: Formulário de contacto -->
        <form class="contacto-form">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" placeholder="O seu nome"/>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="o.seu@email.com"/>
            </div>
            <div class="form-group">
                <label for="assunto">Assunto</label>
                <input type="text" id="assunto" name="assunto" placeholder="Assunto da mensagem"/>
            </div>
            <div class="form-group">
                <label for="mensagem">Mensagem</label>
                <textarea id="mensagem" name="mensagem" placeholder="Escreva a sua mensagem aqui..."></textarea>
            </div>
            <button type="submit" class="btn-enviar">Enviar Mensagem</button>
        </form>
    </div>
</section>


<?php include '../includes/footer_public.php' ?>