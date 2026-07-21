<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Modifier un opérateur</h4>
    <a href="<?= site_url('admin/operators') ?>" class="btn btn-secondary">Retour</a>
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

<div class="card card-stat p-4">
    <form action="<?= site_url('admin/operators/' . $operator['id']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" id="nom" name="nom" value="<?= esc(old('nom', $operator['nom'])) ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <input type="text" id="code" name="code" value="<?= esc(old('code', $operator['code'])) ?>" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>

<?= $this->endSection() ?>