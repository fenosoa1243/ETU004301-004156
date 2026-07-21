<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\CustomerService;

class CustomerController extends BaseController
{
    protected CustomerService $customerService;

    public function __construct()
    {
        $this->customerService = new CustomerService();
    }

    public function index()
    {
        $search  = $this->request->getGet('search');
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;

        $builder = $this->customerService->getAllWithStats($search);
        $total   = $builder->countAllResults(false);
        $clients = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        $pager = service('pager');
        $pager->setPath('admin/customers');

        return view('admin/customers/index', [
            'title'   => 'Comptes clients',
            'clients' => $clients,
            'search'  => $search,
            'pager'   => $pager->makeLinks($page, $perPage, $total),
        ]);
    }

    public function show(int $id)
    {
        $client = $this->customerService->getCustomerDetail($id);

        if ($client === null) {
            return redirect()->to('admin/customers')->with('error', 'Client introuvable.');
        }

        return view('admin/customers/show', [
            'title'  => 'Détail du client',
            'client' => $client,
        ]);
    }

    public function delete(int $id)
    {
        if (! $this->customerService->deleteCustomer($id)) {
            return redirect()->to('admin/customers')->with('error', 'Impossible de supprimer ce client.');
        }

        return redirect()->to('admin/customers')->with('success', 'Client supprimé avec succès.');
    }
}
