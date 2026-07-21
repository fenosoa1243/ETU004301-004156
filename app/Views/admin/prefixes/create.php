<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="card card-stat p-4" style="max-width: 480px;">
    <h5 class="mb-3">Ajouter un préfixe</h5>
    <form action="<?= site_url('admin/prefixes') ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Préfixe (3 chiffres)</label>
            <input type="text" name="prefix" maxlength="3" class="form-control" value="<?= old('prefix') ?>" required>
        </div>
        <button class="btn btn-primary">Enregistrer</button>
        <a href="<?= site_url('admin/prefixes') ?>" class="btn btn-link">Annuler</a>
    </form>
</div>

<?= $this->endSection() ?>