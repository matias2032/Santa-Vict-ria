<?php
$tituloPagina = 'Sobre Nós';

$extensoesValidas = ['jpg', 'jpeg', 'png', 'webp'];

/**
 * EQUIPA
 * Lê automaticamente as fotos em assets/images/galeria/equipe/
 * Convenção do nome do ficheiro: nome-completo--cargo.jpg
 * Exemplo: ana-costa--enfermeira-chefe.jpg  →  Nome: "Ana Costa"  Cargo: "Enfermeira Chefe"
 * Se não usares "--", o cargo assume o valor por omissão definido abaixo.
 */
$pastaEquipe = __DIR__ . '/assets/images/galeria/equipe';
$equipe = [];

if (is_dir($pastaEquipe)) {
    foreach (scandir($pastaEquipe) as $ficheiro) {
        $ext = strtolower(pathinfo($ficheiro, PATHINFO_EXTENSION));
        if (!in_array($ext, $extensoesValidas, true)) {
            continue;
        }

        $nomeBase = pathinfo($ficheiro, PATHINFO_FILENAME); // sem extensão
        $partes = explode('--', $nomeBase, 2);

        $nome  = str_replace(['-', '_'], ' ', $partes[0]);
        $cargo = isset($partes[1]) ? str_replace(['-', '_'], ' ', $partes[1]) : 'Equipa Santa Victória';

        $equipe[] = [
            'foto'  => 'assets/images/galeria/equipe/' . $ficheiro,
            'nome'  => ucwords($nome),
            'cargo' => ucwords($cargo),
        ];
    }
}

/**
 * FOTO DE DESTAQUE
 * Usa a primeira fotografia encontrada em assets/images/galeria/ (fora da subpasta equipe/)
 * para ilustrar a secção "A nossa missão". Se não houver nenhuma, a secção
 * simplesmente não mostra a imagem (sem quebrar o layout).
 */
$pastaGaleria = __DIR__ . '/assets/images/galeria';
$fotoDestaque = null;

if (is_dir($pastaGaleria)) {
    foreach (scandir($pastaGaleria) as $ficheiro) {
        $caminhoCompleto = $pastaGaleria . '/' . $ficheiro;
        if (!is_file($caminhoCompleto)) {
            continue; // ignora subpastas, incluindo equipe/
        }
        $ext = strtolower(pathinfo($ficheiro, PATHINFO_EXTENSION));
        if (in_array($ext, $extensoesValidas, true)) {
            $fotoDestaque = 'assets/images/galeria/' . $ficheiro;
            break;
        }
    }
}

/**
 * PARCEIROS
 * Lê automaticamente os logótipos em assets/images/galeria/parceiros/
 * O nome do ficheiro (sem extensão) é usado como texto alternativo (alt).
 * Exemplo: farmacia-jm.png → alt="Farmacia Jm"
 */
$pastaParceiros = __DIR__ . '/assets/images/galeria/parceiros';
$parceiros = [];

if (is_dir($pastaParceiros)) {
    foreach (scandir($pastaParceiros) as $ficheiro) {
        $ext = strtolower(pathinfo($ficheiro, PATHINFO_EXTENSION));
        if (!in_array($ext, $extensoesValidas, true)) {
            continue;
        }

        $nomeBase = pathinfo($ficheiro, PATHINFO_FILENAME);
        $nome = ucwords(str_replace(['-', '_'], ' ', $nomeBase));

        $parceiros[] = [
            'logo' => 'assets/images/galeria/parceiros/' . $ficheiro,
            'nome' => $nome,
        ];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="secao secao-pagina-topo">
    <div class="container">
        <p class="eyebrow">Sobre nós</p>
        <h1>Uma equipa dedicada à sua saúde</h1>
        <p class="texto-lead" style="max-width:640px">
            O Centro Médico Santa Victória nasceu do compromisso de oferecer cuidados de saúde
            de qualidade, acessíveis e humanos à comunidade de Tete.
        </p>
    </div>
</section>

<!-- <section class="faixa-estatisticas">
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
</section> -->

<section class="secao">
    <div class="container grelha-2">
        <div>
            <p class="eyebrow">A nossa missão</p>
            <h2>Cuidar com rigor, tratar com humanidade</h2>
            <p>
                Prestar cuidados médicos com rigor científico e proximidade humana, acompanhando
                cada paciente com atenção individual, desde a consulta até ao acompanhamento pós-tratamento.
            </p>
            <ul class="lista-check">
                <li>Atendimento humanizado e personalizado</li>
                <li>Equipa médica qualificada e em constante formação</li>
                <li>Infraestrutura preparada para diagnóstico e tratamento</li>
            </ul>
            <a href="contacto.php#agendamento" class="link-seta">Marcar a sua consulta</a>
        </div>

        <?php if ($fotoDestaque): ?>
            <div class="cartao-foto-sobre">
                <img src="<?= htmlspecialchars($fotoDestaque) ?>" alt="Instalações do Centro Médico Santa Victória" loading="lazy">
                <span class="cartao-foto-sobre-selo">Ao seu serviço em Tete</span>
            </div>
        <?php else: ?>
            <div class="cartao-destaque">
                <p class="cartao-destaque-eyebrow">O nosso compromisso</p>
                <h3 class="cartao-destaque-titulo">Sempre perto de si</h3>
                <p class="cartao-destaque-texto">Cuidados de saúde acessíveis, com uma equipa atenta a cada detalhe do seu bem-estar.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="secao secao-valores">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow">O que nos guia</p>
            <h2>Missão, visão e valores</h2>
            <p class="texto-lead">Os princípios que orientam cada decisão e cada atendimento no Centro Médico Santa Victória.</p>
        </div>

        <div class="grelha-valores">
            <div class="cartao-valor">
                <div class="cartao-valor-icone">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.8 4.6a5.4 5.4 0 0 0-7.6 0L12 5.8l-1.2-1.2a5.4 5.4 0 1 0-7.6 7.6L12 21l8.8-8.8a5.4 5.4 0 0 0 0-7.6z"/>
                    </svg>
                </div>
                <h3>Missão</h3>
                <p>Prestar cuidados de saúde acessíveis e de qualidade, com rigor clínico e proximidade humana.</p>
            </div>

            <div class="cartao-valor">
                <div class="cartao-valor-icone">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <h3>Visão</h3>
                <p>Ser reconhecidos como referência em cuidados de saúde na região de Tete, pela confiança dos pacientes.</p>
            </div>

            <div class="cartao-valor">
                <div class="cartao-valor-icone">
                    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>Valores</h3>
                <p>Ética, empatia e melhoria contínua — em cada consulta, em cada interação com quem nos procura.</p>
            </div>
        </div>
    </div>
</section>

<section class="secao secao-historia">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow">A nossa história</p>
            <h2>Como chegámos até aqui</h2>
            <p class="texto-lead"><em>(Exemplo de linha do tempo — ajusta os anos e marcos à história real da clínica.)</em></p>
        </div>

        <div class="linha-tempo">
            <div class="marco-tempo">
                <span class="marco-tempo-ano">2016</span>
                <h3>Fundação</h3>
                <p>Abertura do Centro Médico Santa Victória, com consulta geral e um pequeno quadro clínico.</p>
            </div>
            <div class="marco-tempo">
                <span class="marco-tempo-ano">2019</span>
                <h3>Expansão de serviços</h3>
                <p>Introdução de novas especialidades, incluindo pediatria e ginecologia.</p>
            </div>
            <div class="marco-tempo">
                <span class="marco-tempo-ano">2022</span>
                <h3>Novo equipamento</h3>
                <p>Investimento em equipamento de diagnóstico para reforçar a qualidade do atendimento.</p>
            </div>
            <div class="marco-tempo">
                <span class="marco-tempo-ano">Hoje</span>
                <h3>Ao serviço da comunidade</h3>
                <p>Uma equipa maior e mais especializada, continuando comprometida com a saúde de Tete.</p>
            </div>
        </div>
    </div>
</section>

<section class="secao secao-equipe">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow">A nossa equipa</p>
            <h2>Quem cuida de si</h2>
            <p class="texto-lead">Profissionais dedicados, prontos para acompanhar a sua saúde com atenção e proximidade.</p>
        </div>

        <?php if (empty($equipe)): ?>
            <p>Ainda não há fotos da equipa. Adiciona-as em <code>assets/images/galeria/equipe/</code>.</p>
        <?php else: ?>
            <div class="grelha-galeria">
                <?php foreach ($equipe as $membro): ?>
                    <div class="galeria-item galeria-item-equipe">
                        <img src="<?= htmlspecialchars($membro['foto']) ?>" alt="<?= htmlspecialchars($membro['nome']) ?>" loading="lazy">
                        <div class="galeria-item-legenda">
                            <p class="galeria-item-nome"><?= htmlspecialchars($membro['nome']) ?></p>
                            <p class="galeria-item-cargo"><?= htmlspecialchars($membro['cargo']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($parceiros)): ?>
<section class="secao secao-parceiros">
    <div class="container">
        <div class="secao-cabecalho">
            <p class="eyebrow">Parcerias</p>
            <h2>Quem caminha connosco</h2>
            <p class="texto-lead">Entidades e empresas que colaboram com o Centro Médico Santa Victória.</p>
        </div>

        <div class="grelha-parceiros">
            <?php foreach ($parceiros as $parceiro): ?>
                <div class="cartao-parceiro">
                    <img src="<?= htmlspecialchars($parceiro['logo']) ?>" alt="<?= htmlspecialchars($parceiro['nome']) ?>" loading="lazy">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="secao secao-cta">
    <div class="container secao-cta-interno">
        <div>
            <h2>Quer conhecer os nossos serviços?</h2>
            <p>Descubra todas as especialidades disponíveis no nosso centro médico.</p>
        </div>
        <a href="servicos.php" class="botao botao-branco">Ver serviços</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>