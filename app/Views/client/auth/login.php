<?= $this->extend('client/layout/guest') ?>
<?= $this->section('content') ?>

<div class="card auth-card p-4 p-md-5">
    <div class="text-center mb-4">
        <span class="badge bg-primary-subtle text-primary mb-3">
            <i class="bi bi-shield-lock me-1"></i> Espace Client
        </span>
        <i class="bi bi-wallet2 display-4 text-primary"></i>
        <h1 class="h4 fw-bold mt-2">Mobile Money</h1>
        <p class="text-muted mb-0">Connectez-vous avec votre numéro</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('client/login') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Numéro de téléphone</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                <input type="text" name="telephone" class="form-control form-control-lg"
                       placeholder="0301234567" value="<?= esc(old('telephone')) ?>" maxlength="10" required autofocus inputmode="numeric">
            </div>
            <div class="form-text mt-2">Exemples : 0301234567 ou 0391234567</div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100">
            <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
        </button>
    </form>

    <div class="mt-4 text-center small text-muted border-top pt-3">
        <i class="bi bi-headset me-1"></i>
        Opérateur : <strong><?= esc($operatorPhone ?? '0301234567') ?></strong>
    </div>
</div>

<?= $this->endSection() ?>
