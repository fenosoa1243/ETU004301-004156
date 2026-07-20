<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <form class="d-flex" method="get">
        <input type="text" name="search" value="<?= esc($search) ?>" class="form-control me-2" placeholder="Rechercher un préfixe...">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
    </form>
    <a href="<?= site_url('admin/prefixes/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Ajouter un préfixe
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
            <tr><th>Préfixe</th><th>Statut</th><th>Créé le</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($prefixes as $prefix): ?>
                <tr>
                    <td class="fw-semibold"><?= esc($prefix['prefix']) ?></td>
                    <td>
                        <?php if ($prefix['actif']): ?>
                            <span class="badge bg-success">Actif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactif</span>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($prefix['created_at']) ?></td>
                    <td class="text-end">
                        <a href="<?= site_url('admin/prefixes/' . $prefix['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="<?= site_url('admin/prefixes/' . $prefix['id'] . '/toggle') ?>" method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-toggle2-<?= $prefix['actif'] ? 'on' : 'off' ?>"></i>
                            </button>
                        </form>
                        <form action="<?= site_url('admin/prefixes/' . $prefix['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Supprimer ce préfixe ?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($prefixes)): ?>
                <tr><td colspan="4" class="text-center text-muted">Aucun préfixe trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?= $pager->links() ?>
</div>

<?= $this->endSection() ?>