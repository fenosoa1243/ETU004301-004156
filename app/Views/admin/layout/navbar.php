<nav class="navbar navbar-light bg-white border-bottom px-4">
    <span class="navbar-text fw-semibold text-dark">
        <?= esc($title ?? 'Espace Opérateur') ?>
    </span>
    <span class="navbar-text text-muted">
        <i class="bi bi-person-circle me-1"></i> Opérateur
    </span>
    <div class="d-flex justify-content-between align-items-center w-100">
        <span class="navbar-text fw-semibold text-dark">
            <?= esc($title ?? 'Espace Opérateur') ?>
        </span>
        <div class="d-flex align-items-center gap-3">
            <span class="navbar-text text-muted d-none d-md-inline">
                <i class="bi bi-person-circle me-1"></i> Opérateur
            </span>
            <a href="<?= site_url('client/logout') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
            </a>
        </div>
    </div>
</nav>
