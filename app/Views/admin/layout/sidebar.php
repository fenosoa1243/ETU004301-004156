<nav class="sidebar p-3" style="width: 250px;">
    <a href="<?= site_url('admin') ?>" class="d-flex align-items-center mb-4 text-white text-decoration-none">
        <i class="bi bi-wallet2 fs-4 me-2"></i>
        <span class="fs-5 fw-bold">Mobile Money</span>
    </a>
    <ul class="nav nav-pills flex-column gap-1">
        <li class="nav-item">
            <a href="<?= site_url('admin') ?>" class="nav-link <?= uri_string() === 'admin' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Tableau de bord
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= site_url('admin/prefixes') ?>" class="nav-link <?= str_contains(uri_string(), 'admin/prefixes') ? 'active' : '' ?>">
                <i class="bi bi-hash me-2"></i> Préfixes
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= site_url('admin/operation-types') ?>" class="nav-link <?= str_contains(uri_string(), 'admin/operation-types') ? 'active' : '' ?>">
                <i class="bi bi-arrow-left-right me-2"></i> Types d'opérations
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= site_url('admin/fee-scales') ?>" class="nav-link <?= str_contains(uri_string(), 'admin/fee-scales') ? 'active' : '' ?>">
                <i class="bi bi-cash-coin me-2"></i> Barèmes des frais
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= site_url('admin/gains') ?>" class="nav-link <?= str_contains(uri_string(), 'admin/gains') ? 'active' : '' ?>">
                <i class="bi bi-graph-up-arrow me-2"></i> Situation des gains
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= site_url('admin/statistics') ?>" class="nav-link <?= str_contains(uri_string(), 'admin/statistics') ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line me-2"></i> Statistiques
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= site_url('admin/customers') ?>" class="nav-link <?= str_contains(uri_string(), 'admin/customers') ? 'active' : '' ?>">
                <i class="bi bi-people me-2"></i> Comptes clients
            </a>
        </li>
    </ul>
</nav>