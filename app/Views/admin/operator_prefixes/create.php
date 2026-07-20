<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="card card-stat p-4" style="max-width: 480px;">
    <h5 class="mb-3">Ajouter un préfixe externe</h5>
    <form action="<?= site_url('admin/operator-prefixes') ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="operator_id" class="form-label">Opérateur</label>
            <select id="operator_id" name="operator_id" class="form-select" required>
                <option value="">Sélectionnez un opérateur</option>
                <?php foreach ($operators as $operator): ?>
                    <option value="<?= esc($operator['id']) ?>" <?= set_select('operator_id', $operator['id']) ?>><?= esc($operator['nom'] . ' (' . $operator['code'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="prefix" class="form-label">Préfixe</label>
            <input type="text" id="prefix" name="prefix" value="<?= esc(old('prefix')) ?>" class="form-control" maxlength="3" required>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" id="actif" name="actif" value="1" class="form-check-input" <?= set_checkbox('actif', '1') ?>>
            <label for="actif" class="form-check-label">Actif</label>
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="<?= site_url('admin/operator-prefixes') ?>" class="btn btn-link">Annuler</a>
    </form>
</div>

<?= $this->endSection() ?>