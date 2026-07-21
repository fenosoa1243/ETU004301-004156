<?= $this->extend('client/layout/main') ?>
<?= $this->section('content') ?>

<h1 class="h3 mb-1">Détail de la transaction</h1>
<h2 class="h6 text-muted mb-4 font-monospace"><?= esc($operation['reference']) ?></h2>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-stat p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge <?= transaction_badge($operation['type_operation']) ?> fs-6">
                    <?= esc($operation['type_operation']) ?>
                </span>
                <span class="text-muted"><?= format_datetime_fr($operation['created_at']) ?></span>
            </div>
            <table class="table table-borderless mb-0">
                <tr>
                    <th class="text-muted">Expéditeur</th>
                    <td><?= $operation['expediteur'] ? esc(format_phone($operation['expediteur'])) : '—' ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Destinataire</th>
                    <td><?= $operation['destinataire'] ? esc(format_phone($operation['destinataire'])) : '—' ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Montant</th>
                    <td class="fw-semibold"><?= format_money(abs((float) $operation['montant'])) ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Frais</th>
                    <td><?= format_money((float) $operation['frais']) ?></td>
                </tr>
                <?php if ((float) ($operation['frais_retrait'] ?? 0) > 0): ?>
                <tr>
                    <th class="text-muted">Frais de retrait inclus</th>
                    <td><?= format_money((float) $operation['frais_retrait']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ((float) ($operation['commission_supplementaire'] ?? 0) > 0): ?>
                <tr>
                    <th class="text-muted">Commission supplémentaire</th>
                    <td><?= format_money((float) $operation['commission_supplementaire']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (! empty($operation['batch_reference'])): ?>
                <tr>
                    <th class="text-muted">Référence du lot (envoi multiple)</th>
                    <td class="font-monospace"><?= esc($operation['batch_reference']) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th class="text-muted">Montant total</th>
                    <td class="fw-bold"><?= format_money((float) $operation['montant_total']) ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Solde avant</th>
                    <td><?= format_money((float) $operation['solde_avant']) ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Solde après</th>
                    <td><?= format_money((float) $operation['solde_apres']) ?></td>
                </tr>
            </table>
            <a href="<?= site_url('client/historique') ?>" class="btn btn-outline-secondary mt-3 align-self-start">
                <i class="bi bi-arrow-left me-1"></i>Retour à l'historique
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
