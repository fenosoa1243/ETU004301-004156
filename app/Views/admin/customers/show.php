<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-stat p-3">
            <div class="text-muted small">Numéro</div>
            <div class="fs-4 fw-bold"><?= esc($client['telephone']) ?></div>
            <div class="text-muted small mt-2">Nom</div>
            <div><?= esc($client['nom'] ?: 'Non renseigné') ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat p-3">
            <div class="text-muted small">Solde actuel</div>
            <div class="fs-3 fw-bold text-success"><?= number_format($client['solde'], 0, ',', ' ') ?> Ar</div>
            <div class="text-muted small mt-2">Compte créé le</div>
            <div><?= esc($client['created_at']) ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat p-3">
            <div class="row text-center">
                <div class="col">
                    <div class="fs-5 fw-bold"><?= $client['nb_depots'] ?></div>
                    <div class="small text-muted">Dépôts</div>
                </div>
                <div class="col">
                    <div class="fs-5 fw-bold"><?= $client['nb_retraits'] ?></div>
                    <div class="small text-muted">Retraits</div>
                </div>
                <div class="col">
                    <div class="fs-5 fw-bold"><?= $client['nb_transferts'] ?></div>
                    <div class="small text-muted">Transferts</div>
                </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between small">
                <span>Total envoyé</span><span class="fw-semibold"><?= number_format($client['total_envoye'], 0, ',', ' ') ?> Ar</span>
            </div>
            <div class="d-flex justify-content-between small">
                <span>Total reçu</span><span class="fw-semibold"><?= number_format($client['total_recu'], 0, ',', ' ') ?> Ar</span>
            </div>
        </div>
    </div>
</div>

<div class="card card-stat p-3">
    <h6 class="mb-3">Historique complet</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr><th>Référence</th><th>Type</th><th>Destinataire</th><th class="text-end">Montant</th><th class="text-end">Frais</th><th class="text-end">Solde après</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach ($client['operations'] as $op): ?>
                    <tr>
                        <td><?= esc($op['reference']) ?></td>
                        <td><?= (int) $op['operation_type_id'] === 1 ? 'Dépôt' : ((int) $op['operation_type_id'] === 2 ? 'Retrait' : 'Transfert') ?></td>
                        <td><?= $op['client_destination_id'] ?? '—' ?></td>
                        <td class="text-end"><?= number_format($op['montant'], 0, ',', ' ') ?></td>
                        <td class="text-end"><?= number_format($op['frais'], 0, ',', ' ') ?></td>
                        <td class="text-end"><?= number_format($op['solde_apres'], 0, ',', ' ') ?></td>
                        <td><?= esc($op['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($client['operations'])): ?>
                    <tr><td colspan="7" class="text-center text-muted">Aucune opération.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<a href="<?= site_url('admin/customers') ?>" class="btn btn-link mt-3">← Retour à la liste</a>
<div class="d-flex gap-2 mt-3">
    <a href="<?= site_url('admin/customers') ?>" class="btn btn-secondary">← Retour à la liste</a>
    <?php if (($client['telephone'] ?? '') !== OPERATOR_PHONE): ?>
        <form action="<?= site_url('admin/customers/' . $client['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Supprimer ce client ?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger">Supprimer le client</button>
        </form>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
