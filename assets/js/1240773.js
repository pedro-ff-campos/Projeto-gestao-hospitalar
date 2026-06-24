/**
 * ==========================================================================
 * MEDINVENT - JAVASCRIPT GLOBAL DE INTERATIVIDADE HOSPITALAR
 * ==========================================================================
 */
//alert("O JavaScript foi carregado com sucesso!");//

document.addEventListener('DOMContentLoaded', function () {

    // ── 1. CAIXA DE CONFIRMAÇÃO ANTES DE APAGAR (Mecanismo de Segurança) ──
    const botoesEliminar = document.querySelectorAll('.acao-eliminar');

    botoesEliminar.forEach(function (botao) {
        botao.addEventListener('click', function (evento) {
            // Trava o link temporariamente para o navegador não saltar de página
            evento.preventDefault();

            // Mostra a caixa de confirmação nativa do navegador
            const confirmacao = confirm('ATENÇÃO: Tens a certeza de que desejas eliminar permanentemente este registo do parque tecnológico? Esta ação não pode ser desfeita.');

            // Se o engenheiro clicar em "OK", o JavaScript liberta o caminho para o eliminar.php
            if (confirmacao) {
                window.location.href = this.getAttribute('href');
            }
        });
    });


    // ── 2. VALIDAÇÃO DINÂMICA DE INPUTS (Evita submissões com dados em falta) ──
    const formularios = document.querySelectorAll('form');

    formularios.forEach(function (form) {
        form.addEventListener('submit', function (evento) {
            let formValido = true;
            
            // Procura todos os inputs obrigatórios dentro do formulário que está a ser enviado
            const camposObrigatorios = form.querySelectorAll('[required]');

            camposObrigatorios.forEach(function (campo) {
                // Se o campo estiver em branco ou apenas com espaços
                if (campo.value.trim() === '') {
                    formValido = false;
                    campo.style.borderColor = '#ef4444'; 
                } else {
                    campo.style.borderColor = 'rgba(255, 255, 255, 0.08)'; 
                }
            });

            // Se algum campo falhou, o JavaScript bloqueia o envio e avisa o utilizador
            if (!formValido) {
                evento.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios assinalados com o asterisco (*).');
            }
        });
    });

});
// ── MENU FLUTUANTE DE NOTIFICAÇÕES (DASHBOARD) ──
const btnSino = document.getElementById('btnSinoNotificacoes');
const menuNotif = document.getElementById('menuNotificacoes');

if (btnSino && menuNotif) {
    btnSino.addEventListener('click', function (evento) {
        evento.stopPropagation();
        
        // Versão blindada: usamos a classe 'mostrar' do CSS ou alternamos o estilo direto
        if (menuNotif.style.display === 'none' || menuNotif.style.display === '') {
            menuNotif.style.display = 'block';
        } else {
            menuNotif.style.display = 'none';
        }
    });

    document.addEventListener('click', function (evento) {
        if (!menuNotif.contains(evento.target) && evento.target !== btnSino) {
            menuNotif.style.display = 'none';
        }
    });
}


