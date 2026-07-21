<?= $this->extend('client/layout/main') ?>
<?= $this->section('content') ?>

<h1 class="h3 mb-4"><i class="bi bi-send-plus text-primary me-2"></i>Envoi multiple</h1>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-stat p-4">
            <div class="alert alert-light border">
                <i class="bi bi-wallet2 me-1"></i>Solde disponible :
                <strong><?= format_money((float) $client['solde']) ?></strong>
            </div>
            <p class="text-muted small">
                Saisissez plusieurs numéros de destinataires (un par ligne, ou séparés par des virgules).
                Tous les numéros doivent appartenir au <strong>même opérateur</strong> que vous.
                Le montant saisi sera envoyé <strong>à chaque</strong> bénéficiaire.
            </p>
            <form method="post" action="<?= site_url('client/transfert-multiple') ?>" id="multiTransferForm">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Numéros des bénéficiaires</label>
                    <textarea class="form-control" id="beneficiairesTextarea" rows="5"
                              placeholder="0391111111&#10;0392222222&#10;0393333333"><?= esc(old('beneficiaires')) ?></textarea>
                    <div class="form-text">Un numéro de 10 chiffres par ligne (ou séparés par des virgules). Minimum 2 bénéficiaires.</div>
                    <input type="hidden" name="beneficiaires" id="beneficiairesHidden" value="<?= esc(old('beneficiaires')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Montant par bénéficiaire (Ar)</label>
                    <input type="number" step="1" min="1" name="montant_par_beneficiaire" id="montant"
                           class="form-control form-control-lg" value="<?= esc(old('montant_par_beneficiaire')) ?>" required>
                </div>

                <div id="operatorInfo" class="form-text mb-2">Saisissez les numéros pour vérifier l'opérateur.</div>

                <div class="card bg-light border-0 mb-3 d-none" id="feePreview">
                    <div class="card-body py-3">
                        <h6 class="mb-2">Détail des frais</h6>
                        <div class="d-flex justify-content-between"><span>Nombre de bénéficiaires</span><strong id="pvNb">—</strong></div>
                        <div class="d-flex justify-content-between"><span>Montant par bénéficiaire</span><strong id="pvMontant">—</strong></div>
                        <div class="d-flex justify-content-between"><span>Frais par bénéficiaire</span><strong id="pvFrais">—</strong></div>
                        <div class="d-flex justify-content-between"><span>Montant total par bénéficiaire</span><strong id="pvTotalBenef">—</strong></div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fs-5"><span>Montant total à débiter</span><strong id="pvTotal">—</strong></div>
                        <div class="d-flex justify-content-between text-muted small"><span>Solde après opération</span><strong id="pvSoldeApres">—</strong></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-send-plus me-1"></i>Confirmer l'envoi multiple
                </button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const textarea = document.getElementById('beneficiairesTextarea');
const hiddenField = document.getElementById('beneficiairesHidden');
const montantInput = document.getElementById('montant');
const operatorInfo = document.getElementById('operatorInfo');
const feePreview = document.getElementById('feePreview');
let debounceTimer;

function parseBeneficiaires() {
    return textarea.value
        .split(/[\n,]+/)
        .map(s => s.trim())
        .filter(s => s.length > 0);
}

function formatAr(n) {
    return new Intl.NumberFormat('fr-FR').format(n) + ' Ar';
}

function updatePreview() {
    const list = parseBeneficiaires();
    hiddenField.value = list.join(',');

    const montant = parseFloat(montantInput.value);

    if (list.length < 2 || !montant || montant <= 0) {
        feePreview.classList.add('d-none');
        operatorInfo.textContent = 'Saisissez au moins 2 numéros et un montant valide.';
        operatorInfo.className = 'form-text mb-2';
        return;
    }

    const params = new URLSearchParams({
        beneficiaires: list.join(','),
        montant: montant,
    });

    fetch(`<?= site_url('client/transfert-multiple/preview') ?>?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                operatorInfo.textContent = data.error;
                operatorInfo.className = 'form-text text-danger mb-2';
                feePreview.classList.add('d-none');
                return;
            }

            operatorInfo.textContent = `Tous les numéros appartiennent au même opérateur (${data.nombre_beneficiaires} bénéficiaires).`;
            operatorInfo.className = 'form-text text-success mb-2';

            document.getElementById('pvNb').textContent = data.nombre_beneficiaires;
            document.getElementById('pvMontant').textContent = formatAr(data.montant_par_beneficiaire);
            document.getElementById('pvFrais').textContent = formatAr(data.frais_par_beneficiaire);
            document.getElementById('pvTotalBenef').textContent = formatAr(data.montant_total_par_benef);
            document.getElementById('pvTotal').textContent = formatAr(data.montant_total);
            document.getElementById('pvSoldeApres').textContent = formatAr(data.solde_apres);

            feePreview.classList.remove('d-none');
        });
}

function debouncedPreview() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(updatePreview, 400);
}

textarea.addEventListener('input', debouncedPreview);
montantInput.addEventListener('input', debouncedPreview);

document.getElementById('multiTransferForm').addEventListener('submit', () => {
    hiddenField.value = parseBeneficiaires().join(',');
});
</script>
<?= $this->endSection() ?>
