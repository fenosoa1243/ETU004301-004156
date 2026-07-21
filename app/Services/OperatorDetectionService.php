<?php

namespace App\Services;

use App\Models\OperatorPrefixModel;
use App\Models\PrefixModel;
use RuntimeException;

/**
 * Détecte automatiquement l'opérateur d'un numéro de téléphone.
 */
class OperatorDetectionService
{
    public const TYPE_INTERNAL = 'internal';
    public const TYPE_EXTERNAL = 'external';

    protected PrefixModel $prefixModel;
    protected OperatorPrefixModel $operatorPrefixModel;

    public function __construct()
    {
        $this->prefixModel         = new PrefixModel();
        $this->operatorPrefixModel = new OperatorPrefixModel();
    }

    /**
     * @return array{
     *     type: string,
     *     prefix: string,
     *     operator_id: ?int,
     *     operator_nom: string,
     *     operator_code: ?string,
     *     is_internal: bool
     * }
     */
    public function detect(string $telephone): array
    {
        $telephone = normalize_phone($telephone);
        $prefix    = extract_prefix($telephone);

        if ($this->isInternalPrefix($prefix)) {
            return [
                'type'          => self::TYPE_INTERNAL,
                'prefix'        => $prefix,
                'operator_id'   => null,
                'operator_nom'  => 'Notre opérateur',
                'operator_code' => 'INTERNAL',
                'is_internal'   => true,
            ];
        }

        $external = $this->operatorPrefixModel->findByPrefix($prefix);

        if ($external !== null) {
            return [
                'type'          => self::TYPE_EXTERNAL,
                'prefix'        => $prefix,
                'operator_id'   => (int) $external['operator_id'],
                'operator_nom'  => $external['operator_nom'],
                'operator_code' => $external['operator_code'],
                'is_internal'   => false,
            ];
        }

        throw new RuntimeException('Numéro invalide : préfixe non reconnu.');
    }

    public function isValidNumber(string $telephone): bool
    {
        try {
            $this->detect($telephone);

            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    protected function isInternalPrefix(string $prefix): bool
    {
        if (in_array($prefix, ['030', '039'], true)) {
            return true;
        }

        return $this->prefixModel->where('prefix', $prefix)->where('actif', 1)->first() !== null;
    }
}
