<?= $this->extend('client/layout/main') ?>
<?= $this->section('content') ?>

<h1 class="h3 mb-4"><i class="bi bi-person-circle me-2"></i>Mon profil</h1>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-stat p-4">
            <table class="table table-borderless mb-0">
                <tr>
                    <th class="text-muted">Téléphone</th>
                    <td class="fw-semibold"><?= esc(format_phone($client['telephone'])) ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Date de création</th>
                    <td><?= format_datetime_fr($client['created_at']) ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Nombre d'opérations</th>
                    <td><?= $nb_operations ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Total envoyé</th>
                    <td class="text-danger"><?= format_money($total_envoye) ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Total reçu</th>
                    <td class="text-success"><?= format_money($total_recu) ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Solde actuel</th>
                    <td class="fw-bold"><?= format_money((float) $client['solde']) ?></td>
                </tr>
            </table>
            <div class="alert alert-light border mt-3 mb-0 small">
                <i class="bi bi-info-circle me-1"></i>
                L'ajout d'un nom personnalisé sera disponible dans une prochaine version.
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
