<?= $this->extend('client/layout/main') ?>
<?= $this->section('content') ?>

<h1 class="h3 mb-4"><i class="bi bi-clock-history me-2"></i>Historique des transactions</h1>

<div class="card card-stat p-3 mb-3">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Recherche</label>
            <input type="text" name="search" class="form-control" placeholder="Référence, numéro..."
                   value="<?= esc($filters['search']) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label small">Type</label>
            <select name="type" class="form-select">
                <option value="">Tous</option>
                <?php foreach (['Dépôt', 'Retrait', 'Transfert'] as $type): ?>
                    <option value="<?= esc($type) ?>" <?= $filters['type'] === $type ? 'selected' : '' ?>>
                        <?= esc($type) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Du</label>
            <input type="date" name="date_debut" class="form-control" value="<?= esc($filters['date_debut']) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label small">Au</label>
            <input type="date" name="date_fin" class="form-control" value="<?= esc($filters['date_fin']) ?>">
        </div>
        <div class="col-md-1">
            <label class="form-label small">Min</label>
            <input type="number" name="montant_min" class="form-control" value="<?= esc($filters['montant_min']) ?>">
        </div>
        <div class="col-md-1">
            <label class="form-label small">Max</label>
            <input type="number" name="montant_max" class="form-control" value="<?= esc($filters['montant_max']) ?>">
        </div>
        <div class="col-md-1">
            <button class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
        </div>
    </form>
</div>

<div class="card card-stat p-3">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Type</th><th>Référence</th><th>Expéditeur</th><th>Destinataire</th>
                    <th>Montant</th><th>Frais</th><th>Date</th><th class="text-end">Détail</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($operations as $op): ?>
                    <?php $sign = transaction_sign($op, $client_id); ?>
                    <tr>
                        <td>
                            <span class="badge <?= transaction_badge($op['type_operation']) ?>">
                                <?= esc($op['type_operation']) ?>
                            </span>
                        </td>
                        <td class="font-monospace small"><?= esc($op['reference']) ?></td>
                        <td><?= $op['expediteur'] ? esc(format_phone($op['expediteur'])) : '—' ?></td>
                        <td><?= $op['destinataire'] ? esc(format_phone($op['destinataire'])) : '—' ?></td>
                        <td class="<?= $sign === '-' ? 'text-danger' : 'text-success' ?> fw-semibold">
                            <?= $sign ?> <?= format_money((float) $op['montant']) ?>
                        </td>
                        <td><?= format_money((float) $op['frais']) ?></td>
                        <td><?= format_datetime_fr($op['created_at']) ?></td>
                        <td class="text-end">
                            <a href="<?= site_url('client/historique/' . $op['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($operations)): ?>
                    <tr><td colspan="8" class="text-center text-muted">Aucune opération trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= $pager->links() ?>
</div>

<?= $this->endSection() ?>
