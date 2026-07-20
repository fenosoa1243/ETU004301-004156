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
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Revenu total</div>
            <div class="fs-4 fw-bold"><?= number_format($gains['revenu_total'], 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Opérations (filtrées)</div>
            <div class="fs-4 fw-bold"><?= $gains['nb_operations'] ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Moyenne des frais</div>
            <div class="fs-4 fw-bold"><?= number_format($gains['moyenne_frais'], 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <canvas id="chartGains" height="60"></canvas>
        </div>
    </div>
</div>

<div class="card card-stat p-3">
    <h6 class="mb-3">Détail des opérations</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr><th>Référence</th><th>Type</th><th>Expéditeur</th><th class="text-end">Montant</th><th class="text-end">Frais</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($gains['details'] as $d): ?>
                    <tr>
                        <td><?= esc($d['reference']) ?></td>
                        <td><?= esc($d['type_operation']) ?></td>
                        <td><?= esc($d['expediteur']) ?></td>
                        <td class="text-end"><?= number_format($d['montant'], 0, ',', ' ') ?></td>
                        <td class="text-end"><?= number_format($d['frais'], 0, ',', ' ') ?></td>
                        <td><?= esc($d['date_operation']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($gains['details'])): ?>
                    <tr><td colspan="6" class="text-center text-muted">Aucune opération sur cette période.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
new Chart(document.getElementById('chartGains'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($gains['par_type'], 'type_operation')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($gains['par_type'], 'total_frais')) ?>,
            backgroundColor: ['#198754', '#dc3545', '#0d6efd']
        }]
    }
});
</script>
<?= $this->endSection() ?>