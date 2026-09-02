<?php
require_once __DIR__ . '/config/db.php';

$tituloPagina = 'Início';

/**
 * HERO DINÂMICO
 * Lê automaticamente todas as imagens guardadas em assets/images/galeria/
 * Basta colocares fotos novas nessa pasta - aparecem no slider sem editar código.
 */
$pastaGaleria = __DIR__ . '/assets/images/galeria';
$extensoesValidas = ['jpg', 'jpeg', 'png', 'webp'];
$imagensHero = [];

if (is_dir($pastaGaleria)) {
    foreach (scandir($pastaGaleria) as $ficheiro) {
        $ext = strtolower(pathinfo($ficheiro, PATHINFO_EXTENSION));
        if (in_array($ext, $extensoesValidas, true)) {
            $imagensHero[] = 'assets/images/galeria/' . $ficheiro;
        }
    }
}

// Se a pasta ainda estiver vazia, usa uma cor de fundo em vez de imagem quebrada.
if (empty($imagensHero)) {
    $imagensHero = [null];
}

/**
 * SERVIÇOS (tabela: tratamentos)
 * Mostra só os tratamentos ativos, limitados a 6 na página inicial.
 */
$tratamentos = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT id_tratamento, nome, descricao, preco 
                              FROM tratamentos 
                              WHERE ativo = 1 
                              ORDER BY criado_em DESC 
                              LIMIT 6");
        $tratamentos = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao carregar tratamentos: ' . $e->getMessage());
    }
}

// Conteúdo de reserva, caso a tabela ainda esteja vazia ou a BD esteja em baixo.
if (empty($tratamentos)) {
    $tratamentos = [
        ['nome' => 'Consulta Geral', 'descricao' => 'Avaliação clínica completa com médico de clínica geral.', 'preco' => null],
        ['nome' => 'Pediatria', 'descricao' => 'Acompanhamento de saúde para crianças e recém-nascidos.', 'preco' => null],
        ['nome' => 'Ginecologia', 'descricao' => 'Consultas e exames de saúde da mulher.', 'preco' => null],
        ['nome' => 'Análises Clínicas', 'descricao' => 'Recolha e processamento de exames laboratoriais.', 'preco' => null],
        ['nome' => 'Ecografia', 'descricao' => 'Exames de imagem para diagnóstico rápido e preciso.', 'preco' => null],
        ['nome' => 'Odontologia', 'descricao' => 'Cuidados dentários preventivos e curativos.', 'preco' => null],
    ];
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero" id="hero">
    <div class="hero-slides" id="heroSlides">
        <?php foreach ($imagensHero as $i => $imagem): ?>
            <div class="hero-slide <?= $i === 0 ? 'ativo' : '' ?>"
                 <?= $imagem ? 'style="background-image:url(\'' . htmlspecialchars($imagem) . '\')"' : '' ?>></div>
        <?php endforeach; ?>
        <div class="hero-sobreposicao"></div>
    </div>

    <div class="hero-conteudo">
        <p class="hero-eyebrow">Centro Médico Santa Victória</p>
        <h1 class="hero-titulo">Cuidar de si é <span>o nosso compromisso</span></h1>
        <p class="hero-texto">
            Uma equipa médica dedicada, atendimento humano e tecnologia ao serviço da sua saúde,
            aqui na Cidade de Tete.
        </p>
        <div class="hero-acoes">
            <a href="contacto.php#agendamento" class="botao botao-primario">Marcar consulta</a>
            <a href="servicos.php" class="botao botao-secundario">Ver serviços</a>
        </div>
    </div>

    <?php if (count($imagensHero) > 1): ?>
    <div class="hero-marcadores" id="heroMarcadores">
        <?php foreach ($imagensHero as $i => $imagem): ?>
            <button class="hero-marcador <?= $i === 0 ? 'ativo' : '' ?>" data-slide="<?= $i ?>" aria-label="Foto <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<section class="secao secao-sobre-resumo">
    <div class="container grelha-2">
        <div>
            <p class="eyebrow">Sobre nós</p>
            <h2>Saúde de confiança, perto de si</h2>
            <p class="texto-lead">
                Há anos ao serviço da comunidade de Tete, o Centro Médico Santa Victória reúne uma equipa
                de profissionais dedicados e infraestrutura adequada para acompanhar a sua saúde e a da sua família,
                com rigor clínico e proximidade humana.
            </p>
            <ul class="lista-check">
                <li>Equipa médica qualificada e atenta</li>
                <li>Marcação de consultas simples e rápida</li>
                <li>Acompanhamento próximo em cada etapa</li>
            </ul>
            <a href="sobre.php" class="link-seta">Conhecer a nossa história</a>
        </div>
        <div class="cartao-destaque">
             <p class="cartao-destaque-numero" data-contador data-alvo="10" data-prefixo="+" style="color:white">0</p>
            <p class="cartao-destaque-legenda">anos a cuidar da comunidade de Tete</p>
            <div class="cartao-destaque-linha"></div>
            <p class="cartao-destaque-numero" style="color:white">24/</p><p class="cartao-destaque-numero" data-contador data-alvo="7" style="color:white">0</p>
            <p class="cartao-destaque-legenda">disponibilidade para urgências</p>
        </div>
    </div>
</section>

<section class="secao secao-servicos" id="servicos">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow">O que oferecemos</p>
            <h2>Os nossos serviços</h2>
            <p class="texto-lead">Um conjunto de especialidades pensado para cuidar de si em cada fase da vida.</p>
        </div>

        <div class="grelha-servicos">
            <?php foreach ($tratamentos as $tratamento): ?>
                <article class="cartao-servico">
                    <h3><?= htmlspecialchars($tratamento['nome']) ?></h3>
                    <p><?= htmlspecialchars($tratamento['descricao'] ?? '') ?></p>
                    <?php if (!empty($tratamento['preco'])): ?>
                        <p class="cartao-servico-preco">Desde <?= number_format((float)$tratamento['preco'], 2, ',', '.') ?> MT</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="secao-acao">
            <a href="servicos.php" class="botao botao-primario">Ver todos os serviços</a>
        </div>
    </div>
</section>

<section class="secao secao-cta">
    <div class="container secao-cta-interno">
        <div>
            <h2>Pronto para cuidar da sua saúde?</h2>
            <p>Marque a sua consulta em poucos minutos e a nossa equipa entra em contacto para confirmar.</p>
        </div>
        <a href="contacto.php#agendamento" class="botao botao-branco">Marcar consulta</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
