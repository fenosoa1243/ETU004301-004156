<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Espace Opérateur') ?> | Mobile Money</title>
    <link href="<?= base_url('vendor/bootstrap-5.3.3/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/bootstrap-icons/font/bootstrap-icons.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/admin.css') ?>" rel="stylesheet">
    <?= $this->renderSection('styles') ?>
</head>
<body>
<div class="d-flex">
    <?= $this->include('admin/layout/sidebar') ?>

    <div class="flex-grow-1">
        <?= $this->include('admin/layout/navbar') ?>

        <main class="p-4">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= esc(session()->getFlashdata('success')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= esc(session()->getFlashdata('error')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>

        <?= $this->include('admin/layout/footer') ?>
    </div>
</div>

<script src="<?= base_url('vendor/bootstrap-5.3.3/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('vendor/chart.js/chart.umd.min.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>