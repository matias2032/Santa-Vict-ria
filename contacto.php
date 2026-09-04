<!-- contacto.php -->
<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/idioma.php';
require_once __DIR__ . '/config/disponibilidade.php';
$tituloPagina = t('contacto.titulo_pagina');

// Lista de tratamentos para o formulário (inclui preço)
$tratamentos = buscarTratamentosTraduzidos($pdo, $idioma);
$diasPorTratamento = buscarDiasPorTratamentos($pdo, array_column($tratamentos, 'id_tratamento'));

// Estado vindo do processar_agendamento.php (via redirect com querystring)
$estado = $_GET['estado'] ?? null; // 'sucesso', 'erro' ou 'indisponivel'
$mensagemIndisponibilidade = $_GET['msg'] ?? '';
$dataPreSelecionada = $_GET['data'] ?? '';
$tratamentosPreSelecionados = [];
if (isset($_GET['tratamento'])) {
    $tratamentosPreSelecionados = array_map('intval', (array)$_GET['tratamento']);
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="secao secao-pagina-topo">
    <div class="container">
        <p class="eyebrow"><?= htmlspecialchars(t('contacto.topo.eyebrow')) ?></p>
        <h1><?= htmlspecialchars(t('contacto.topo.titulo')) ?></h1>
        <p class="texto-lead" style="max-width:640px">
            <?= htmlspecialchars(t('contacto.topo.texto')) ?>
        </p>
    </div>
</section>

<section class="secao" id="agendamento">
    <div class="container grelha-contacto">
        <div>
            <h2><?= htmlspecialchars(t('contacto.info.titulo')) ?></h2>

            <div class="info-contacto-item">
                <h3><?= htmlspecialchars(t('contacto.info.morada_titulo')) ?></h3>
                <p><?= htmlspecialchars(t('contacto.info.morada_texto')) ?></p>
            </div>
            <div class="info-contacto-item">
                <h3><?= htmlspecialchars(t('contacto.info.telefone_titulo')) ?></h3>
                <a href="+258870000345" target="_blank">+258 87 000 0345</a> ou <a href="+258852824765" target="_blank">+258 85 282 4765</a>
            </div>
            <div class="info-contacto-item">
                <h3><?= htmlspecialchars(t('contacto.info.email_titulo')) ?></h3>

                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=info@stecheng.co.mz&su=Contato%20via%20site" target="_blank">info@cmsv.co.mz</a>
            </div>
                        <div class="info-contacto-item">
                <h3><?= htmlspecialchars(t('contacto.info.horario_titulo')) ?></h3>
                <p><?= htmlspecialchars(t('footer.horario')) ?> <span class="badge-horario" id="badgeHorario" data-texto-aberto="<?= htmlspecialchars(t('contacto.horario.aberto_agora')) ?>" data-texto-fechado="<?= htmlspecialchars(t('contacto.horario.fechado')) ?>" hidden></span></p>
            </div>
        </div>

        <div>
            <?php if ($estado === 'sucesso'): ?>
                <div class="mensagem-estado mensagem-sucesso" id="mensagemEstado">
                    <?= htmlspecialchars(t('contacto.mensagem.sucesso')) ?>
                </div>
            <?php elseif ($estado === 'erro'): ?>
                <div class="mensagem-estado mensagem-erro" id="mensagemEstado">
                    <?= htmlspecialchars(t('contacto.mensagem.erro')) ?>
                </div>
            <?php elseif ($estado === 'indisponivel' && $mensagemIndisponibilidade !== ''): ?>
                <div class="mensagem-estado mensagem-aviso">
                    <?= htmlspecialchars($mensagemIndisponibilidade) ?>
                </div>
            <?php endif; ?>

            <?php if ($estado === 'sucesso' || $estado === 'erro'): ?>
            <script>
                (function () {
                    var msg = document.getElementById('mensagemEstado');
                    if (!msg) return;

                    // Ao fim de 3 segundos, inicia um fade suave e depois remove o elemento
                    setTimeout(function () {
                        msg.style.transition = 'opacity 0.4s ease';
                        msg.style.opacity = '0';

                        setTimeout(function () {
                            msg.remove();
                        }, 400);
                    }, 3000);
                })();
            </script>
            <?php endif; ?>

            <form class="formulario" action="processar_agendamento.php" method="POST">
                <div class="campo-linha">
                    <div class="campo">
                        <label for="nome_cliente"><?= htmlspecialchars(t('contacto.form.nome_label')) ?></label>
                        <input type="text" id="nome_cliente" name="nome_cliente" required>
                    </div>
                    <div class="campo">
                        <label for="telefone_cliente"><?= htmlspecialchars(t('contacto.form.telefone_label')) ?></label>
                        <input type="tel" id="telefone_cliente" name="telefone_cliente">
                    </div>
                </div>

                <div class="campo">
                    <label for="email_cliente"><?= htmlspecialchars(t('contacto.form.email_label')) ?></label>
                    <input type="email" id="email_cliente" name="email_cliente" required>
                </div>

                <div class="campo">
                    <label for="data_preferencial"><?= htmlspecialchars(t('contacto.form.data_label')) ?></label>
                    <input type="date" id="data_preferencial" name="data_preferencial" value="<?= htmlspecialchars($dataPreSelecionada) ?>">
                </div>

                <div class="campo">
                    <label for="buscar_servico"><?= htmlspecialchars(t('contacto.form.servicos_label')) ?></label>

                    <!-- Campo de Pesquisa em Tempo Real -->
                    <input type="text" id="buscar_servico" placeholder="<?= htmlspecialchars(t('contacto.form.buscar_placeholder')) ?>" style="margin-bottom: 10px;">

                    <div class="aviso-disponibilidade" id="avisoDisponibilidade" hidden>
                        <?= htmlspecialchars(t('contacto.form.aviso_dias_removidos')) ?>
                    </div>

                    <!-- Contêiner com Rolagem Vertical e Contador -->
                    <div class="caixa-servicos-scroll">
                        <div class="lista-servicos-pesquisa" id="listaServicos">
                            <?php foreach ($tratamentos as $tratamento): ?>
                                <?php
                                    $idTrat = (int)$tratamento['id_tratamento'];
                                    $diasTrat = $diasPorTratamento[$idTrat] ?? [];
                                    $diasAttr = implode(',', $diasTrat); // vazio = sem restrição (todos os dias)
                                ?>
                                <label class="item-servico-checkbox" data-nome="<?= mb_strtolower(htmlspecialchars($tratamento['nome'])) ?>" data-dias="<?= htmlspecialchars($diasAttr) ?>">
                                    <span class="item-servico-linha-principal">
                                        <input type="checkbox" name="id_tratamentos[]" value="<?= $idTrat ?>"
                                            <?= in_array($idTrat, $tratamentosPreSelecionados, true) ? 'checked' : '' ?>>
                                        <span class="nome-servico-texto"><?= htmlspecialchars($tratamento['nome']) ?></span>
                                    </span>
                                    <span class="item-servico-linha-secundaria">
                                        <span class="dias-disponiveis-servico">
                                            <?php if (!empty($diasTrat)): ?>
                                                <?= sprintf(
                                                    htmlspecialchars(t('contacto.form.disponivel_dias')),
                                                    htmlspecialchars(implode(', ', array_map(
                                                        fn($d) => t(DIAS_SEMANA_ABREV_CHAVES[$d]),
                                                        $diasTrat
                                                    )))
                                                ) ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars(t('contacto.form.disponivel_todos_dias')) ?>
                                            <?php endif; ?>
                                        </span>
                                        <span class="preco-servico"><?= number_format((float)$tratamento['preco'], 2, ',', '.') ?> MT</span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="chips-servicos-selecionados" id="chipsServicos"></div>
                    <span class="resumo-selecao" id="resumoSelecao">0 <?= htmlspecialchars(t('contacto.form.resumo_selecionado')) ?></span>
                </div>



                <div class="campo">
                    <label for="mensagem"><?= htmlspecialchars(t('contacto.form.mensagem_label')) ?></label>
                    <textarea id="mensagem" name="mensagem" placeholder="<?= htmlspecialchars(t('contacto.form.mensagem_placeholder')) ?>"></textarea>
                </div>

                <button type="submit" class="botao botao-primario"><?= htmlspecialchars(t('contacto.form.botao_enviar')) ?></button>
            </form>
        </div>
    </div>
</section>

<section class="secao secao-faq">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow"><?= htmlspecialchars(t('contacto.faq.eyebrow')) ?></p>
            <h2><?= htmlspecialchars(t('contacto.faq.titulo')) ?></h2>
            <p class="texto-lead"><?= htmlspecialchars(t('contacto.faq.texto')) ?></p>
        </div>

        <div class="lista-faq">
            <details class="item-faq" open>
                <summary><?= htmlspecialchars(t('contacto.faq.p1_pergunta')) ?></summary>
                <p><?= htmlspecialchars(t('contacto.faq.p1_resposta')) ?></p>
            </details>
            <details class="item-faq">
                <summary><?= htmlspecialchars(t('contacto.faq.p2_pergunta')) ?></summary>
                <p><?= htmlspecialchars(t('contacto.faq.p2_resposta')) ?></p>
            </details>
            <details class="item-faq">
                <summary><?= htmlspecialchars(t('contacto.faq.p3_pergunta')) ?></summary>
                <p><?= htmlspecialchars(t('contacto.faq.p3_resposta')) ?></p>
            </details>
            <details class="item-faq">
                <summary><?= htmlspecialchars(t('contacto.faq.p4_pergunta')) ?></summary>
                <p><?= htmlspecialchars(t('contacto.faq.p4_resposta')) ?></p>
            </details>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Textos traduzidos, injetados pelo PHP (esta secção corre server-side via contacto.php)
    const textoResumoSelecao = <?= json_encode(t('contacto.form.resumo_selecionado')) ?>;
    const textoRemoverAria = <?= json_encode(t('contacto.form.remover_aria')) ?>;

    const inputBusca = document.getElementById('buscar_servico');
    const itens = document.querySelectorAll('.item-servico-checkbox');
    const resumo = document.getElementById('resumoSelecao');
    const checkboxes = document.querySelectorAll('.item-servico-checkbox input[type="checkbox"]');

    // 1. Filtro de pesquisa instantâneo
    inputBusca.addEventListener('input', function () {
        const termo = this.value.toLowerCase().trim();
        itens.forEach(item => {
            const nome = item.getAttribute('data-nome');
            if (nome.includes(termo)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // 2. Atualizar contador e chips dos selecionados
    const chipsContainer = document.getElementById('chipsServicos');

    function atualizarContador() {
        const marcados = document.querySelectorAll('.item-servico-checkbox input[type="checkbox"]:checked');
        resumo.textContent = textoResumoSelecao.replace('%d', marcados.length);

        chipsContainer.innerHTML = '';
        marcados.forEach((cb) => {
            const nome = cb.closest('.item-servico-checkbox').querySelector('.nome-servico-texto').textContent.trim();

            const chip = document.createElement('span');
            chip.className = 'chip-servico';
            const ariaRemover = textoRemoverAria.replace('%s', nome);
            chip.innerHTML = '<span>' + nome + '</span><button type="button" class="chip-servico-remover" aria-label="' + ariaRemover + '">&times;</button>';

            chip.querySelector('.chip-servico-remover').addEventListener('click', function () {
                cb.checked = false;
                atualizarContador();
            });

            chipsContainer.appendChild(chip);
        });
    }

// Reordena itens marcados para o topo ao carregar a página
    const lista = document.getElementById('listaServicos');
    itens.forEach(item => {
        const cb = item.querySelector('input[type="checkbox"]');
        if (cb && cb.checked) {
            lista.prepend(item);
        }
    });

    checkboxes.forEach(cb => cb.addEventListener('change', atualizarContador));
    atualizarContador(); // Inicializa

    // 3. Bloqueio de serviços indisponíveis no dia da semana escolhido.
    // Espelha a mesma lógica do backend (config/disponibilidade.php), usando
    // o data-dias já renderizado em cada item — não é uma segunda fonte de verdade,
    // apenas ajuda visual: a validação final e obrigatória continua no servidor.
    const inputData = document.getElementById('data_preferencial');
    const avisoDisponibilidade = document.getElementById('avisoDisponibilidade');

    function diaIsoDaData(valorData) {
        if (!valorData) return null;
        const data = new Date(valorData + 'T00:00:00');
        if (isNaN(data.getTime())) return null;
        const diaJs = data.getDay(); // 0 (domingo) a 6 (sábado)
        return diaJs === 0 ? 7 : diaJs; // converte para ISO: 1 (segunda) a 7 (domingo)
    }

    function aplicarDisponibilidadeDoDia() {
        const diaIso = diaIsoDaData(inputData.value);
        let algumRemovido = false;

        itens.forEach(item => {
            const diasAttr = item.getAttribute('data-dias');
            const checkbox = item.querySelector('input[type="checkbox"]');
            const temRestricao = diasAttr && diasAttr.trim() !== '';
            const diasPermitidos = temRestricao ? diasAttr.split(',').map(Number) : [];
            const indisponivel = diaIso !== null && temRestricao && !diasPermitidos.includes(diaIso);

            item.classList.toggle('item-servico-indisponivel', indisponivel);
            checkbox.disabled = indisponivel;

            if (indisponivel && checkbox.checked) {
                checkbox.checked = false;
                algumRemovido = true;
            }
        });

        if (algumRemovido) {
            atualizarContador();
        }

        if (avisoDisponibilidade) {
            avisoDisponibilidade.hidden = !algumRemovido;
        }
    }

    if (inputData) {
        inputData.addEventListener('change', aplicarDisponibilidadeDoDia);
        aplicarDisponibilidadeDoDia(); // aplica logo ao carregar (ex: data pré-preenchida após um aviso)
    }
});
</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>