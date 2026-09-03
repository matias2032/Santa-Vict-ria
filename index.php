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
        <p class="hero-eyebrow">Centro Médico Santa Victória</p>
        <h1 class="hero-titulo">Cuidar de si é <span>o nosso compromisso</span></h1>
        <p class="hero-texto">
            Uma equipa médica dedicada, atendimento humano e tecnologia ao serviço da sua saúde,
            aqui na Cidade de Tete.
        </p>
        <!-- <div class="hero-acoes">
            <a href="contacto.php#agendamento" class="botao botao-primario">Marcar consulta</a>
            <a href="servicos.php" class="botao botao-secundario">Ver serviços</a>
        </div> -->
    </div>

    <?php if (count($imagensHero) > 1): ?>
    <div class="hero-marcadores" id="heroMarcadores">
        <?php foreach ($imagensHero as $i => $imagem): ?>
            <button class="hero-marcador <?= $i === 0 ? 'ativo' : '' ?>" data-slide="<?= $i ?>" aria-label="Foto <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<section class="faixa-estatisticas">
    <div class="container faixa-estatisticas-grelha">
        <div class="estatistica">
            <p class="estatistica-numero" data-contador data-alvo="10" data-prefixo="+">0</p>
            <p class="estatistica-legenda">Anos de experiência</p>
        </div>
        <div class="estatistica">
            <p class="estatistica-numero" data-contador data-alvo="50" data-prefixo="+">0</p>
            <p class="estatistica-legenda">Especialidades e serviços</p>
        </div>
        <div class="estatistica">
            <p class="estatistica-numero" data-contador data-alvo="5000" data-prefixo="+">0</p>
            <p class="estatistica-legenda">Pacientes atendidos</p>
        </div>
        <div class="estatistica">
            <p class="estatistica-numero">24/7</p>
            <p class="estatistica-legenda">Disponibilidade para urgências</p>
        </div>
    </div>
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
            <p class="cartao-destaque-eyebrow">Precisa de ajuda agora?</p>
            <h3 class="cartao-destaque-titulo">Fale com a nossa equipa</h3>
            <p class="cartao-destaque-texto">Estamos disponíveis por telefone, WhatsApp ou presencialmente, todos os dias da semana.</p>
            <div class="cartao-destaque-linha"></div>
            <a href="tel:+258870000345" class="cartao-destaque-contacto">+258 87 000 0345</a>
            <a href="contacto.php#agendamento" class="botao botao-branco cartao-destaque-botao">Marcar consulta</a>
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

<section class="secao secao-processo">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow">Como funciona</p>
            <h2>Um processo simples, claro e profissional</h2>
            <p class="texto-lead">Acompanhamos cada passo, desde o primeiro contacto até ao acompanhamento pós-consulta.</p>
        </div>

        <div class="grelha-processo">
            <div class="passo-processo">
                <span class="passo-numero">01</span>
                <h3>Contacto</h3>
                <p>Marque a sua consulta por telefone, WhatsApp ou através do nosso formulário online.</p>
            </div>
            <div class="passo-processo">
                <span class="passo-numero">02</span>
                <h3>Triagem</h3>
                <p>A nossa equipa confirma a data e prepara tudo para o receber com o cuidado necessário.</p>
            </div>
            <div class="passo-processo">
                <span class="passo-numero">03</span>
                <h3>Consulta</h3>
                <p>É atendido por um profissional qualificado, com atenção total ao seu caso.</p>
            </div>
            <div class="passo-processo">
                <span class="passo-numero">04</span>
                <h3>Acompanhamento</h3>
                <p>Mantemos o acompanhamento necessário após a consulta, sempre que aplicável.</p>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($equipeDestaque)): ?>
<section class="secao secao-equipe">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow">A nossa equipa</p>
            <h2>Quem cuida de si</h2>
            <p class="texto-lead">Profissionais dedicados, prontos para acompanhar a sua saúde com atenção e proximidade.</p>
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
            <a href="sobre.php#equipe" class="botao botao-primario">Conhecer toda a equipa</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($galeriaDestaque)): ?>
<section class="secao secao-galeria-preview">
    <div class="container">
        <div class="secao-cabecalho-flex">
            <div>
                <p class="eyebrow">Instalações</p>
                <h2>Conheça os nossos espaços</h2>
            </div>
            <a href="galeria.php" class="link-seta">Ver galeria completa</a>
        </div>

        <div class="grelha-galeria grelha-galeria-preview">
            <?php foreach ($galeriaDestaque as $imagem): ?>
                <div class="galeria-item">
                    <img src="<?= htmlspecialchars($imagem) ?>" alt="Instalações do Centro Médico Santa Victória" loading="lazy">
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
            <h2>Pronto para cuidar da sua saúde?</h2>
            <p>Marque a sua consulta em poucos minutos e a nossa equipa entra em contacto para confirmar.</p>
        </div>
        <a href="contacto.php#agendamento" class="botao botao-branco">Marcar consulta</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>