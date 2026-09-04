<?php
// Widget de cabeçalho - incluído em todas as páginas.
// Uso: include 'includes/header.php';  (a partir da raiz do site)

require_once __DIR__ . '/../config/idioma.php';

$paginaAtual = basename($_SERVER['PHP_SELF']);

function navAtivo(string $pagina, string $atual): string {
    return $pagina === $atual ? 'nav-link ativo' : 'nav-link';
}

/**
 * Constrói o URL da página atual trocando (ou definindo) o parâmetro ?lang=,
 * preservando os restantes parâmetros GET já existentes (ex: ?tratamento=3).
 * Não preserva o fragmento (#agendamento) — limitação aceitável para o toggle.
 */
function urlComIdioma(string $idiomaAlvo, string $paginaAtual): string {
    $params = $_GET;
    $params['lang'] = $idiomaAlvo;
    return $paginaAtual . '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($idioma) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!--
    Script anti-flash: aplica o tema guardado (ou a preferência do sistema)
    ANTES do CSS carregar, para evitar o "flash" de tema claro seguido de
    troca brusca para escuro. Tem de correr de forma síncrona, aqui no <head>,
    antes do <link rel="stylesheet">.
-->
<script>
(function () {
    try {
        var guardado = localStorage.getItem('tema');
        var prefereEscuro = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var tema = guardado || (prefereEscuro ? 'escuro' : 'claro');
        document.documentElement.setAttribute('data-tema', tema);
    } catch (e) {
        // localStorage indisponível (modo privado restrito, etc.) — mantém o tema claro por omissão.
    }
})();
</script>

<title><?= isset($tituloPagina) ? htmlspecialchars($tituloPagina) . ' (Centro Médico Santa Victória)' : 'Centro Médico Santa Victória' ?></title>
<meta name="description" content="Centro Médico Santa Victória - cuidados de saúde de confiança, com uma equipa dedicada ao seu bem-estar.">
<link rel="icon" href="assets/images/logo.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<a href="#conteudo" class="pular-link"><?= t('nav.saltar') ?></a>

<header class="cabecalho" id="cabecalho">
    <div class="cabecalho-interno">
        <a href="index.php" class="marca">
            <img src="assets/images/logo.png" alt="Centro Médico Santa Victória" class="marca-logo">
        </a>

        <nav class="navegacao" id="navegacao">
            <a href="index.php" class="<?= navAtivo('index.php', $paginaAtual) ?>"><?= t('nav.inicio') ?></a>
            <a href="sobre.php" class="<?= navAtivo('sobre.php', $paginaAtual) ?>"><?= t('nav.sobre') ?></a>
            <a href="servicos.php" class="<?= navAtivo('servicos.php', $paginaAtual) ?>"><?= t('nav.servicos') ?></a>
            <a href="galeria.php" class="<?= navAtivo('galeria.php', $paginaAtual) ?>"><?= t('nav.galeria') ?></a>
            <a href="contacto.php" class="<?= navAtivo('contacto.php', $paginaAtual) ?>"><?= t('nav.contacto') ?></a>
        </nav>

        <div class="acoes-cabecalho">
        <div class="seletor-idioma" role="group" aria-label="<?= htmlspecialchars(t('nav.selecionar_idioma')) ?>">
            <a href="<?= htmlspecialchars(urlComIdioma('pt', $paginaAtual)) ?>"
               class="seletor-idioma-pill<?= $idioma === 'pt' ? ' ativo' : '' ?>"
               <?= $idioma === 'pt' ? 'aria-current="true"' : '' ?>>
                <span class="seletor-idioma-bandeira">
                    <svg viewBox="0 0 40 40" width="20" height="20" aria-hidden="true">
                        <defs><clipPath id="clipBandeiraPT"><circle cx="20" cy="20" r="20"/></clipPath></defs>
                        <g clip-path="url(#clipBandeiraPT)">
                            <rect width="16" height="40" fill="#046A38"/>
                            <rect x="16" width="24" height="40" fill="#DA291C"/>
                            <circle cx="16" cy="20" r="6" fill="#FFCC00" stroke="#046A38" stroke-width="1"/>
                        </g>
                    </svg>
                </span>
                <span>PT</span>
            </a>

            <a href="<?= htmlspecialchars(urlComIdioma('en', $paginaAtual)) ?>"
               class="seletor-idioma-pill<?= $idioma === 'en' ? ' ativo' : '' ?>"
               <?= $idioma === 'en' ? 'aria-current="true"' : '' ?>>
                <span class="seletor-idioma-bandeira">
                    <svg viewBox="0 0 40 40" width="20" height="20" aria-hidden="true">
                        <defs><clipPath id="clipBandeiraEN"><circle cx="20" cy="20" r="20"/></clipPath></defs>
                        <g clip-path="url(#clipBandeiraEN)">
                            <rect width="40" height="40" fill="#00247D"/>
                            <path d="M0 0 L40 40 M40 0 L0 40" stroke="#fff" stroke-width="6"/>
                            <path d="M0 0 L40 40 M40 0 L0 40" stroke="#CF142B" stroke-width="2.5"/>
                            <path d="M20 0 V40 M0 20 H40" stroke="#fff" stroke-width="11"/>
                            <path d="M20 0 V40 M0 20 H40" stroke="#CF142B" stroke-width="6"/>
                        </g>
                    </svg>
                </span>
                <span>EN</span>
            </a>
        </div>

            <button type="button" class="alternar-tema" id="alternarTema"
                    aria-pressed="false"
                    aria-label="<?= htmlspecialchars(t('nav.modo_escuro')) ?>"
                    data-label-claro="<?= htmlspecialchars(t('nav.modo_claro')) ?>"
                    data-label-escuro="<?= htmlspecialchars(t('nav.modo_escuro')) ?>">
                <svg class="icone-sol" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66 4.93 19.07M19.07 4.93l-1.41 1.41"/>
                </svg>
                <svg class="icone-lua" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
        </div>

        <a href="contacto.php#agendamento" class="botao botao-primario botao-cabecalho"><?= t('nav.marcar') ?></a>

        <button class="menu-alterna" id="menuAlterna" aria-label="<?= t('nav.abrir_menu') ?>" aria-expanded="false" aria-controls="navegacao">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<script>
(function () {
    var btn = document.getElementById('alternarTema');
    if (!btn) return;

    function aplicarEstadoBotao(tema) {
        var escuro = tema === 'escuro';
        btn.setAttribute('aria-pressed', escuro ? 'true' : 'false');
        btn.setAttribute('aria-label', escuro ? btn.dataset.labelClaro : btn.dataset.labelEscuro);
    }

    aplicarEstadoBotao(document.documentElement.getAttribute('data-tema') || 'claro');

    btn.addEventListener('click', function () {
        var atual = document.documentElement.getAttribute('data-tema') || 'claro';
        var novo = atual === 'escuro' ? 'claro' : 'escuro';
        document.documentElement.setAttribute('data-tema', novo);
        try {
            localStorage.setItem('tema', novo);
        } catch (e) {
            // localStorage indisponível — o tema ainda muda visualmente, só não persiste.
        }
        aplicarEstadoBotao(novo);
    });
})();
</script>

<main id="conteudo">