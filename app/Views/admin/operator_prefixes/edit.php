<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="card card-stat p-4" style="max-width: 480px;">
    <h5 class="mb-3">Modifier un préfixe externe</h5>
    <form action="<?= site_url('admin/operator-prefixes/' . $prefix['id']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="operator_id" class="form-label">Opérateur</label>
            <select id="operator_id" name="operator_id" class="form-select" required>
                <option value="">Sélectionnez un opérateur</option>
                <?php foreach ($operators as $operator): ?>
                    <option value="<?= esc($operator['id']) ?>" <?= $operator['id'] == old('operator_id', $prefix['operator_id']) ? 'selected' : '' ?>><?= esc($operator['nom'] . ' (' . $operator['code'] . ')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="prefix" class="form-label">Préfixe</label>
            <input type="text" id="prefix" name="prefix" value="<?= esc(old('prefix', $prefix['prefix'])) ?>" class="form-control" maxlength="3" required>
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
        <a href="<?= site_url('admin/operator-prefixes') ?>" class="btn btn-link">Annuler</a>
    </form>
</div>

<?= $this->endSection() ?>