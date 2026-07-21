<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\ClientModel;
use App\Services\OperationService;
use RuntimeException;

class TransferController extends BaseController
{
    protected OperationService $operationService;

    public function __construct()
    {
        $this->operationService = new OperationService();
    }

    public function create()
    {
        $client = (new ClientModel())->find(session()->get('client_id'));

        return view('client/transfer/create', [
            'title'  => 'Transfert',
            'client' => $client,
        ]);
    }

    public function preview()
    {
        $telephone = normalize_phone((string) $this->request->getGet('telephone'));
        $montant   = (float) $this->request->getGet('montant');
        $inclure   = $this->request->getGet('inclure_frais_retrait') ? true : false;

        if ($montant <= 0 || strlen($telephone) !== 10) {
            return $this->response->setJSON(['error' => 'Paramètres invalides.']);
        }

        try {
            $preview = $this->operationService->previewTransfer($telephone, $montant, $inclure);
            $isExternal = $preview['is_external'];

            return $this->response->setJSON([
                'montant'                   => $preview['montant'],
                'frais'                     => $preview['frais'],
                'frais_retrait'             => $preview['frais_retrait'],
                'commission_supplementaire' => $preview['commission_supplementaire'],
                'montant_total'             => $preview['montant_total'],
                'is_external'               => $isExternal,
                'operator_nom'              => $preview['detection']['operator_nom'],
                'operator_type'             => $preview['detection']['type'],
                'can_include_retrait'       => ! $isExternal,
                'warning'                   => $isExternal ? 'Les frais de retrait ne s\'appliquent pas aux transferts vers les autres opérateurs.' : null,
            ]);
        } catch (RuntimeException $e) {
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function store()
    {
        $rules = [
            'telephone_destinataire' => 'required|numeric|exact_length[10]',
            'montant'                => 'required|numeric|greater_than[0]',
            'inclure_frais_retrait'  => 'permit_empty|in_list[0,1]',
        ];

        $messages = [
            'telephone_destinataire' => [
                'required'     => 'Le numéro du destinataire est obligatoire.',
                'numeric'      => 'Le numéro du destinataire doit être numérique.',
                'exact_length' => 'Le numéro du destinataire doit contenir exactement 10 chiffres.',
            ],
            'montant' => [
                'required'     => 'Le montant est obligatoire.',
                'numeric'      => 'Le montant doit être numérique.',
                'greater_than' => 'Le montant doit être supérieur à zéro.',
            ],
            'inclure_frais_retrait' => [
                'in_list' => 'L\'option d\'inclusion des frais de retrait est invalide.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $destinataire      = normalize_phone((string) $this->request->getPost('telephone_destinataire'));
        $montant           = (float) $this->request->getPost('montant');
        $inclureFraisRetrait = $this->request->getPost('inclure_frais_retrait') ? true : false;

        try {
            $detection = $this->operationService->previewTransfer($destinataire, $montant, $inclureFraisRetrait)['detection'];

            if ($inclureFraisRetrait && ! $detection['is_internal']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Les frais de retrait ne sont pas applicables aux transferts vers un autre opérateur.');
            }

            $transaction = $this->operationService->transfer(
                (int) session()->get('client_id'),
                $destinataire,
                $montant,
                $inclureFraisRetrait
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $detection = $transaction['detection'];
        $typeLabel = $detection['is_internal'] ? 'interne' : 'vers ' . $detection['operator_nom'];

        return redirect()->to(site_url('client'))
            ->with('success', 'Transfert ' . $typeLabel . ' de ' . format_money($montant) . ' effectué. Référence : ' . $transaction['reference']);
    }
}
