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
            $row->lot_year,
            $row->lot_make,
            $row->lot_model,

            $row->fit_for_auction !== null ? (int) $row->fit_for_auction : null,
            $row->lot_valuation !== null ? (float) $row->lot_valuation : null,
            $row->not_consigned_reason,
            $row->recommended_auction_id !== null ? (int) $row->recommended_auction_id : null,

            $row->consigned_id !== null ? (int) $row->consigned_id : null,
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
}
