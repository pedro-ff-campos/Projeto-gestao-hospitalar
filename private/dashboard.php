<!--Comentário- Ficheiro php com o índice do site a criar, de gestão hospitalar -->

<?php include '../includes/header.php'; ?>
<?php include '../includes/auth.php'; ?>

<!-- ==== CONTEÚDO PRINCIPAL ==== -->
<main>
    <h1>Dashboard</h1>

    <!-- Secção de indicadores (KPIs) -->
     <section>
        <h2>Resumo Geral</h2>
        <div>
            <article>
                <h3>Total de Equipamentos</h3>
                <p><!-- valor vindo do PHP/MySQL --></p>
            </article>

            <article>
                <h3>Equipamentos ativos</h3>
                <p><!-- valor vindo do PHP/MySQL --></p>
            </article>

            <article>
                <h3>Em Manutenção</h3>
                <p><!-- valor vindo do PHP/MySQL --></p>
            </article>

            <article>
                <h3>Inativos</h3>
                <p><!-- valor vindo do PHP/MySQL --></p>
            </article>

            <article>
                <h3>Garantias Expiradas</h3>
                <p><!-- valor vindo do PHP/MySQL --></p>
            </article>

            <article>
                <h3>Sem Documentação</h3>
                <p><!-- valor vindo do PHP/MySQL --></p>
            </article>
        </div>
    </section>

    <!-- Secção de Equipamentos Recentes -->
    <section>
        <h2>Equipamentos Recentes</h2>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Designação</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Serviço</th>
                    <th>Estado</th>
                    <th>Criticalidade</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Linhas geradas dinamicamente pelo PHP/MySQL -->
            </tbody>
        </table>

        <a href="equipamentos.php">Ver Todos os Equipamentos</a>
    </section>

    <!-- Secção de Alertas -->
    <section>
        <h2>Alertas</h2>
        <ul>
            <!-- Alertas gerados dinamicamente pelo PHP/MySQL -->
            <!-- Diferentes tipos de alertas como garantias expiradas, manutenções pendentes, equipamentos inativos, etc. -->
        </ul>
    </section>

    <!-- Secção de Equipamentos por Serviço -->
    <section>
        <h2>Equipamentos por Serviço</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Serviço</th>
                    <th>Número de Equipamentos</th>
                </tr>
            </thead>
            <tbody>
                <!-- Linhas geradas dinamicamente pelo PHP/MySQL -->
            </tbody>
        </table>
    </section>
</main>

<?php include '../includes/footer.php'; ?>