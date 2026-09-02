<?php
$tituloPagina = 'Sobre Nós';

/**
 * EQUIPA
 * Lê automaticamente as fotos em assets/images/galeria/equipe/
 * Convenção do nome do ficheiro: nome-completo--cargo.jpg
 * Exemplo: ana-costa--enfermeira-chefe.jpg  →  Nome: "Ana Costa"  Cargo: "Enfermeira Chefe"
 * Se não usares "--", o cargo assume o valor por omissão definido abaixo.
 */
$pastaEquipe = __DIR__ . '/assets/images/galeria/equipe';
$extensoesValidas = ['jpg', 'jpeg', 'png', 'webp'];
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

<section class="secao">
    <div class="container grelha-2">
        <div>
            <h2>A nossa missão</h2>
            <p>
                Prestar cuidados médicos com rigor científico e proximidade humana, acompanhando
                cada paciente com atenção individual, desde a consulta até ao acompanhamento pós-tratamento.
            </p>
            <ul class="lista-check">
                <li>Atendimento humanizado e personalizado</li>
                <li>Equipa médica qualificada e em constante formação</li>
                <li>Infraestrutura preparada para diagnóstico e tratamento</li>
            </ul>
        </div>
        <div class="cartao-destaque">
            <p class="cartao-destaque-numero" data-contador data-alvo="10" data-prefixo="+">0</p>
            <p class="cartao-destaque-legenda">anos de experiência</p>
            <div class="cartao-destaque-linha"></div>
            <p class="cartao-destaque-numero" data-contador data-alvo="50" data-prefixo="+">0</p>
            <p class="cartao-destaque-legenda">especialidades e serviços</p>
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