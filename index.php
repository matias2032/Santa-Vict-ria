<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/idioma.php';

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
$tratamentos = buscarTratamentosTraduzidos($pdo, $idioma, ['preco'], 'criado_em', 'DESC', 6);

// Conteúdo de reserva, caso a tabela ainda esteja vazia ou a BD esteja em baixo.
if (empty($tratamentos)) {
    $tratamentos = [
        ['nome' => t('index.fallback.consulta_geral.nome'), 'descricao' => t('index.fallback.consulta_geral.descricao'), 'preco' => null],
        ['nome' => t('index.fallback.pediatria.nome'), 'descricao' => t('index.fallback.pediatria.descricao'), 'preco' => null],
        ['nome' => t('index.fallback.ginecologia.nome'), 'descricao' => t('index.fallback.ginecologia.descricao'), 'preco' => null],
        ['nome' => t('index.fallback.analises.nome'), 'descricao' => t('index.fallback.analises.descricao'), 'preco' => null],
        ['nome' => t('index.fallback.ecografia.nome'), 'descricao' => t('index.fallback.ecografia.descricao'), 'preco' => null],
        ['nome' => t('index.fallback.odontologia.nome'), 'descricao' => t('index.fallback.odontologia.descricao'), 'preco' => null],
    ];
}

/**
 * EQUIPA EM DESTAQUE (para a home)
 * Reutiliza a mesma pasta/convenção de nomes da página Sobre Nós:
 * assets/images/galeria/equipe/nome-completo--cargo.jpg
 * Mostra só as 4 primeiras fotos encontradas.
 */
$pastaEquipe = __DIR__ . '/assets/images/galeria/equipe';
$equipeDestaque = [];

if (is_dir($pastaEquipe)) {
    foreach (scandir($pastaEquipe) as $ficheiro) {
        $ext = strtolower(pathinfo($ficheiro, PATHINFO_EXTENSION));
        if (!in_array($ext, $extensoesValidas, true)) {
            continue;
        }

        $nomeBase = pathinfo($ficheiro, PATHINFO_FILENAME);
        $partes = explode('--', $nomeBase, 2);
        $nome  = str_replace(['-', '_'], ' ', $partes[0]);
        $cargo = isset($partes[1]) ? str_replace(['-', '_'], ' ', $partes[1]) : 'Equipa Santa Victória';

        $equipeDestaque[] = [
            'foto'  => 'assets/images/galeria/equipe/' . $ficheiro,
            'nome'  => ucwords($nome),
            'cargo' => ucwords($cargo),
        ];
    }
}
$equipeDestaque = array_slice($equipeDestaque, 0, 4);

// Fotos para a pré-visualização da galeria (reaproveita as imagens do hero, até 4)
$galeriaDestaque = array_slice(array_filter($imagensHero), 0, 4);

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
        <p class="hero-eyebrow"><?= t('index.hero.marca') ?></p>
        <h1 class="hero-titulo"><?= t('index.hero.titulo_1') ?> <span><?= t('index.hero.titulo_2') ?></span></h1>
        <p class="hero-texto">
            <?= t('index.hero.texto') ?>
        </p>
        <!-- <div class="hero-acoes">
            <a href="contacto.php#agendamento" class="botao botao-primario">Marcar consulta</a>
            <a href="servicos.php" class="botao botao-secundario">Ver serviços</a>
        </div> -->
    </div>

    <?php if (count($imagensHero) > 1): ?>
    <div class="hero-marcadores" id="heroMarcadores">
        <?php foreach ($imagensHero as $i => $imagem): ?>
            <button class="hero-marcador <?= $i === 0 ? 'ativo' : '' ?>" data-slide="<?= $i ?>" aria-label="<?= t('index.hero.foto_alt') ?> <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<section class="faixa-estatisticas">
    <div class="container faixa-estatisticas-grelha">
        <div class="estatistica">
            <p class="estatistica-numero" data-contador data-alvo="10" data-prefixo="+">0</p>
            <p class="estatistica-legenda"><?= t('index.estatisticas.anos') ?></p>
        </div>
        <div class="estatistica">
            <p class="estatistica-numero" data-contador data-alvo="50" data-prefixo="+">0</p>
            <p class="estatistica-legenda"><?= t('index.estatisticas.especialidades') ?></p>
        </div>
        <div class="estatistica">
            <p class="estatistica-numero" data-contador data-alvo="5000" data-prefixo="+">0</p>
            <p class="estatistica-legenda"><?= t('index.estatisticas.pacientes') ?></p>
        </div>
        <div class="estatistica">
            <p class="estatistica-numero">24/7</p>
            <p class="estatistica-legenda"><?= t('index.estatisticas.urgencias') ?></p>
        </div>
    </div>
</section>

<section class="secao secao-sobre-resumo">
    <div class="container grelha-2">
        <div>
            <p class="eyebrow"><?= t('index.sobre.eyebrow') ?></p>
            <h2><?= t('index.sobre.titulo') ?></h2>
            <p class="texto-lead">
                <?= t('index.sobre.texto') ?>
            </p>
            <ul class="lista-check">
                <li><?= t('index.sobre.item_1') ?></li>
                <li><?= t('index.sobre.item_2') ?></li>
                <li><?= t('index.sobre.item_3') ?></li>
            </ul>
            <a href="sobre.php" class="link-seta"><?= t('index.sobre.link') ?></a>
        </div>
        <div class="cartao-destaque">
            <p class="cartao-destaque-eyebrow"><?= t('index.cartao.eyebrow') ?></p>
            <h3 class="cartao-destaque-titulo"><?= t('index.cartao.titulo') ?></h3>
            <p class="cartao-destaque-texto"><?= t('index.cartao.texto') ?></p>
            <div class="cartao-destaque-linha"></div>
            <a href="tel:+258870000345" class="cartao-destaque-contacto">+258 87 000 0345</a>
            <a href="contacto.php#agendamento" class="botao botao-branco cartao-destaque-botao"><?= t('index.cartao.botao') ?></a>
        </div>
    </div>
</section>

<section class="secao secao-servicos" id="servicos">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow"><?= t('index.servicos.eyebrow') ?></p>
            <h2><?= t('index.servicos.titulo') ?></h2>
            <p class="texto-lead"><?= t('index.servicos.texto') ?></p>
        </div>

        <div class="grelha-servicos">
            <?php foreach ($tratamentos as $tratamento): ?>
                <article class="cartao-servico">
                    <h3><?= htmlspecialchars($tratamento['nome']) ?></h3>
                    <p><?= htmlspecialchars($tratamento['descricao'] ?? '') ?></p>
                    <?php if (!empty($tratamento['preco'])): ?>
                        <p class="cartao-servico-preco"><?= t('index.servicos.desde') ?> <?= number_format((float)$tratamento['preco'], 2, ',', '.') ?> MT</p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="secao-acao">
            <a href="servicos.php" class="botao botao-primario"><?= t('index.servicos.botao') ?></a>
        </div>
    </div>
</section>

<section class="secao secao-processo">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow"><?= t('index.processo.eyebrow') ?></p>
            <h2><?= t('index.processo.titulo') ?></h2>
            <p class="texto-lead"><?= t('index.processo.texto') ?></p>
        </div>

        <div class="grelha-processo">
            <div class="passo-processo">
                <span class="passo-numero">01</span>
                <h3><?= t('index.processo.p1_titulo') ?></h3>
                <p><?= t('index.processo.p1_texto') ?></p>
            </div>
            <div class="passo-processo">
                <span class="passo-numero">02</span>
                <h3><?= t('index.processo.p2_titulo') ?></h3>
                <p><?= t('index.processo.p2_texto') ?></p>
            </div>
            <div class="passo-processo">
                <span class="passo-numero">03</span>
                <h3><?= t('index.processo.p3_titulo') ?></h3>
                <p><?= t('index.processo.p3_texto') ?></p>
            </div>
            <div class="passo-processo">
                <span class="passo-numero">04</span>
                <h3><?= t('index.processo.p4_titulo') ?></h3>
                <p><?= t('index.processo.p4_texto') ?></p>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($equipeDestaque)): ?>
<section class="secao secao-equipe">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow"><?= t('index.equipe.eyebrow') ?></p>
            <h2><?= t('index.equipe.titulo') ?></h2>
            <p class="texto-lead"><?= t('index.equipe.texto') ?></p>
        </div>

        <div class="grelha-galeria">
            <?php foreach ($equipeDestaque as $membro): ?>
                <div class="galeria-item galeria-item-equipe">
                    <img src="<?= htmlspecialchars($membro['foto']) ?>" alt="<?= htmlspecialchars($membro['nome']) ?>" loading="lazy">
                    <div class="galeria-item-legenda">
                        <p class="galeria-item-nome"><?= htmlspecialchars($membro['nome']) ?></p>
                        <p class="galeria-item-cargo"><?= htmlspecialchars($membro['cargo']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="secao-acao">
            <a href="sobre.php#equipe" class="botao botao-primario"><?= t('index.equipe.botao') ?></a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($galeriaDestaque)): ?>
<section class="secao secao-galeria-preview">
    <div class="container">
        <div class="secao-cabecalho-flex">
            <div>
                <p class="eyebrow"><?= t('index.galeria.eyebrow') ?></p>
                <h2><?= t('index.galeria.titulo') ?></h2>
            </div>
            <a href="galeria.php" class="link-seta"><?= t('index.galeria.link') ?></a>
        </div>

        <div class="grelha-galeria grelha-galeria-preview">
            <?php foreach ($galeriaDestaque as $imagem): ?>
                <div class="galeria-item">
                    <img src="<?= htmlspecialchars($imagem) ?>" alt="<?= t('index.galeria.foto_alt') ?>" loading="lazy">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- <section class="secao secao-depoimentos">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow">Depoimentos</p>
            <h2>O que dizem os nossos pacientes</h2>
            <p class="texto-lead">
                Exemplos de depoimentos — substitui por testemunhos reais dos teus pacientes assim que os tiveres.
            </p>
        </div>

        <div class="grelha-depoimentos">
            <article class="cartao-depoimento">
                <p class="depoimento-texto">Fui muito bem atendida, desde a marcação até à consulta. Equipa atenciosa e profissional.</p>
                <div class="depoimento-autor">
                    <span class="depoimento-avatar">MC</span>
                    <div>
                        <p class="depoimento-nome">Maria Chissano</p>
                        <p class="depoimento-cargo">Paciente</p>
                    </div>
                </div>
            </article>
            <article class="cartao-depoimento">
                <p class="depoimento-texto">Consegui marcar a consulta em minutos e fui atendido no horário combinado. Recomendo.</p>
                <div class="depoimento-autor">
                    <span class="depoimento-avatar">JM</span>
                    <div>
                        <p class="depoimento-nome">João Mucavel</p>
                        <p class="depoimento-cargo">Paciente</p>
                    </div>
                </div>
            </article>
            <article class="cartao-depoimento">
                <p class="depoimento-texto">Levei o meu filho a uma consulta de pediatria e o acompanhamento foi excelente do início ao fim.</p>
                <div class="depoimento-autor">
                    <span class="depoimento-avatar">AS</span>
                    <div>
                        <p class="depoimento-nome">Ana Sitoe</p>
                        <p class="depoimento-cargo">Encarregada de educação</p>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section> -->

<section class="secao secao-cta">
    <div class="container secao-cta-interno">
        <div>
            <h2><?= t('index.cta.titulo') ?></h2>
            <p><?= t('index.cta.texto') ?></p>
        </div>
        <a href="contacto.php#agendamento" class="botao botao-branco"><?= t('index.cta.botao') ?></a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>