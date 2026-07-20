<?= $this->extend('client/layout/main') ?>
<?= $this->section('content') ?>

<h1 class="h3 mb-4"><i class="bi bi-arrow-left-right text-primary me-2"></i>Transfert</h1>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-stat p-4">
            <div class="alert alert-light border">
                <i class="bi bi-wallet2 me-1"></i>Solde disponible :
                <strong><?= format_money((float) $client['solde']) ?></strong>
            </div>
            <p class="text-muted small">
                Des frais sont automatiquement calculés selon le barème de l'opérateur et le montant transféré.
            </p>
            <form method="post" action="<?= site_url('client/transfert') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Numéro du destinataire</label>
                    <input type="text" name="telephone_destinataire" class="form-control form-control-lg"
                           maxlength="10" placeholder="0391234567"
                           value="<?= esc(old('telephone_destinataire')) ?>" required>
                    <div class="form-text">Si le numéro n'existe pas encore, un compte sera automatiquement créé.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Montant à transférer (Ar)</label>
                    <input type="number" step="1" min="1" name="montant" class="form-control form-control-lg"
                           value="<?= esc(old('montant')) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-send me-1"></i>Confirmer le transfert
                </button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
