<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form class="d-flex" method="get">
        <input type="text" name="search" value="<?= esc($search) ?>" class="form-control me-2" placeholder="Rechercher...">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="<?= site_url('admin/promotions/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Ajouter une promotion
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
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

<?php if ($active): ?>
    <div class="alert alert-info">
        Promotion actuellement appliquée sur les frais de transfert interne : <strong><?= esc($active['pourcentage']) ?>%</strong> de réduction.
    </div>
<?php else: ?>
    <div class="alert alert-secondary">Aucune promotion active actuellement.</div>
<?php endif; ?>

<div class="card card-stat p-3 mb-3">
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Pourcentage</th>
                <th>Actif</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($promotions as $promotion): ?>
                <tr>
                    <td class="fw-semibold"><?= esc($promotion['pourcentage']) ?>%</td>
                    <td>
                        <?php if ($promotion['actif']): ?>
                            <span class="badge bg-success">Actif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a href="<?= site_url('admin/promotions/' . $promotion['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="<?= site_url('admin/promotions/' . $promotion['id'] . '/toggle') ?>" method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-warning" type="submit">
                                <i class="bi bi-toggle2-<?= $promotion['actif'] ? 'on' : 'off' ?>"></i>
                            </button>
                        </form>
                        <form action="<?= site_url('admin/promotions/' . $promotion['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Supprimer cette promotion ?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($promotions)): ?>
                <tr><td colspan="3" class="text-center text-muted">Aucune promotion trouvée.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?= $pager->links() ?>
</div>

<div class="card card-stat p-3">
    <h5>Historique des changements</h5>
    <table class="table align-middle">
        <thead>
            <tr>
                <th>Promotion</th>
                <th>Avant</th>
                <th>Après</th>
                <th>Modifié le</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $item): ?>
                <tr>
                    <td><?= esc($item['pourcentage_actuel']) ?>%</td>
                    <td><?= esc($item['pourcentage_avant']) ?>%</td>
                    <td><?= esc($item['pourcentage_apres']) ?>%</td>
                    <td><?= esc($item['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($history)): ?>
                <tr><td colspan="4" class="text-center text-muted">Aucun historique disponible.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>