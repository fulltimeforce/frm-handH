<?php

require_once __DIR__ . '/../entities/ConditionReportRequestEntity.php';
require_once __DIR__ . '/../dtos/CreateConditionReportRequestDto.php';
require_once __DIR__ . '/../dtos/UpdateConditionReportRequestDto.php';

class ConditionReportRequestRepository
{
    private $wpdb;
    private $table = 'wp_hh_condition_requests';

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function insert(CreateConditionReportRequestDto $createConditionReportRequestDto)
    {
        $data = $createConditionReportRequestDto->getDataValues();
        $dataTypes = $createConditionReportRequestDto->getDataTypes();

        $inserted = $this->wpdb->insert($this->table, $data, $dataTypes);
        if (!$inserted) {
            return null;
        }

        $condition_request_id = (int) $this->wpdb->insert_id;

        return $this->find($condition_request_id);
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

        return new ConditionReportRequestEntity(
            (int) $row->id,
            (int) $row->gf_entry_id,
            (string) $row->created_at,
            (string) $row->status,
            (int) $row->assigned_user_id,

            (int) $row->auction_id,
            $row->lot_number,
            $row->auction_name,
            (int) $row->lot_id,

            $row->lot_name,
            $row->lot_year,
            $row->lot_make,
            $row->lot_model,

            $row->sold !== null ? (int) $row->sold : null,
            $row->sold_price !== null ? (float) $row->sold_price : null,

            $row->updated_at
        );
    }

    public function update(UpdateConditionReportRequestDto $updateDto)
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

    public function moveToInProgress(int $id, int $assigned_user_id): bool
    {
        // Solo mover si está en NEW
        $updated = $this->wpdb->update(
            $this->table,
            [
                'assigned_user_id' => $assigned_user_id,
                'status'           => 'in_progress',
                'updated_at'       => current_time('mysql'),
            ],
            [
                'id'     => $id,
                'status' => 'new',
            ],
            ['%d', '%s', '%s'],
            ['%d', '%s']
        );

        if ($updated === false) {
            return false;
        }

        // $updated === 0 => no cambió (no estaba en new, o id inválido)
        return $updated > 0;
    }

    public function passToCompleted(int $request_id): bool
    {
        // solo si está en in_progress
        $updated = $this->wpdb->update(
            $this->table,
            [
                'status'     => 'completed',
                'updated_at' => current_time('mysql'),
            ],
            [
                'id'     => $request_id,
                'status' => 'in_progress',
            ],
            ['%s', '%s'],
            ['%d', '%s']
        );

        if ($updated === false) return false;
        return $updated > 0;
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
