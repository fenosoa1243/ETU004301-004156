<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<form class="row g-2 mb-4" method="get">
    <div class="col-auto">
        <input type="date" name="date_debut" value="<?= esc($filters['date_debut']) ?>" class="form-control">
    </div>
    <div class="col-auto">
        <input type="date" name="date_fin" value="<?= esc($filters['date_fin']) ?>" class="form-control">
    </div>
    <div class="col-auto">
        <select name="type" class="form-select">
            <option value="">Tous les types</option>
            <option value="Retrait" <?= $filters['type'] === 'Retrait' ? 'selected' : '' ?>>Retrait</option>
            <option value="Transfert" <?= $filters['type'] === 'Transfert' ? 'selected' : '' ?>>Transfert</option>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Filtrer</button>
    </div>
</form>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card card-stat p-3 h-100">
            <h6 class="text-primary mb-3"><i class="bi bi-house me-1"></i>Gains opérateur interne</h6>
            <div class="row g-2">
                <div class="col-4">
                    <div class="text-muted small">Transferts internes</div>
                    <div class="fs-4 fw-bold"><?= number_format($gains['internes']['nb_transferts'], 0, ',', ' ') ?></div>
                </div>
                <div class="col-4">
                    <div class="text-muted small">Montant des frais</div>
                    <div class="fs-4 fw-bold"><?= number_format($gains['internes']['total_frais'], 0, ',', ' ') ?> Ar</div>
                </div>
                <div class="col-4">
                    <div class="text-muted small">Total montant</div>
                    <div class="fs-4 fw-bold"><?= number_format($gains['internes']['total_montant'], 0, ',', ' ') ?> Ar</div>
                </div>
            </div>
            <canvas id="chartInternes" height="80" class="mt-3"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-stat p-3 h-100">
            <h6 class="text-warning mb-3"><i class="bi bi-globe me-1"></i>Gains autres opérateurs</h6>
            <div class="row g-2">
                <div class="col-3">
                    <div class="text-muted small">Transferts externes</div>
                    <div class="fs-5 fw-bold"><?= number_format($gains['externes']['nb_transferts'], 0, ',', ' ') ?></div>
                </div>
                <div class="col-3">
                    <div class="text-muted small">Frais</div>
                    <div class="fs-5 fw-bold"><?= number_format($gains['externes']['total_frais'], 0, ',', ' ') ?> Ar</div>
                </div>
                <div class="col-3">
                    <div class="text-muted small">Comm. sup.</div>
                    <div class="fs-5 fw-bold"><?= number_format($gains['externes']['total_commission_sup'], 0, ',', ' ') ?> Ar</div>
                </div>
                <div class="col-3">
                    <div class="text-muted small">Total gains</div>
                    <div class="fs-5 fw-bold text-success"><?= number_format($gains['externes']['total_gains'], 0, ',', ' ') ?> Ar</div>
                </div>
            </div>
            <canvas id="chartExternes" height="80" class="mt-3"></canvas>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-stat p-3">
            <div class="text-muted small">Revenu total</div>
            <div class="fs-4 fw-bold"><?= number_format($gains['revenu_total'], 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat p-3">
            <div class="text-muted small">Opérations (filtrées)</div>
            <div class="fs-4 fw-bold"><?= $gains['nb_operations'] ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat p-3">
            <div class="text-muted small">Moyenne des frais</div>
            <div class="fs-4 fw-bold"><?= number_format($gains['moyenne_frais'], 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
</div>

<div class="card card-stat p-3">
    <h6 class="mb-3">Détail des opérations</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr><th>Référence</th><th>Type</th><th>Expéditeur</th><th>Destinataire</th><th class="text-end">Montant</th><th class="text-end">Frais</th><th class="text-end">Comm. sup.</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($gains['details'] as $d): ?>
                    <tr>
                        <td><?= esc($d['reference']) ?></td>
                        <td><?= esc($d['type_operation']) ?></td>
                        <td><?= esc($d['expediteur']) ?></td>
                        <td><?= esc($d['destinataire'] ?? '—') ?></td>
                        <td class="text-end"><?= number_format($d['montant'], 0, ',', ' ') ?></td>
                        <td class="text-end"><?= number_format($d['frais'], 0, ',', ' ') ?></td>
                        <td class="text-end"><?= number_format($d['commission_supplementaire'] ?? 0, 0, ',', ' ') ?></td>
                        <td><?= esc($d['date_operation']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($gains['details'])): ?>
                    <tr><td colspan="8" class="text-center text-muted">Aucune opération sur cette période.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
new Chart(document.getElementById('chartInternes'), {
    type: 'bar',
    data: {
        labels: ['Frais internes'],
        datasets: [{ data: [<?= (float) $gains['internes']['total_frais'] ?>], backgroundColor: '#0d6efd' }]
    },
    options: { plugins: { legend: { display: false } } }
});
new Chart(document.getElementById('chartExternes'), {
    type: 'bar',
    data: {
        labels: ['Frais', 'Comm. sup.', 'Total'],
        datasets: [{
            data: [<?= (float) $gains['externes']['total_frais'] ?>, <?= (float) $gains['externes']['total_commission_sup'] ?>, <?= (float) $gains['externes']['total_gains'] ?>],
            backgroundColor: ['#fd7e14', '#ffc107', '#198754']
        }]
    },
    options: { plugins: { legend: { display: false } } }
});
</script>
<?= $this->endSection() ?>
