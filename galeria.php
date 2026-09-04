<?php
require_once __DIR__ . '/config/idioma.php';
require_once __DIR__ . '/config/categorias_galeria.php';
$tituloPagina = t('galeria.titulo_pagina');

$pastaGaleria = __DIR__ . '/assets/images/galeria';
$extensoesValidas = ['jpg', 'jpeg', 'png', 'webp'];
$imagens = [];

/**
 * Convenção opcional do nome do ficheiro para categorizar as fotos:
 * categoria--descricao.jpg → ex: instalacoes--sala-de-espera.jpg
 * Sem "--", a foto entra na categoria traduzida por 'galeria.categoria_geral'.
 */
if (is_dir($pastaGaleria)) {
    foreach (scandir($pastaGaleria) as $ficheiro) {
        $caminhoCompleto = $pastaGaleria . '/' . $ficheiro;
        if (!is_file($caminhoCompleto)) {
            continue; // ignora as subpastas equipe/ e parceiros/
        }

        $ext = strtolower(pathinfo($ficheiro, PATHINFO_EXTENSION));
        if (!in_array($ext, $extensoesValidas, true)) {
            continue;
        }

        $nomeBase = pathinfo($ficheiro, PATHINFO_FILENAME);
        $partes = explode('--', $nomeBase, 2);
        $categoria = count($partes) > 1 ? traduzirCategoriaGaleria($partes[0]) : t('galeria.categoria_geral');

        $imagens[] = [
            'caminho'   => 'assets/images/galeria/' . $ficheiro,
            'categoria' => $categoria,
        ];
    }
}

// Categorias únicas encontradas, para os separadores de filtro
$categorias = array_values(array_unique(array_column($imagens, 'categoria')));
sort($categorias);

require_once __DIR__ . '/includes/header.php';
?>

<section class="secao secao-pagina-topo">
    <div class="container">
        <p class="eyebrow"><?= htmlspecialchars(t('galeria.topo.eyebrow')) ?></p>
        <h1><?= htmlspecialchars(t('galeria.topo.titulo')) ?></h1>
        <p class="texto-lead" style="max-width:640px">
            <?= htmlspecialchars(t('galeria.topo.texto')) ?>
        </p>
    </div>
</section>

<section class="secao">
    <div class="container">
        <?php if (empty($imagens)): ?>
            <p><?= sprintf(htmlspecialchars(t('galeria.vazio')), '<code>assets/images/galeria/</code>') ?></p>
        <?php else: ?>
            <?php if (count($categorias) > 1): ?>
                <div class="filtros-galeria" id="filtrosGaleria">
                    <button type="button" class="filtro-galeria ativo" data-categoria="todas"><?= htmlspecialchars(t('galeria.filtro.todas')) ?></button>
                    <?php foreach ($categorias as $categoria): ?>
                        <button type="button" class="filtro-galeria" data-categoria="<?= htmlspecialchars(mb_strtolower($categoria)) ?>">
                            <?= htmlspecialchars($categoria) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="grelha-galeria" id="grelhaGaleriaFotos">
                <?php foreach ($imagens as $indice => $imagem): ?>
                    <a href="<?= htmlspecialchars($imagem['caminho']) ?>" target="_blank"
                       class="galeria-item abre-lightbox"
                       data-categoria="<?= htmlspecialchars(mb_strtolower($imagem['categoria'])) ?>"
                       data-indice="<?= $indice ?>">
                        <img src="<?= htmlspecialchars($imagem['caminho']) ?>" alt="<?= htmlspecialchars(t('galeria.foto_alt')) ?>" loading="lazy">
                    </a>
                <?php endforeach; ?>
            </div>

            <p class="mensagem-sem-resultados" id="mensagemSemFotos" hidden>
                <?= htmlspecialchars(t('galeria.sem_fotos')) ?>
            </p>
        <?php endif; ?>
    </div>
</section>

<div class="lightbox-galeria" id="lightboxGaleria" hidden>
    <button type="button" class="lightbox-fechar" id="lightboxFechar" aria-label="<?= htmlspecialchars(t('galeria.lightbox.fechar')) ?>">&times;</button>
    <button type="button" class="lightbox-seta lightbox-anterior" id="lightboxAnterior" aria-label="<?= htmlspecialchars(t('galeria.lightbox.anterior')) ?>">&#8249;</button>
    <img src="" alt="<?= htmlspecialchars(t('galeria.lightbox.imagem_alt')) ?>" id="lightboxImagem">
    <button type="button" class="lightbox-seta lightbox-seguinte" id="lightboxSeguinte" aria-label="<?= htmlspecialchars(t('galeria.lightbox.seguinte')) ?>">&#8250;</button>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>