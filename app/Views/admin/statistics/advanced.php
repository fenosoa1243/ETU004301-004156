<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card card-stat p-3">
            <h6>Opérations par jour (14 derniers jours)</h6>
            <canvas id="chartParJour" height="120"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-stat p-3">
            <h6>Opérations par mois</h6>
            <canvas id="chartParMois" height="120"></canvas>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card card-stat p-3">
            <h6>Répartition des montants par type</h6>
            <canvas id="chartParType" height="120"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-stat p-3">
            <h6>Top 5 clients (volume)</h6>
            <table class="table table-sm">
                <thead><tr><th>Client</th><th class="text-end">Opérations</th><th class="text-end">Volume</th></tr></thead>
                <tbody>
                    <?php foreach ($stats['top_clients'] as $c): ?>
                        <tr>
                            <td><?= esc($c['nom'] ?: $c['telephone']) ?></td>
                            <td class="text-end"><?= $c['nb_operations'] ?></td>
                            <td class="text-end"><?= number_format($c['volume'], 0, ',', ' ') ?> Ar</td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($stats['top_clients'])): ?>
                        <tr><td colspan="3" class="text-center text-muted">Aucune donnée.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
new Chart(document.getElementById('chartParJour'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($stats['par_jour'], 'jour')) ?>,
        datasets: [{ label: 'Opérations', data: <?= json_encode(array_column($stats['par_jour'], 'nombre')) ?>, borderColor: '#0d6efd', tension: .3 }]
    }
});
new Chart(document.getElementById('chartParMois'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($stats['par_mois'], 'mois')) ?>,
        datasets: [{ label: 'Opérations', data: <?= json_encode(array_column($stats['par_mois'], 'nombre')) ?>, backgroundColor: '#198754' }]
    }
});
new Chart(document.getElementById('chartParType'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($stats['par_type'], 'type_operation')) ?>,
        datasets: [{ data: <?= json_encode(array_column($stats['par_type'], 'total_montant')) ?>, backgroundColor: ['#198754', '#dc3545', '#0d6efd'] }]
    }
});
</script>
<?= $this->endSection() ?>