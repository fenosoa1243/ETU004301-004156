<nav class="navbar navbar-expand-lg navbar-client navbar-dark px-3 sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= site_url('client') ?>">
            <i class="bi bi-wallet2 me-1"></i> Mobile Money
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#clientNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="clientNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link <?= uri_string() === 'client' ? 'active' : '' ?>" href="<?= site_url('client') ?>">
                        <i class="bi bi-speedometer2 me-1"></i>Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains(uri_string(), 'client/solde') ? 'active' : '' ?>" href="<?= site_url('client/solde') ?>">
                        <i class="bi bi-cash-stack me-1"></i>Mon solde
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains(uri_string(), 'client/depot') ? 'active' : '' ?>" href="<?= site_url('client/depot') ?>">
                        <i class="bi bi-arrow-down-circle me-1"></i>Dépôt
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains(uri_string(), 'client/retrait') ? 'active' : '' ?>" href="<?= site_url('client/retrait') ?>">
                        <i class="bi bi-arrow-up-circle me-1"></i>Retrait
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains(uri_string(), 'client/transfert') && !str_contains(uri_string(), 'multiple') ? 'active' : '' ?>" href="<?= site_url('client/transfert') ?>">
                        <i class="bi bi-arrow-left-right me-1"></i>Transfert
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains(uri_string(), 'client/transfert-multiple') ? 'active' : '' ?>" href="<?= site_url('client/transfert-multiple') ?>">
                        <i class="bi bi-send-plus me-1"></i>Envoi multiple
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains(uri_string(), 'client/historique') ? 'active' : '' ?>" href="<?= site_url('client/historique') ?>">
                        <i class="bi bi-clock-history me-1"></i>Historique
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= esc(format_phone(session()->get('client_telephone'))) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="<?= site_url('client/profil') ?>">
                                <i class="bi bi-person me-2"></i>Mon profil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= site_url('client/logout') ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
