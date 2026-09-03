<?php
$tituloPagina = 'Galeria';

$pastaGaleria = __DIR__ . '/assets/images/galeria';
$extensoesValidas = ['jpg', 'jpeg', 'png', 'webp'];
$imagens = [];

/**
 * Convenção opcional do nome do ficheiro para categorizar as fotos:
 * categoria--descricao.jpg → ex: instalacoes--sala-de-espera.jpg
 * Sem "--", a foto entra na categoria "Geral".
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
        $categoria = count($partes) > 1 ? ucwords(str_replace(['-', '_'], ' ', $partes[0])) : 'Geral';

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
        <p class="eyebrow">Galeria</p>
        <h1>Conheça as nossas instalações</h1>
        <p class="texto-lead" style="max-width:640px">
            Um olhar sobre os espaços do Centro Médico Santa Victória.
        </p>
    </div>
</section>

<section class="secao">
    <div class="container">
        <?php if (empty($imagens)): ?>
            <p>Ainda não há fotografias na galeria. Adicione ficheiros em <code>assets/images/galeria/</code>.</p>
        <?php else: ?>
            <?php if (count($categorias) > 1): ?>
                <div class="filtros-galeria" id="filtrosGaleria">
                    <button type="button" class="filtro-galeria ativo" data-categoria="todas">Todas</button>
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
                        <img src="<?= htmlspecialchars($imagem['caminho']) ?>" alt="Fotografia do Centro Médico Santa Victória" loading="lazy">
                    </a>
                <?php endforeach; ?>
            </div>

            <p class="mensagem-sem-resultados" id="mensagemSemFotos" hidden>
                Não há fotografias nesta categoria.
            </p>
        <?php endif; ?>
    </div>
</section>

<div class="lightbox-galeria" id="lightboxGaleria" hidden>
    <button type="button" class="lightbox-fechar" id="lightboxFechar" aria-label="Fechar">&times;</button>
    <button type="button" class="lightbox-seta lightbox-anterior" id="lightboxAnterior" aria-label="Foto anterior">&#8249;</button>
    <img src="" alt="Fotografia ampliada" id="lightboxImagem">
    <button type="button" class="lightbox-seta lightbox-seguinte" id="lightboxSeguinte" aria-label="Próxima foto">&#8250;</button>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
