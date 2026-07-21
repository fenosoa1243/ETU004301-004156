<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Clients</div>
            <div class="fs-3 fw-bold"><?= number_format($stats['nb_clients'], 0, ',', ' ') ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Dépôts</div>
            <div class="fs-3 fw-bold"><?= number_format($stats['nb_depots'], 0, ',', ' ') ?></div>
            <div class="text-success small"><?= number_format($stats['total_depots'], 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Retraits</div>
            <div class="fs-3 fw-bold"><?= number_format($stats['nb_retraits'], 0, ',', ' ') ?></div>
            <div class="text-danger small"><?= number_format($stats['total_retraits'], 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Transferts</div>
            <div class="fs-3 fw-bold"><?= number_format($stats['nb_transferts'], 0, ',', ' ') ?></div>
            <div class="text-primary small"><?= number_format($stats['total_transferts'], 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-stat p-3 bg-dark text-white">
            <div class="small">Revenus des frais</div>
            <div class="fs-4 fw-bold"><?= number_format($stats['revenu_frais'], 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Opérateurs partenaires</div>
            <div class="fs-3 fw-bold"><?= number_format($stats['nb_operateurs'], 0, ',', ' ') ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Transferts externes</div>
            <div class="fs-3 fw-bold"><?= number_format($stats['nb_transferts_externes'], 0, ',', ' ') ?></div>
            <div class="text-warning small"><?= number_format($stats['montant_transferts_externes'], 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Commission sup. collectée</div>
            <div class="fs-3 fw-bold text-success"><?= number_format($stats['commission_sup_collectee'], 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card card-stat p-3">
            <h6 class="mb-3">Opérations par type</h6>
            <canvas id="chartOperations" height="120"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-stat p-3">
            <h6 class="mb-3">Transferts internes vs externes</h6>
            <canvas id="chartTransferCompare" height="120"></canvas>
        </div>
    </div>
</div>

<div class="card card-stat p-3">
    <h6 class="mb-3">Dernières opérations</h6>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Référence</th><th>Type</th><th>Expéditeur</th><th>Destinataire</th>
                    <th>Opérateur</th><th class="text-end">Montant</th><th class="text-end">Frais</th><th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['dernieres_operations'] as $op): ?>
                    <tr>
                        <td><?= esc($op['reference']) ?></td>
                        <td><span class="badge bg-secondary"><?= esc($op['type_operation']) ?></span></td>
                        <td><?= esc($op['expediteur']) ?></td>
                        <td><?= esc($op['destinataire'] ?? '—') ?></td>
                        <td><?= esc($op['operateur_externe'] ?? 'Interne') ?></td>
                        <td class="text-end"><?= number_format($op['montant'], 0, ',', ' ') ?></td>
                        <td class="text-end"><?= number_format($op['frais'] + ($op['commission_supplementaire'] ?? 0), 0, ',', ' ') ?></td>
                        <td><?= esc($op['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($stats['dernieres_operations'])): ?>
                    <tr><td colspan="8" class="text-center text-muted">Aucune opération enregistrée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
new Chart(document.getElementById('chartOperations'), {
    type: 'bar',
    data: {
        labels: ['Dépôts', 'Retraits', 'Transferts'],
        datasets: [{
            label: "Nombre d'opérations",
            data: [<?= (int) $stats['nb_depots'] ?>, <?= (int) $stats['nb_retraits'] ?>, <?= (int) $stats['nb_transferts'] ?>],
            backgroundColor: ['#198754', '#dc3545', '#0d6efd']
        }]
    },
    options: { plugins: { legend: { display: false } } }
});

new Chart(document.getElementById('chartTransferCompare'), {
    type: 'doughnut',
    data: {
        labels: ['Internes', 'Externes'],
        datasets: [{
            data: [<?= (int) $stats['nb_transferts_internes'] ?>, <?= (int) $stats['nb_transferts_externes'] ?>],
            backgroundColor: ['#0d6efd', '#fd7e14']
        }]
    }
});
</script>
<?= $this->endSection() ?>
