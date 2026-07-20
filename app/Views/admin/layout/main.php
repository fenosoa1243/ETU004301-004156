<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Espace Opérateur') ?> | Mobile Money</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background-color: #1e2a38; }
        .sidebar .nav-link { color: #c9d3dd; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { color: #fff; background-color: #2c3e50; border-radius: .375rem; }
        .card-stat { border: none; border-radius: .75rem; box-shadow: 0 .125rem .5rem rgba(0,0,0,.08); }
    </style>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>