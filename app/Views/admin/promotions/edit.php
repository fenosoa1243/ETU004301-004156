<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="card card-stat p-4" style="max-width: 480px;">
    <h5 class="mb-3">Modifier la promotion</h5>
    <form action="<?= site_url('admin/promotions/' . $promotion['id']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="pourcentage" class="form-label">Pourcentage de réduction sur les frais</label>
            <input type="number" id="pourcentage" name="pourcentage" value="<?= esc(old('pourcentage', $promotion['pourcentage'])) ?>" class="form-control" min="0" max="100" step="0.01" required>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" id="actif" name="actif" value="1" class="form-check-input" <?= old('actif', $promotion['actif']) ? 'checked' : '' ?>>
            <label for="actif" class="form-check-label">Actif</label>
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <a href="<?= site_url('admin/promotions') ?>" class="btn btn-link">Annuler</a>
    </form>
</div>

<?= $this->endSection() ?>