<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/idioma.php';
$tituloPagina = t('servicos.titulo_pagina');

$tratamentos = buscarTratamentosTraduzidos($pdo, $idioma);

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

            <div class="grelha-servicos" id="grelhaServicos">
                <?php foreach ($tratamentos as $tratamento): ?>
                    <article class="cartao-servico"
                             data-nome="<?= htmlspecialchars(mb_strtolower($tratamento['nome'])) ?>"
                             data-descricao="<?= htmlspecialchars(mb_strtolower($tratamento['descricao'] ?? '')) ?>">
                        <h3><?= htmlspecialchars($tratamento['nome']) ?></h3>
                        <p><?= htmlspecialchars($tratamento['descricao'] ?? '') ?></p>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>