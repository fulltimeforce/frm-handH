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
            null, // lot_year
            null, // lot_make
            null, // lot_model
            $sold,
            $sold_price
        );

        return $this->repository->update($dto);
    }

    /* ===================== BUSINESS ACTIONS ===================== */

    public function assignToUser(int $id, int $user_id)
    {
        $dto = new UpdateConditionReportRequestDto(
            $id,
            null,
            $user_id
        );

        return $this->repository->update($dto);
    }

    public function changeStatus(int $id, string $status)
    {
        $dto = new UpdateConditionReportRequestDto(
            $id,
            $status
        );

        return $this->repository->update($dto);
    }

    /* ===================== READ ===================== */

    public function get(int $id)
    {
        return $this->repository->find($id);
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
}
