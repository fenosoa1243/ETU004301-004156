<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<form class="row g-2 mb-4" method="get">
    
    <div class="col-auto">
        <select name="mois" class="form-select">
            <option value="">Mois</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= ((int) ($report['filters']['mois'] ?? 0) === $m) ? 'selected' : '' ?>><?= esc((string) $m) ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-auto">
        <input type="number" name="annee" value="<?= esc($report['filters']['annee'] ?? '') ?>" class="form-control" placeholder="Année" style="width:120px;">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Filtrer</button>
    </div>
    <div class="col-auto ms-auto">
        <a href="<?= site_url('admin/reports/export/pdf') ?>" class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
        </a>
        <a href="<?= site_url('admin/reports/export/excel') ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
    </div>
</form>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card card-stat p-3">
            <h6 class="mb-3">Répartition des transferts</h6>
            <canvas id="chartTransferts" height="100"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-stat p-3">
            <h6 class="mb-3">Répartition des revenus</h6>
            <canvas id="chartRevenus" height="100"></canvas>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card card-stat p-3">
            <h6 class="mb-3">Montants dus aux autres opérateurs</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Opérateur</th><th class="text-end">Transferts</th><th class="text-end">Montant</th><th class="text-end">Commission perçue</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['montants_dus'] as $m): ?>
                            <tr>
                                <td><?= esc($m['operateur']) ?></td>
                                <td class="text-end"><?= number_format($m['nb_transferts'], 0, ',', ' ') ?></td>
                                <td class="text-end"><?= number_format($m['montant_total'], 0, ',', ' ') ?> Ar</td>
                                <td class="text-end"><?= number_format($m['commission_percue'], 0, ',', ' ') ?> Ar</td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($report['montants_dus'])): ?>
                            <tr><td colspan="4" class="text-center text-muted">Aucune donnée.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card card-stat p-3">
            <h6 class="mb-3">Top opérateurs</h6>
            <table class="table table-sm">
                <thead><tr><th>Opérateur</th><th class="text-end">Transferts</th><th class="text-end">Volume</th></tr></thead>
                <tbody>
                    <?php foreach ($report['top_operateurs'] as $t): ?>
                        <tr>
                            <td><?= esc($t['operateur']) ?></td>
                            <td class="text-end"><?= number_format($t['nb'], 0, ',', ' ') ?></td>
                            <td class="text-end"><?= number_format($t['montant'], 0, ',', ' ') ?> Ar</td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($report['top_operateurs'])): ?>
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
const repartitionTransferts = <?= json_encode($report['repartition_transferts']) ?>;
const labelsTransferts = repartitionTransferts.map(r => r.label);
const dataTransferts = repartitionTransferts.map(r => r.nombre);

new Chart(document.getElementById('chartTransferts'), {
    type: 'pie',
    data: { labels: labelsTransferts, datasets: [{ data: dataTransferts, backgroundColor: ['#0d6efd', '#fd7e14'] }] },
    options: { plugins: { legend: { position: 'bottom' } } }
});

const repartitionRevenus = <?= json_encode($report['repartition_revenus']) ?>;
const labelsRevenus = repartitionRevenus.map(r => r.label);

new Chart(document.getElementById('chartRevenus'), {
    type: 'bar',
    data: {
        labels: labelsRevenus,
        datasets: [{
            label: 'Montant (Ar)',
            data: repartitionRevenus.map(r => r.montant),
            backgroundColor: ['#198754', '#ffc107']
        }]
    },
    options: { plugins: { legend: { display: false } } }
});
</script>
<?= $this->endSection() ?>