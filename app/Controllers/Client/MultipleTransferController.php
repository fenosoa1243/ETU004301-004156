<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Services\MultipleTransferService;
use App\Validation\MultipleTransferRules;
use RuntimeException;

class MultipleTransferController extends BaseController
{
    protected MultipleTransferService $multipleTransferService;

    public function __construct()
    {
        $this->multipleTransferService = new MultipleTransferService();
    }

    /**
     * Affiche le formulaire de transfert multiple.
     */
    public function create()
    {
        $client = (new ClientModel())->find(session()->get('client_id'));

        return view('client/transfer/multiple-create', [
            'title'  => 'Transfert multiple',
            'client' => $client,
        ]);
    }

    /**
     * Prévisualise les frais du transfert multiple.
     */
    public function preview()
    {
        $beneficiaires = $this->request->getGet('beneficiaires');
        $montant       = (float) $this->request->getGet('montant');
        $clientId      = (int) session()->get('client_id');

        if (empty($beneficiaires) || $montant <= 0) {
            return $this->response->setJSON(['error' => 'Paramètres invalides.']);
        }

        try {
            $list = array_filter(array_map('trim', explode(',', $beneficiaires)));
            $validation = $this->multipleTransferService->validate($clientId, $list);

            if (!$validation['valid']) {
                return $this->response->setJSON([
                    'error' => implode(' ', $validation['errors']),
                ]);
            }

            // Calculer les frais par bénéficiaire
            $feeService = new \App\Services\FeeService();
            $fee = $feeService->calculateDetailedFee(
                \App\Services\OperationService::TRANSFERT,
                $montant,
                false,  // isExternal (jamais pour transferts multiples)
                true,   // appliqueFrais
                false   // inclureFraisRetrait
            );

            $montantTotal = $fee['montant_total'] * count($list);
            $client = (new ClientModel())->find($clientId);

            return $this->response->setJSON([
                'montant_par_beneficiaire' => $montant,
                'nombre_beneficiaires'     => count($list),
                'frais_par_beneficiaire'   => $fee['frais'],
                'montant_total_par_benef'  => $fee['montant_total'],
                'montant_total'            => $montantTotal,
                'solde_client'             => (float) $client['solde'],
                'solde_apres'              => (float) $client['solde'] - $montantTotal,
            ]);
        } catch (RuntimeException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    /**
     * Traite le transfert multiple.
     */
    public function store()
    {
        $rules = [
            'beneficiaires'          => 'required|validate_beneficiaires_list',
            'montant_par_beneficiaire' => 'required|validate_montant_multiple',
        ];

        $messages = [
            'beneficiaires' => [
                'required' => 'La liste des bénéficiaires est obligatoire.',
            ],
            'montant_par_beneficiaire' => [
                'required' => 'Le montant par bénéficiaire est obligatoire.',
            ],
        ];

        // Charger les règles personnalisées
        $this->validator->setRule('beneficiaires', 'beneficiaires', [
            new \App\Validation\MultipleTransferRules(),
            'validate_beneficiaires_list',
        ]);

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $clientId = (int) session()->get('client_id');
        $beneficiairesStr = (string) $this->request->getPost('beneficiaires');
        $montant = (float) $this->request->getPost('montant_par_beneficiaire');

        $beneficiaires = array_filter(array_map('trim', explode(',', $beneficiairesStr)));

        try {
            // Validation finale
            $validation = $this->multipleTransferService->validate($clientId, $beneficiaires);
            if (!$validation['valid']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Validation échouée : ' . implode('; ', $validation['errors']));
            }

            // Effectuer le transfert
            $result = $this->multipleTransferService->transfer($clientId, $beneficiaires, $montant);

            return redirect()->to(site_url('client'))
                ->with('success',
                    'Transfert multiple de ' . format_money($montant) . ' effectué vers '
                    . $result['nombre_beneficiaires'] . ' bénéficiaires. '
                    . 'Référence batch : ' . $result['reference_batch']
                );
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
