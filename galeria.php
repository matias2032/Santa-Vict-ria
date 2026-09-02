<?php
$tituloPagina = 'Galeria';

$pastaGaleria = __DIR__ . '/assets/images/galeria';
$extensoesValidas = ['jpg', 'jpeg', 'png', 'webp'];
$imagens = [];

if (is_dir($pastaGaleria)) {
    foreach (scandir($pastaGaleria) as $ficheiro) {
        $ext = strtolower(pathinfo($ficheiro, PATHINFO_EXTENSION));
        if (in_array($ext, $extensoesValidas, true)) {
            $imagens[] = 'assets/images/galeria/' . $ficheiro;
        }
    }
}

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
            <div class="grelha-galeria">
                <?php foreach ($imagens as $imagem): ?>
                    <a href="<?= htmlspecialchars($imagem) ?>" target="_blank" class="galeria-item">
                        <img src="<?= htmlspecialchars($imagem) ?>" alt="Fotografia do Centro Médico Santa Victória" loading="lazy">
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
