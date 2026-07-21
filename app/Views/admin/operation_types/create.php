<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="card card-stat p-4" style="max-width: 560px;">
    <h5 class="mb-3">Ajouter un type d'opération</h5>
    <form action="<?= site_url('admin/operation-types') ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="nom" class="form-control" value="<?= old('nom') ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= old('description') ?></textarea>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="applique_frais" value="1" class="form-check-input" id="appliqueFrais">
            <label class="form-check-label" for="appliqueFrais">Applique des frais</label>
        </div>
        <button class="btn btn-primary">Enregistrer</button>
        <a href="<?= site_url('admin/operation-types') ?>" class="btn btn-link">Annuler</a>
    </form>
</div>

<?= $this->endSection() ?>