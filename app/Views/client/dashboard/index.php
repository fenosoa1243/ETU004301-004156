<?= $this->extend('client/layout/main') ?>
<?= $this->section('content') ?>

<h1 class="h3 mb-1">Bienvenue, <?= esc(format_phone($client['telephone'])) ?></h1>
<p class="text-muted mb-4">Voici un aperçu de votre compte.</p>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card solde-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="opacity-75 small">Solde actuel</div>
                    <div class="display-6 fw-bold"><?= format_money((float) $client['solde']) ?></div>
                </div>
                <i class="bi bi-wallet2 fs-1 opacity-75"></i>
            </div>
            <a href="<?= site_url('client/solde') ?>" class="btn btn-light btn-sm mt-3 align-self-start">
                <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
            </a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3 h-100 text-center d-flex flex-column justify-content-center">
            <div class="text-muted small">Opérations totales</div>
            <div class="fs-3 fw-bold"><?= $nb_operations ?></div>
        </div>
    </div>
    <!-- <div class="col-md-3">
        <div class="card card-stat p-3 h-100 d-flex flex-column justify-content-center">
            <div class="text-muted small mb-1">Envoyé / Reçu</div>
            <div class="text-danger fw-semibold"><i class="bi bi-arrow-up-short"></i> <?= format_money($total_envoye) ?></div>
            <div class="text-success fw-semibold"><i class="bi bi-arrow-down-short"></i> <?= format_money($total_recu) ?></div>
        </div>
    </div> -->
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <a href="<?= site_url('client/depot') ?>" class="btn btn-outline-success w-100 py-3">
            <i class="bi bi-arrow-down-circle d-block fs-3 mb-1"></i>Dépôt
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= site_url('client/retrait') ?>" class="btn btn-outline-danger w-100 py-3">
            <i class="bi bi-arrow-up-circle d-block fs-3 mb-1"></i>Retrait
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= site_url('client/transfert') ?>" class="btn btn-outline-primary w-100 py-3">
            <i class="bi bi-arrow-left-right d-block fs-3 mb-1"></i>Transfert
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= site_url('client/transfert-multiple') ?>" class="btn btn-outline-info w-100 py-3">
            <i class="bi bi-send-plus d-block fs-3 mb-1"></i>Envoi multiple
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= site_url('client/historique') ?>" class="btn btn-outline-secondary w-100 py-3">
            <i class="bi bi-clock-history d-block fs-3 mb-1"></i>Historique
        </a>
    </div>
</div>

<div class="card card-stat p-3">
    <h2 class="h6 mb-3">Dernières opérations</h2>
    <?php if (empty($dernieres_operations)): ?>
        <p class="text-muted mb-0">Aucune opération pour le moment.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr><th>Type</th><th>Référence</th><th>Montant</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php
                        $typeNames = [1 => 'Dépôt', 2 => 'Retrait', 3 => 'Transfert'];
                    ?>
                    <?php foreach ($dernieres_operations as $op): ?>
                        <?php
                            $typeName = $typeNames[(int) $op['operation_type_id']] ?? '—';
                            $sign     = transaction_sign($op, (int) $client['id']);
                        ?>
                        <tr>
                            <td><i class="bi <?= transaction_icon($typeName) ?> me-1"></i><?= esc($typeName) ?></td>
                            <td class="font-monospace small"><?= esc($op['reference']) ?></td>
                            <td class="fw-semibold">
                                <?= format_money(abs((float) $op['montant'])) ?>
                            </td>
                            <td><?= format_datetime_fr($op['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
