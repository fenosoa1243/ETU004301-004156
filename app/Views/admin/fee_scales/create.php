<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="card card-stat p-4" style="max-width: 560px;">
    <h5 class="mb-3">Ajouter une tranche de frais</h5>
    <form action="<?= site_url('admin/fee-scales') ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Type d'opération</label>
            <select name="operation_type_id" class="form-select" required>
                <option value="">-- Sélectionner --</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= $type['id'] ?>" <?= old('operation_type_id') == $type['id'] ? 'selected' : '' ?>>
                        <?= esc($type['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="row">
            <div class="col mb-3">
                <label class="form-label">Montant minimum</label>
                <input type="number" step="0.01" name="montant_min" class="form-control" value="<?= old('montant_min') ?>" required>
            </div>
            <div class="col mb-3">
                <label class="form-label">Montant maximum</label>
                <input type="number" step="0.01" name="montant_max" class="form-control" value="<?= old('montant_max') ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Frais</label>
            <input type="number" step="0.01" name="frais" class="form-control" value="<?= old('frais') ?>" required>
        </div>
        <button class="btn btn-primary">Enregistrer</button>
        <a href="<?= site_url('admin/fee-scales') ?>" class="btn btn-link">Annuler</a>
    </form>
</div>

<?= $this->endSection() ?>