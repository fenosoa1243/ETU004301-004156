<?= $this->extend('client/layout/main') ?>
<?= $this->section('content') ?>

<h1 class="h3 mb-4"><i class="bi bi-arrow-left-right text-primary me-2"></i>Transfert</h1>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-stat p-4">
            <div class="alert alert-light border">
                <i class="bi bi-wallet2 me-1"></i>Solde disponible :
                <strong><?= format_money((float) $client['solde']) ?></strong>
            </div>
            <p class="text-muted small">
                Le système détecte automatiquement l'opérateur du destinataire.
                Les transferts vers un autre opérateur incluent une commission supplémentaire.
            </p>
            <form method="post" action="<?= site_url('client/transfert') ?>" id="transferForm">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Numéro du destinataire</label>
                    <input type="text" name="telephone_destinataire" id="telephone" maxlength="10"
                           class="form-control form-control-lg" placeholder="0391234567 ou 0341234567"
                           value="<?= esc(old('telephone_destinataire')) ?>" required>
                    <div class="form-text" id="operatorInfo">Saisissez un numéro pour détecter l'opérateur.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Montant à transférer (Ar)</label>
                    <input type="number" step="1" min="1" name="montant" id="montant"
                           class="form-control form-control-lg" value="<?= esc(old('montant')) ?>" required>
                </div>

                <input type="hidden" name="inclure_frais_retrait" id="inclureFraisRetraitHidden" value="<?= esc(old('inclure_frais_retrait', '0')) ?>">
                <div class="form-check form-switch mb-3 d-none" id="withdrawalFeeOption">
                    <input class="form-check-input" type="checkbox" id="inclureFraisRetrait" value="1">
                    <label class="form-check-label" for="inclureFraisRetrait">Inclure les frais de retrait</label>
                </div>
                <div class="alert alert-warning d-none" id="withdrawalFeeWarning"></div>

                <div class="card bg-light border-0 mb-3 d-none" id="feePreview">
                    <div class="card-body py-3">
                        <h6 class="mb-2">Détail des frais</h6>
                        <div class="d-flex justify-content-between"><span>Montant</span><strong id="pvMontant">—</strong></div>
                        <div class="d-flex justify-content-between"><span>Commission</span><strong id="pvFrais">—</strong></div>
                        <div class="d-flex justify-content-between"><span>Frais de retrait</span><strong id="pvFraisRetrait">—</strong></div>
                        <div class="d-flex justify-content-between text-warning d-none" id="pvSupRow"><span>Commission supplémentaire</span><strong id="pvSup">—</strong></div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fs-5"><span>Montant total</span><strong id="pvTotal">—</strong></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-send me-1"></i>Confirmer le transfert
                </button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const telInput = document.getElementById('telephone');
const montantInput = document.getElementById('montant');
const feePreview = document.getElementById('feePreview');
const operatorInfo = document.getElementById('operatorInfo');
const withdrawalFeeOption = document.getElementById('withdrawalFeeOption');
const withdrawalFeeWarning = document.getElementById('withdrawalFeeWarning');
const inclureFraisRetraitCheckbox = document.getElementById('inclureFraisRetrait');
const inclureFraisRetraitHidden = document.getElementById('inclureFraisRetraitHidden');
let debounceTimer;

function formatAr(n) {
    return new Intl.NumberFormat('fr-FR').format(n) + ' Ar';
}

function updatePreview() {
    const tel = telInput.value.trim();
    const montant = parseFloat(montantInput.value);
    const inclure = inclureFraisRetraitCheckbox.checked;

    if (tel.length !== 10 || !montant || montant <= 0) {
        feePreview.classList.add('d-none');
        withdrawalFeeOption.classList.add('d-none');
        withdrawalFeeWarning.classList.add('d-none');
        return;
    }

    fetch(`<?= site_url('client/transfert/preview') ?>?telephone=${encodeURIComponent(tel)}&montant=${montant}&inclure_frais_retrait=${inclure ? 1 : 0}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                operatorInfo.textContent = data.error;
                operatorInfo.className = 'form-text text-danger';
                feePreview.classList.add('d-none');
                withdrawalFeeOption.classList.add('d-none');
                withdrawalFeeWarning.classList.add('d-none');
                return;
            }

            operatorInfo.textContent = data.is_external
                ? `Opérateur détecté : ${data.operator_nom} (transfert externe)`
                : `Opérateur détecté : ${data.operator_nom} (transfert interne)`;
            operatorInfo.className = data.is_external ? 'form-text text-warning' : 'form-text text-success';

            document.getElementById('pvMontant').textContent = formatAr(data.montant);
            document.getElementById('pvFrais').textContent = formatAr(data.frais);
            document.getElementById('pvFraisRetrait').textContent = formatAr(data.frais_retrait);
            document.getElementById('pvTotal').textContent = formatAr(data.montant_total);

            const supRow = document.getElementById('pvSupRow');
            if (data.commission_supplementaire > 0) {
                supRow.classList.remove('d-none');
                document.getElementById('pvSup').textContent = formatAr(data.commission_supplementaire);
            } else {
                supRow.classList.add('d-none');
            }

            if (data.can_include_retrait) {
                withdrawalFeeOption.classList.remove('d-none');
                withdrawalFeeWarning.classList.add('d-none');
            } else {
                withdrawalFeeOption.classList.add('d-none');
                withdrawalFeeWarning.textContent = data.warning || 'Les frais de retrait ne s\'appliquent pas aux transferts vers les autres opérateurs.';
                withdrawalFeeWarning.classList.remove('d-none');
                inclureFraisRetraitCheckbox.checked = false;
                inclureFraisRetraitHidden.value = '0';
            }

            feePreview.classList.remove('d-none');
        });
}

function debouncedPreview() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(updatePreview, 400);
}

telInput.addEventListener('input', debouncedPreview);
montantInput.addEventListener('input', debouncedPreview);
</script>
<?= $this->endSection() ?>
