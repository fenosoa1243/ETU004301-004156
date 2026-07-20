<?php

namespace App\Services;

use App\Models\ClientModel;
use App\Models\TransactionModel;
use Config\Database;

class CustomerService
{
    protected ClientModel $clientModel;
    protected TransactionModel $transactionModel;

    protected const DEPOT     = 1;
    protected const RETRAIT   = 2;
    protected const TRANSFERT = 3;

    public function __construct()
    {
        $this->clientModel      = new ClientModel();
        $this->transactionModel = new TransactionModel();
    }

    public function getAllWithStats(?string $search = null)
    {
        $db = Database::connect();

        $builder = $db->table('clients c')
            ->select("
                c.id, c.telephone, c.nom, c.solde, c.created_at,
                (SELECT COUNT(*) FROM transactions t WHERE t.client_source_id = c.id OR t.client_destination_id = c.id) AS nb_operations,
                (SELECT COALESCE(SUM(montant),0) FROM transactions t WHERE t.client_source_id = c.id AND t.operation_type_id = " . self::DEPOT . ") AS total_depots,
                (SELECT COALESCE(SUM(montant),0) FROM transactions t WHERE t.client_source_id = c.id AND t.operation_type_id = " . self::RETRAIT . ") AS total_retraits,
                (SELECT COALESCE(SUM(montant),0) FROM transactions t WHERE t.client_source_id = c.id AND t.operation_type_id = " . self::TRANSFERT . ") AS total_transferts
            ");

        if ($search) {
            $builder->like('c.telephone', $search);
        }

        return $builder->orderBy('c.created_at', 'DESC');
    }

    public function getCustomerDetail(int $id): ?array
    {
        $client = $this->clientModel->find($id);

        if ($client === null) {
            return null;
        }

        $operations = $this->transactionModel->forClient($id);

        $client['nb_depots']     = count(array_filter($operations, fn ($o) => (int) $o['operation_type_id'] === self::DEPOT && (int) $o['client_source_id'] === $id));
        $client['nb_retraits']   = count(array_filter($operations, fn ($o) => (int) $o['operation_type_id'] === self::RETRAIT && (int) $o['client_source_id'] === $id));
        $client['nb_transferts'] = count(array_filter($operations, fn ($o) => (int) $o['operation_type_id'] === self::TRANSFERT && (int) $o['client_source_id'] === $id));

        $client['total_envoye'] = array_sum(array_map(
            fn ($o) => (int) $o['client_source_id'] === $id ? $o['montant'] : 0,
            $operations
        ));

        $client['total_recu'] = array_sum(array_map(
            fn ($o) => (int) ($o['client_destination_id'] ?? 0) === $id ? $o['montant'] : 0,
            $operations
        ));

        $client['operations'] = $operations;

        return $client;
    }
}