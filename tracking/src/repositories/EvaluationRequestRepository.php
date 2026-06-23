<?php

use BcMath\Number;

require_once __DIR__ . '/../entities/EvaluationRequestEntity.php';
require_once __DIR__ . '/../dtos/CreateEvaluationRequestDto.php';
require_once __DIR__ . '/../dtos/UpdateEvaluationRequestDto.php';

class EvaluationRequestRepository
{

    private $wpdb;
    private $table = 'wp_hh_eval_requests';

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function insert(CreateEvaluationRequestDto $createEvaluationRequestDto)
    {
        $data = $createEvaluationRequestDto->getDataValues();
        $dataTypes = $createEvaluationRequestDto->getDataTypes();

        $inserted = $this->wpdb->insert($this->table, $data, $dataTypes);
        if (!$inserted) {
            return null; // O lanzar una excepción
        }

        $evaluation_request_id = $this->wpdb->insert_id;
        return $this->wpdb->find($evaluation_request_id);
    }

    public function getAll()
    {
        $query = $this->wpdb->get_results("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $query;
    }

    public function find(int $id)
    {
        $query = $this->wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE id = %d",
            $id
        );

        $row = $this->wpdb->get_row($query);

        if (!$row) {
            return null;
        }

        return new EvaluationRequestEntity(
            (int) $row->id,
            (int) $row->gf_entry_id,
            (string) $row->created_at,
            (string) $row->status,
            (int) $row->assigned_user_id,

            (int) $row->lot_id,
            $row->lot_name,
            $row->lot_year,
            $row->lot_make,
            $row->lot_model,

            $row->fit_for_auction !== null ? (int) $row->fit_for_auction : null,
            $row->lot_valuation !== null ? (float) $row->lot_valuation : null,
            $row->not_consigned_reason,
            $row->recommended_auction_id !== null ? (int) $row->recommended_auction_id : null,

            $row->sold !== null ? (int) $row->sold : null,
            $row->sold_price !== null ? (float) $row->sold_price : null,

            $row->updated_at
        );
    }

    public function update(UpdateEvaluationRequestDto $updateDto)
    {
        $id = (int) $updateDto->getId();

        $data = $updateDto->getDataValues();
        $dataTypes = $updateDto->getDataTypes();

        if (isset($data['id'])) {
            unset($data['id']);
        }

        if (empty($data)) {
            return $this->find($id);
        }

        if (!isset($data['updated_at'])) {
            $data['updated_at'] = current_time('mysql');
            $dataTypes[] = '%s';
        }

        $updated = $this->wpdb->update(
            $this->table,
            $data,
            ['id' => $id],
            $dataTypes,
            ['%d']
        );

        if ($updated === false) {
            return null;
        }

        return $this->find($id);
    }

    public function updateStatusIfCurrent(int $requestId, string $fromStatus, string $toStatus): bool
    {
        $updated = $this->wpdb->update(
            $this->table,
            [
                'status'     => $toStatus,
                'updated_at' => current_time('mysql'),
            ],
            [
                'id'     => $requestId,
                'status' => $fromStatus,
            ],
            ['%s', '%s'],
            ['%d', '%s']
        );

        return ($updated !== false && $updated > 0);
    }

    public function assignUserAndMoveStatusIfCurrent(int $requestId, int $assignedUserId, string $fromStatus, string $toStatus): bool
    {
        $updated = $this->wpdb->update(
            $this->table,
            [
                'assigned_user_id' => $assignedUserId,
                'status'           => $toStatus,
                'updated_at'       => current_time('mysql'),
            ],
            [
                'id'     => $requestId,
                'status' => $fromStatus,
            ],
            ['%d', '%s', '%s'],
            ['%d', '%s']
        );

        return ($updated !== false && $updated > 0);
    }

    public function findById(int $id)
    {
        $sql = $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d LIMIT 1", $id);
        $row = $this->wpdb->get_row($sql);
        return $row ?: null;
    }

    public function updateStatus(int $id, string $status, array $extra = [])
    {
        if ($id <= 0 || $status === '') return false;

        $data = [
            'status'     => $status,
            'updated_at' => current_time('mysql'),
        ];

        $format = ['%s', '%s'];

        if (array_key_exists('fit_for_auction', $extra)) {
            $data['fit_for_auction'] = (int) $extra['fit_for_auction'];
            $format[] = '%d';
        }

        if (array_key_exists('not_consigned_reason', $extra)) {
            // puede ser string o null
            $data['not_consigned_reason'] = $extra['not_consigned_reason'];
            $format[] = '%s';
        }

        return $this->updateById($id, $data, $format);
    }

    public function updateFields(int $id, array $data): bool
    {
        if (empty($data)) return false;

        $data['updated_at'] = current_time('mysql');

        $result = $this->wpdb->update(
            $this->table,
            $data,
            ['id' => $id]
        );

        return ($result !== false);
    }

    public function updateById(int $id, array $data, array $format): bool
    {
        if ($id <= 0) return false;
        if (empty($data)) return false;

        $result = $this->wpdb->update(
            $this->table,
            $data,
            ['id' => $id],
            $format,
            ['%d']
        );

        return $result !== false; // false = SQL error
    }

    public function updateConsignmentDecision(int $id, string $status, int $fitForAuction, $reasonNullable): bool
    {
        $updated = $this->wpdb->update(
            $this->table,
            [
                'status'              => $status,
                'fit_for_auction'     => $fitForAuction,
                'not_consigned_reason' => $reasonNullable, // null si accepted
                'updated_at'          => current_time('mysql'),
            ],
            [
                'id' => $id,
            ],
            [
                '%s',
                '%d',
                '%s', // si es null, wpdb lo convierte a NULL
                '%s',
            ],
            [
                '%d'
            ]
        );

        return ($updated !== false && $updated > 0);
    }

    public function updateConsignmentYes(int $id, string $status, $lotValuation, int $recommendedAuctionId): bool
    {
        $updated = $this->wpdb->update(
            $this->table,
            [
                'status'                 => $status,
                'fit_for_auction'        => 1,
                'lot_valuation'          => $lotValuation, // decimal/text ok
                'recommended_auction_id' => $recommendedAuctionId,
                'not_consigned_reason'   => null,
                'updated_at'             => current_time('mysql'),
            ],
            ['id' => $id],
            ['%s', '%d', '%s', '%d', '%s', '%s'],
            ['%d']
        );

        return $updated !== false;
    }

    public function updateConsignmentNo(int $id, string $status, ?string $reason): bool
    {
        $updated = $this->wpdb->update(
            $this->table,
            [
                'status'               => $status,
                'fit_for_auction'        => 0,
                'not_consigned_reason' => $reason,
                'recommended_auction_id' => null,
                'lot_valuation'        => null,
                'updated_at'           => current_time('mysql'),
            ],
            ['id' => $id],
            ['%s', '%d', '%s', '%s', '%s', '%s'],
            ['%d']
        );

        return $updated !== false;
    }

    public function existsByLotId(int $lotId): bool
    {
        if ($lotId <= 0) return false;

        $sql = "SELECT id FROM {$this->table} WHERE lot_id = %d LIMIT 1";
        $id = $this->wpdb->get_var($this->wpdb->prepare($sql, $lotId));

        return !empty($id);
    }

    public function updateByLotId(int $lotId, array $data): int
    {
        if ($lotId <= 0) return 0;

        $allowed = ['lot_year', 'lot_make', 'lot_model', 'sold', 'sold_price'];
        $clean = [];

        foreach ($allowed as $k) {
            if (array_key_exists($k, $data)) {
                $clean[$k] = $data[$k];
            }
        }

        if (empty($clean)) return 0;

        // updated_at si existe
        $clean['updated_at'] = current_time('mysql');

        $setParts = [];
        $values = [];

        foreach ($clean as $k => $v) {
            $setParts[] = "{$k} = " . ($k === 'sold' ? "%d" : "%s");
            $values[] = $v;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE lot_id = %d";
        $values[] = $lotId;

        $prepared = $this->wpdb->prepare($sql, $values);
        $result = $this->wpdb->query($prepared);

        return is_numeric($result) ? (int) $result : 0;
    }
}
