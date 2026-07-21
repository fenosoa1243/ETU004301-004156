<?= $this->extend('client/layout/main') ?>
<?= $this->section('content') ?>

<h1 class="h3 mb-4">Mon solde</h1>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card solde-card p-4">
            <div class="opacity-75 small">Solde actuel</div>
            <div class="display-5 fw-bold mb-3"><?= format_money((float) $client['solde']) ?></div>
            <a href="<?= site_url('client/solde') ?>" class="btn btn-light btn-sm align-self-start">
                <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
            </a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-stat p-4 h-100">
            <h2 class="h6">Détails</h2>
            <ul class="list-unstyled mb-0">
                <li class="mb-2">
                    <i class="bi bi-hash me-2 text-muted"></i>Numéro :
                    <strong><?= esc(format_phone($client['telephone'])) ?></strong>
                </li>
                <li class="mb-2">
                    <i class="bi bi-bar-chart me-2 text-muted"></i>Nombre total d'opérations :
                    <strong><?= $nb_operations ?></strong>
                </li>
                <li>
                    <i class="bi bi-clock-history me-2 text-muted"></i>Date dernière opération :
                    <strong><?= $derniere_operation ? format_datetime_fr($derniere_operation['created_at']) : 'Aucune opération' ?></strong>
                </li>
            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
