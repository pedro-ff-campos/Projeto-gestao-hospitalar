<?php
declare(strict_types=1);

require_once '../../includes/db.php';
session_start();

// Proteção básica: Se não estiver logado, expulsa 
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: ../../login.php');
    exit;
}

// ── 1. QUERY: Procurar todos os equipamentos reais com a sua localização ────
try {
    // INNER JOIN para cruzar o ID da sala com o nome real do serviço
    $sql = "SELECT e.codigo, e.designacao, e.marca, e.modelo, e.numero_serie, e.estado, e.criticidade, l.servico 
            FROM equipamentos e 
            INNER JOIN localizacoes l ON l.id = e.id_localizacao 
            ORDER BY e.codigo ASC";
    $stmt = $pdo->query($sql);
    $equipamentos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro técnico: Não foi possível carregar os dados biomédicos para exportação.");
}

// ── 2. CAPTURAR O FORMATO PEDIDO VIA GET ─────────────────────────────────────
$formato = $_GET['tipo'] ?? 'csv';
$data_atual = date('Ymd');

// ════════════ CANAL A: EXPORTAÇÃO EXCEL (CSV) ════════════
if ($formato === 'csv') {
    // Força o navegador a fazer o download de um ficheiro CSV [INDEX]
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=inventario_biomedico_' . $data_atual . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // TRUQUE DE ENGENHARIA: Injeta o BOM UTF-8 para o Excel abrir os acentos portugueses sem erros
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Escreve os cabeçalhos das colunas
    fputcsv($output, ['Código Interno', 'Designação do Equipamento', 'Marca', 'Modelo', 'Número de Série', 'Estado Operacional', 'Criticidade', 'Serviço Hospitalar']);
    
    // Escreve as linhas de dados reais
    foreach ($equipamentos as $eq) {
        fputcsv($output, [
            $eq['codigo'], 
            $eq['designacao'], 
            $eq['marca'] ?? '—', 
            $eq['modelo'] ?? '—', 
            $eq['numero_serie'] ?? '—', 
            $eq['estado'], 
            $eq['criticidade'], 
            $eq['servico']
        ]);
    }
    
    fclose($output);
    exit;
}

// ════════════ CANAL B: EXPORTAÇÃO INTERATIVA (JSON) ════════════
if ($formato === 'json') {
    // Força o download de um ficheiro JSON plano, ideal para transferir dados entre softwares [INDEX]
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=inventario_biomedico_' . $data_atual . '.json');
    
    // Imprime o array formatado com recuos e caracteres acentuados legíveis
    echo json_encode($equipamentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ════════════ CANAL C: RELATÓRIO IMPRESSO (PDF NATIVO) ════════════
if ($formato === 'pdf') {
   
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Relatório de Inventário Biomédico — PDF</title>
        <style>
            body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; padding: 30px; margin: 0; }
            .header-pdf { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
            h1 { font-size: 22px; color: #0f172a; margin: 0 0 5px 0; font-weight: 700; }
            .hospital-tag { font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
            .meta-info { font-size: 11px; color: #64748b; margin-top: 5px; }
            table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 15px; }
            th { background-color: #f1f5f9; color: #334155; font-weight: 700; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; border: 1px solid #cbd5e1; padding: 10px; text-align: left; }
            td { border: 1px solid #e2e8f0; padding: 10px; text-align: left; color: #334155; }
            tr:nth-child(even) { background-color: #f8fafc; }
            .badge-pdf { font-weight: 600; text-transform: uppercase; font-size: 9px; }
        </style>
    </head>
    <body>

        <div class="header-pdf">
            <h1>Relatório Oficial de Inventário Tecnológico</h1>
            <div class="hospital-tag"><?php echo htmlspecialchars($_SESSION['user_hospital'] ?? 'Hospital Geral', ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="meta-info">Documento emitido em: <?php echo date('d/m/Y \à\s H:i'); ?> | Responsável técnico: <?php echo htmlspecialchars($_SESSION['user_nome'] ?? 'Engenheiro Biomédico', ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Designação do Dispositivo</th>
                    <th>Marca / Modelo</th>
                    <th>Número Série</th>
                    <th>Serviço</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($equipamentos as $eq): ?>
                <tr>
                    <td style="font-weight: bold; color: #0f172a;">#<?php echo htmlspecialchars($eq['codigo']); ?></td>
                    <td><strong><?php echo htmlspecialchars($eq['designacao']); ?></strong></td>
                    <td><?php echo htmlspecialchars(($eq['marca'] ?? '—') . ' / ' . ($eq['modelo'] ?? '—')); ?></td>
                    <td style="color: #64748b;"><?php echo htmlspecialchars($eq['numero_serie'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($eq['servico']); ?></td>
                    <td><span class="badge-pdf"><?php echo htmlspecialchars($eq['estado']); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- força o navegador a abrir a janela de "Guardar como PDF" automaticamente -->
        <script>
            window.onload = function() {
                window.print();
                // Fecha a aba automaticamente após a impressão para o utilizador não ficar preso numa página em branco
                setTimeout(function() { window.close(); }, 500);
            };
        </script>
    </body>
    </html>
    <?php
    exit;
}
