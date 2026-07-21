<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<?php
    $lignes          = $stats['lignes'] ?? [];
    $totalTransferts = $stats['total_transferts'] ?? 0;
    $totalMontant    = $stats['total_montant'] ?? 0;
    $totalCommission = $stats['total_commission'] ?? 0;
    $totalNet        = $stats['total_net_a_envoyer'] ?? 0;

    $sortLink = static function (string $column, string $label) use ($sort, $order) {
        $nextOrder = ($sort === $column && $order === 'ASC') ? 'DESC' : 'ASC';
        $icon = '';
        if ($sort === $column) {
            $icon = $order === 'ASC' ? ' <i class="bi bi-caret-up-fill"></i>' : ' <i class="bi bi-caret-down-fill"></i>';
        }
        $url = site_url('admin/settlements') . '?sort=' . $column . '&order=' . $nextOrder;
        return '<a href="' . $url . '" class="text-decoration-none text-dark">' . esc($label) . $icon . '</a>';
    };
?>

<form class="d-flex mb-3" method="get">
    <input type="hidden" name="sort" value="<?= esc($sort) ?>">
    <input type="hidden" name="order" value="<?= esc($order) ?>">
    <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>" class="form-control me-2" placeholder="Rechercher un opérateur...">
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</form>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Transferts externes</div>
            <div class="fs-4 fw-bold"><?= number_format($totalTransferts, 0, ',', ' ') ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Montant transféré</div>
            <div class="fs-4 fw-bold"><?= number_format($totalMontant, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Commission perçue</div>
            <div class="fs-4 fw-bold text-success"><?= number_format($totalCommission, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="text-muted small">Montant net à envoyer</div>
            <div class="fs-4 fw-bold text-primary"><?= number_format($totalNet, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
</div>

<div class="card card-stat p-3">
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th><?= $sortLink('operateur', 'Opérateur') ?></th>
                    <th class="text-end"><?= $sortLink('nb_transferts', 'Nombre de transferts') ?></th>
                    <th class="text-end"><?= $sortLink('montant_total', 'Montant total') ?></th>
                    <th class="text-end"><?= $sortLink('commission_percue', 'Commission perçue') ?></th>
                    <th class="text-end"><?= $sortLink('montant_net_a_envoyer', 'Montant net à envoyer') ?></th>
                    <th class="text-end"><?= $sortLink('derniere_date', 'Dernière date') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignes as $row): ?>
                    <tr>
                        <td><?= esc($row['operateur']) ?></td>
                        <td class="text-end"><?= number_format((int) $row['nb_transferts'], 0, ',', ' ') ?></td>
                        <td class="text-end"><?= number_format((float) $row['montant_total'], 0, ',', ' ') ?> Ar</td>
                        <td class="text-end"><?= number_format((float) $row['commission_percue'], 0, ',', ' ') ?> Ar</td>
                        <td class="text-end"><?= number_format((float) $row['montant_net_a_envoyer'], 0, ',', ' ') ?> Ar</td>
                        <td class="text-end"><?= esc($row['derniere_date'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($lignes)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Aucune donnée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>