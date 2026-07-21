<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Services\AuthService;

/**
 * Gère la connexion automatique du client (identification par numéro
 * de téléphone uniquement, sans inscription ni mot de passe).
 */
class AuthController extends BaseController
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login()
    {
        if (session()->get('client_id')) {
            return redirect()->to(site_url('client'));
        }

        return view('client/auth/login', [
            'title' => 'Connexion',
            'operatorPhone' => OPERATOR_PHONE,
            'loginExamples' => ['0301234567', '0391234567'],
        ]);
    }

    public function attempt()
    {
        $rules = [
            'telephone' => 'required|numeric|exact_length[10]',
        ];

        $messages = [
            'telephone' => [
                'required'     => 'Le numéro de téléphone est obligatoire.',
                'numeric'      => 'Le numéro de téléphone doit être numérique.',
                'exact_length' => 'Le numéro de téléphone doit contenir exactement 10 chiffres.',
                'regex_match'  => 'Le numéro doit commencer par 030 ou 039.',
            ],
        ];

        $telephone = normalize_phone((string) $this->request->getPost('telephone'));

        if (! $this->validateData(['telephone' => $telephone], $rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($telephone === OPERATOR_PHONE) {
            return redirect()->to(site_url('admin'))
                ->with('success', 'Bienvenue opérateur !');
        }

        if (! $this->authService->validatePrefix($telephone)) {
            return redirect()->back()->withInput()
                ->with('error', "Ce préfixe n'est pas pris en charge par l'opérateur.");
        }

        $client = $this->authService->findOrCreate($telephone);

        session()->set([
            'client_id'        => $client['id'],
            'client_telephone' => $client['telephone'],
        ]);

        return redirect()->to(site_url('client'))
            ->with('success', 'Bienvenue ' . format_phone($client['telephone']) . ' !');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to(site_url('client/login'))->with('success', 'Vous avez été déconnecté.');
    }
}
