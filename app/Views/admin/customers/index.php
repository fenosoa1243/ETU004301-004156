<?= $this->extend('admin/layout/main') ?>
<?= $this->section('content') ?>

<form class="d-flex mb-3" method="get">
    <input type="text" name="search" value="<?= esc($search) ?>" class="form-control me-2" placeholder="Rechercher un numéro...">
    <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
</form>

<div class="card card-stat p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Numéro</th><th>Nom</th><th class="text-end">Solde</th><th>Créé le</th>
                    <th class="text-end">Opérations</th><th class="text-end">Dépôts</th>
                    <th class="text-end">Retraits</th><th class="text-end">Transferts</th><th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td class="fw-semibold"><?= esc($client['telephone']) ?></td>
                        <td><?= esc($client['nom'] ?: '—') ?></td>
                        <td class="text-end"><?= number_format($client['solde'], 0, ',', ' ') ?> Ar</td>
                        <td><?= esc($client['created_at']) ?></td>
                        <td class="text-end"><?= $client['nb_operations'] ?></td>
                        <td class="text-end"><?= number_format($client['total_depots'], 0, ',', ' ') ?></td>
                        <td class="text-end"><?= number_format($client['total_retraits'], 0, ',', ' ') ?></td>
                        <td class="text-end"><?= number_format($client['total_transferts'], 0, ',', ' ') ?></td>
                        <td class="text-end">
                            <a href="<?= site_url('admin/customers/' . $client['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($clients)): ?>
                    <tr><td colspan="9" class="text-center text-muted">Aucun client trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= $pager ?>
</div>

<?= $this->endSection() ?>