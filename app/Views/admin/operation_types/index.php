<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form class="d-flex" method="get">
        <input type="text" name="search" value="<?= esc($search) ?>" class="form-control me-2" placeholder="Rechercher...">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="<?= site_url('admin/operation-types/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Ajouter un type
    </a>
</div>

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
            <tr><th>Nom</th><th>Description</th><th>Frais appliqués</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($types as $type): ?>
                <tr>
                    <td class="fw-semibold"><?= esc($type['nom']) ?></td>
                    <td><?= esc($type['description']) ?></td>
                    <td>
                        <?php if ($type['applique_frais']): ?>
                            <span class="badge bg-warning text-dark">Oui</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Non</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a href="<?= site_url('admin/operation-types/' . $type['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="<?= site_url('admin/operation-types/' . $type['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Supprimer ce type ?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($types)): ?>
                <tr><td colspan="4" class="text-center text-muted">Aucun type trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?= $pager->links() ?>
</div>

<?= $this->endSection() ?>