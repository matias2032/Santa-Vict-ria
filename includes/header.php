<?php
// Widget de cabeçalho - incluído em todas as páginas.
// Uso: include 'includes/header.php';  (a partir da raiz do site)

$paginaAtual = basename($_SERVER['PHP_SELF']);

function navAtivo(string $pagina, string $atual): string {
    return $pagina === $atual ? 'nav-link ativo' : 'nav-link';
}
?>
<!DOCTYPE html>
<html lang="pt-mz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($tituloPagina) ? htmlspecialchars($tituloPagina) . ' | Centro Médico Santa Victória' : 'Centro Médico Santa Victória' ?></title>
<meta name="description" content="Centro Médico Santa Victória - cuidados de saúde de confiança, com uma equipa dedicada ao seu bem-estar.">
<link rel="icon" href="assets/images/logo.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<a href="#conteudo" class="pular-link">Saltar para o conteúdo</a>

<header class="cabecalho" id="cabecalho">
    <div class="cabecalho-interno">
        <a href="index.php" class="marca">
            <img src="assets/images/logo.png" alt="Centro Médico Santa Victória" class="marca-logo">
        </a>

        <nav class="navegacao" id="navegacao">
            <a href="index.php" class="<?= navAtivo('index.php', $paginaAtual) ?>">Início</a>
            <a href="sobre.php" class="<?= navAtivo('sobre.php', $paginaAtual) ?>">Sobre Nós</a>
            <a href="servicos.php" class="<?= navAtivo('servicos.php', $paginaAtual) ?>">Serviços</a>
            <a href="galeria.php" class="<?= navAtivo('galeria.php', $paginaAtual) ?>">Galeria</a>
            <a href="contacto.php" class="<?= navAtivo('contacto.php', $paginaAtual) ?>">Contacto</a>
        </nav>

        <a href="contacto.php#agendamento" class="botao botao-primario botao-cabecalho">Marcar consulta</a>

        <button class="menu-alterna" id="menuAlterna" aria-label="Abrir menu" aria-expanded="false" aria-controls="navegacao">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<main id="conteudo">
