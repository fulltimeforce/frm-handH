<?php

require_once __DIR__ . '/../repositories/ConditionReportRequestRepository.php';
require_once __DIR__ . '/../dtos/CreateConditionReportRequestDto.php';
require_once __DIR__ . '/../dtos/UpdateConditionReportRequestDto.php';

class ConditionReportRequestService
{
    private $repository;

    public function __construct()
    {
        $this->repository = new ConditionReportRequestRepository();
    }

    /* ===================== CREATE ===================== */

    public function create(
        int $gf_entry_id,
        string $created_at,
        int $assigned_user_id,
        int $auction_id,
        int $lot_id,
        ?string $lot_name = null,
        ?string $lot_number = null,
        ?string $auction_name = null,
        ?string $lot_year = null,
        ?string $lot_make = null,
        ?string $lot_model = null
    ) {
        $dto = new CreateConditionReportRequestDto(
            $gf_entry_id,
            $created_at,
            'new',
            $assigned_user_id,
            $auction_id,
            $lot_number,
            $auction_name,
            $lot_id,
            $lot_name,
            $lot_year,
            $lot_make,
            $lot_model
        );

        return $this->repository->insert($dto);
    }

    /* ===================== UPDATE ===================== */

    public function update(
        int $id,
        $status = null,
        $assigned_user_id = null,
        $sold = null,
        $sold_price = null
    ) {
        $dto = new UpdateConditionReportRequestDto(
            $id,
            $status,
            $assigned_user_id,
            null, // auction_id
            null, // lot_number
            null, // auction_name
            null, // lot_id
            null, // lot_name
            null, // lot_year
            null, // lot_make
            null, // lot_model
            $sold,
            $sold_price
        );

        return $this->repository->update($dto);
    }

    /* ===================== READ ===================== */

    public function get(int $id)
    {
        return $this->repository->find($id);
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function passToInProgress(int $request_id, int $assigned_user_id): bool
    {
        // regla: si no hay usuario, NO cambia nada
        if ($request_id <= 0 || $assigned_user_id <= 0) {
            return false;
        }

        // regla: usuario debe existir
        $u = get_user_by('id', $assigned_user_id);
        if (!$u) {
            return false;
        }

        return $this->repository->moveToInProgress($request_id, $assigned_user_id);
    }

    public function passToCompleted(int $request_id): bool
    {
        return $this->repository->passToCompleted($request_id);
    }

    public function syncEvalRequestFromVehicle(int $vehicleId): int
    {
        if ($vehicleId <= 0) return 0;

        // Si no existe ningún eval_request con este lot_id, no hacemos nada
        if (!$this->repository->existsByLotId($vehicleId)) {
            return 0;
        }

        // ====== Leer ACF ======

        // Year (text) -> year_vehicle
        $lot_year = get_field('year_vehicle', $vehicleId);
        $lot_year = is_string($lot_year) ? trim($lot_year) : (string) $lot_year;
        if ($lot_year === '') $lot_year = null;

        // Make (post object) -> artist_maker_brand
        $make = get_field('artist_maker_brand', $vehicleId);
        $lot_make = $this->getCptTitle($make);

        // Model (post object) -> model_vehicle
        $model = get_field('model_vehicle', $vehicleId);
        $lot_model = $this->getCptTitle($model);

        // Status (select) -> status
        $status = get_field('status', $vehicleId);
        if (is_array($status)) $status = $status[0] ?? '';
        $status = is_string($status) ? trim($status) : (string) $status;

        // Sold boolean (1/0)
        $sold = (strcasecmp($status, 'Sold') === 0) ? 1 : 0;

        // Sold price (text) -> sold_price
        $sold_price = get_field('sold_price', $vehicleId);
        $sold_price = is_string($sold_price) ? trim($sold_price) : (string) $sold_price;
        if ($sold_price === '') $sold_price = null;

        $lot_name = $vehicleId ? get_the_title($vehicleId) : null;

        // ====== Update en tu tabla ======
        return $this->repository->updateByLotId($vehicleId, [
            'lot_name'   => $lot_name,
            'lot_year'   => $lot_year,
            'lot_make'   => $lot_make,
            'lot_model'  => $lot_model,
            'sold'       => $sold,
            'sold_price' => $sold_price,
        ]);
    }

    private function getCptTitle($model): ?string
    {
        if ($model instanceof WP_Post) {
            $t = get_the_title($model->ID);
            return $t ? (string) $t : null;
        }

        if (is_numeric($model)) {
            $t = get_the_title((int) $model);
            return $t ? (string) $t : null;
        }

        if (is_string($model)) {
            $model = trim($model);
            return $model !== '' ? $model : null;
        }

        return null;
    }
}
