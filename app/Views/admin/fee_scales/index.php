<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Barèmes des frais</h5>
    <a href="<?= site_url('admin/fee-scales/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Ajouter une tranche
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card card-stat p-3">
    <table class="table align-middle">
        <thead>
            <tr><th>Opération</th><th>Montant min</th><th>Montant max</th><th>Frais</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($feeScales as $scale): ?>
                <tr>
                    <td><span class="badge bg-secondary"><?= esc($scale['operation_nom']) ?></span></td>
                    <td><?= number_format($scale['montant_min'], 0, ',', ' ') ?> Ar</td>
                    <td><?= number_format($scale['montant_max'], 0, ',', ' ') ?> Ar</td>
                    <td><?= number_format($scale['frais'], 0, ',', ' ') ?> Ar</td>
                    <td class="text-end">
                        <a href="<?= site_url('admin/fee-scales/' . $scale['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="<?= site_url('admin/fee-scales/' . $scale['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Supprimer cette tranche ?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($feeScales)): ?>
                <tr><td colspan="5" class="text-center text-muted">Aucune tranche définie.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>