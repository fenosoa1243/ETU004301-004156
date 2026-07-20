<?= $this->extend('client/layout/main') ?>
<?= $this->section('content') ?>

<h1 class="h3 mb-4"><i class="bi bi-arrow-up-circle text-danger me-2"></i>Retrait</h1>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-stat p-4">
            <div class="alert alert-light border">
                <i class="bi bi-wallet2 me-1"></i>Solde disponible :
                <strong><?= format_money((float) $client['solde']) ?></strong>
            </div>
            <p class="text-muted small">
                Des frais sont automatiquement calculés selon le barème de l'opérateur et le montant retiré.
            </p>
            <form method="post" action="<?= site_url('client/retrait') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Montant à retirer (Ar)</label>
                    <input type="number" step="1" min="1" name="montant" class="form-control form-control-lg"
                           value="<?= esc(old('montant')) ?>" required>
                </div>
                <button type="submit" class="btn btn-danger btn-lg w-100">
                    <i class="bi bi-check-circle me-1"></i>Confirmer le retrait
                </button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
