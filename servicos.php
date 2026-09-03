<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/idioma.php';
require_once __DIR__ . '/config/disponibilidade.php';
$tituloPagina = t('servicos.titulo_pagina');

$tratamentos = buscarTratamentosTraduzidos($pdo, $idioma);
$diasPorTratamento = buscarDiasPorTratamentos($pdo, array_column($tratamentos, 'id_tratamento'));

// Dias efetivamente usados por algum tratamento, para só mostrar botões de filtro relevantes.
$diasUsados = [];
foreach ($diasPorTratamento as $dias) {
    foreach ($dias as $d) {
        $diasUsados[$d] = true;
    }
}
ksort($diasUsados);

// Serviço em destaque: usa o primeiro da lista para dar visibilidade extra a um tratamento.
// Podes trocar por outra lógica (ex: um id fixo) assim que tiveres um serviço "estrela".
$servicoDestaque = !empty($tratamentos) ? $tratamentos[0] : null;

require_once __DIR__ . '/includes/header.php';
?>

<section class="secao secao-pagina-topo">
    <div class="container">
        <p class="eyebrow"><?= htmlspecialchars(t('servicos.topo.eyebrow')) ?></p>
        <h1><?= htmlspecialchars(t('servicos.topo.titulo')) ?></h1>
        <p class="texto-lead" style="max-width:640px">
            <?= htmlspecialchars(t('servicos.topo.texto')) ?>
        </p>
    </div>
</section>

<?php if ($servicoDestaque): ?>
<section class="secao secao-destaque-servico">
    <div class="container cartao-destaque-servico">
        <div class="cartao-destaque-servico-icone">
            <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2 3 7v6c0 5 3.8 8.7 9 9 5.2-.3 9-4 9-9V7l-9-5z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>
        </div>
        <div class="cartao-destaque-servico-texto">
            <p class="cartao-destaque-servico-eyebrow"><?= htmlspecialchars(t('servicos.destaque.eyebrow')) ?></p>
            <h2><?= htmlspecialchars($servicoDestaque['nome']) ?></h2>
            <p><?= htmlspecialchars($servicoDestaque['descricao'] ?? '') ?></p>
        </div>
        <a href="contacto.php?tratamento=<?= (int)$servicoDestaque['id_tratamento'] ?>#agendamento" class="botao botao-branco"><?= htmlspecialchars(t('servicos.destaque.botao')) ?></a>
    </div>
</section>
<?php endif; ?>

<section class="secao secao-servicos">
    <div class="container">
        <?php if (empty($tratamentos)): ?>
            <p><?= htmlspecialchars(t('servicos.lista.vazio')) ?></p>
        <?php else: ?>
            <div class="barra-pesquisa-servicos">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text" id="pesquisaServicos" placeholder="<?= htmlspecialchars(t('servicos.pesquisa.placeholder')) ?>" aria-label="<?= htmlspecialchars(t('servicos.pesquisa.aria')) ?>">
            </div>

            <?php if (!empty($diasUsados)): ?>
                <div class="filtros-galeria" id="filtrosDiasServicos">
                    <button type="button" class="filtro-galeria ativo" data-dia="todos"><?= htmlspecialchars(t('servicos.filtro_dias.todos')) ?></button>
                    <?php foreach (array_keys($diasUsados) as $dia): ?>
                        <button type="button" class="filtro-galeria" data-dia="<?= $dia ?>">
                            <?= htmlspecialchars(t(DIAS_SEMANA_ABREV_CHAVES[$dia])) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="grelha-servicos" id="grelhaServicos">
                <?php foreach ($tratamentos as $tratamento): ?>
                    <?php
                        $idTratCartao = (int)$tratamento['id_tratamento'];
                        $diasCartao = $diasPorTratamento[$idTratCartao] ?? [];
                    ?>
                    <article class="cartao-servico"
                             data-nome="<?= htmlspecialchars(mb_strtolower($tratamento['nome'])) ?>"
                             data-descricao="<?= htmlspecialchars(mb_strtolower($tratamento['descricao'] ?? '')) ?>"
                             data-dias="<?= htmlspecialchars(implode(',', $diasCartao)) ?>">
                        <h3><?= htmlspecialchars($tratamento['nome']) ?></h3>
                        <p><?= htmlspecialchars($tratamento['descricao'] ?? '') ?></p>
                        <p class="cartao-servico-dias">
                            <?php if (!empty($diasCartao)): ?>
                                <?= sprintf(
                                    htmlspecialchars(t('servicos.cartao.dias_disponiveis')),
                                    htmlspecialchars(implode(', ', array_map(
                                        fn($d) => t(DIAS_SEMANA_ABREV_CHAVES[$d]),
                                        $diasCartao
                                    )))
                                ) ?>
                            <?php else: ?>
                                <?= htmlspecialchars(t('servicos.cartao.dias_todos')) ?>
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($tratamento['preco'])): ?>
                            <p class="cartao-servico-preco"><?= htmlspecialchars(t('servicos.cartao.desde')) ?> <?= number_format((float)$tratamento['preco'], 2, ',', '.') ?> MT</p>
                        <?php endif; ?>
                        <a href="contacto.php?tratamento=<?= (int)$tratamento['id_tratamento'] ?>#agendamento" class="link-seta"><?= htmlspecialchars(t('servicos.cartao.link')) ?></a>
                    </article>
                <?php endforeach; ?>
            </div>

            <p class="mensagem-sem-resultados" id="mensagemSemResultados" hidden>
                <?= htmlspecialchars(t('servicos.sem_resultados')) ?>
            </p>
        <?php endif; ?>
    </div>
</section>

<section class="secao secao-faq">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow"><?= htmlspecialchars(t('servicos.faq.eyebrow')) ?></p>
            <h2><?= htmlspecialchars(t('servicos.faq.titulo')) ?></h2>
            <p class="texto-lead"><?= htmlspecialchars(t('servicos.faq.texto')) ?></p>
        </div>

        <div class="lista-faq">
            <details class="item-faq" open>
                <summary><?= htmlspecialchars(t('servicos.faq.p1_pergunta')) ?></summary>
                <p><?= htmlspecialchars(t('servicos.faq.p1_resposta')) ?></p>
            </details>
            <details class="item-faq">
                <summary><?= htmlspecialchars(t('servicos.faq.p2_pergunta')) ?></summary>
                <p><?= htmlspecialchars(t('servicos.faq.p2_resposta')) ?></p>
            </details>
            <details class="item-faq">
                <summary><?= htmlspecialchars(t('servicos.faq.p3_pergunta')) ?></summary>
                <p><?= htmlspecialchars(t('servicos.faq.p3_resposta')) ?></p>
            </details>
            <details class="item-faq">
                <summary><?= htmlspecialchars(t('servicos.faq.p4_pergunta')) ?></summary>
                <p><?= htmlspecialchars(t('servicos.faq.p4_resposta')) ?></p>
            </details>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Filtro por dia da semana (independente da pesquisa por texto, que continua em main.js)
    const filtrosDias = document.getElementById('filtrosDiasServicos');
    const grelhaServicos = document.getElementById('grelhaServicos');
    const mensagemSemResultados = document.getElementById('mensagemSemResultados');

    if (filtrosDias && grelhaServicos) {
        const cartoes = grelhaServicos.querySelectorAll('.cartao-servico');

        filtrosDias.addEventListener('click', function (evento) {
            const botao = evento.target.closest('.filtro-galeria');
            if (!botao) return;

            filtrosDias.querySelectorAll('.filtro-galeria').forEach((b) => b.classList.remove('ativo'));
            botao.classList.add('ativo');

            const diaEscolhido = botao.dataset.dia;
            let algumVisivel = false;

            cartoes.forEach((cartao) => {
                const diasAttr = cartao.getAttribute('data-dias');
                const temRestricao = diasAttr && diasAttr.trim() !== '';
                const diasCartao = temRestricao ? diasAttr.split(',') : [];
                const corresponde = diaEscolhido === 'todos' || !temRestricao || diasCartao.includes(diaEscolhido);

                cartao.style.display = corresponde ? '' : 'none';
                if (corresponde) algumVisivel = true;
            });

            if (mensagemSemResultados) {
                mensagemSemResultados.hidden = algumVisivel;
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>