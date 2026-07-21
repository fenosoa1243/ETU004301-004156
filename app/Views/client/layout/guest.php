<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Connexion') ?> | Mobile Money</title>
    <link href="<?= base_url('vendor/bootstrap-5.3.3/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/bootstrap-icons/font/bootstrap-icons.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/client-guest.css') ?>" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <?= $this->renderSection('content') ?>
        </div>
    </div>
</div>
<script src="<?= base_url('vendor/bootstrap-5.3.3/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
