<?php
require_once __DIR__ . '/config/db.php';
$tituloPagina = 'Serviços';

$tratamentos = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT id_tratamento, nome, descricao, preco 
                              FROM tratamentos 
                              WHERE ativo = 1 
                              ORDER BY nome ASC");
        $tratamentos = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Erro ao carregar tratamentos: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="secao secao-pagina-topo">
    <div class="container">
        <p class="eyebrow">O que oferecemos</p>
        <h1>Os nossos serviços</h1>
        <p class="texto-lead" style="max-width:640px">
            Especialidades e serviços clínicos pensados para acompanhar a sua saúde em todas as etapas da vida.
        </p>
    </div>
</section>

<section class="secao secao-servicos">
    <div class="container">
        <?php if (empty($tratamentos)): ?>
            <p>Ainda não existem serviços cadastrados. Volte brevemente.</p>
        <?php else: ?>
            <div class="grelha-servicos">
                <?php foreach ($tratamentos as $tratamento): ?>
                    <article class="cartao-servico">
                        <h3><?= htmlspecialchars($tratamento['nome']) ?></h3>
                        <p><?= htmlspecialchars($tratamento['descricao'] ?? '') ?></p>
                        <?php if (!empty($tratamento['preco'])): ?>
                            <p class="cartao-servico-preco">Desde <?= number_format((float)$tratamento['preco'], 2, ',', '.') ?> MT</p>
                        <?php endif; ?>
                        <a href="contacto.php?tratamento=<?= (int)$tratamento['id_tratamento'] ?>#agendamento" class="link-seta">Marcar consulta</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
