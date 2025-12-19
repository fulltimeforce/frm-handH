<?php

require_once __DIR__ . '/../repositories/EvaluationRequestRepository.php';
require_once __DIR__ . '/../dtos/CreateEvaluationRequestDto.php';
require_once __DIR__ . '/../dtos/UpdateEvaluationRequestDto.php';

class EvaluationRequestService
{
    private $repository;

    public function __construct()
    {
        $this->repository = new EvaluationRequestRepository();
    }

    /* ===================== CREATE ===================== */

    public function create(
        int $gf_entry_id,
        string $created_at,
        int $assigned_user_id,
        int $lot_id,
        ?string $lot_year = null,
        ?string $lot_make = null,
        ?string $lot_model = null
    ) {
        $dto = new CreateEvaluationRequestDto(
            $gf_entry_id,
            $created_at,
            'new',
            $assigned_user_id,
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
        $fit_for_auction = null,
        $lot_valuation = null,
        $not_consigned_reason = null,
        $recommended_auction_id = null,
        $consigned_id = null,
        $sold = null,
        $sold_price = null
    ) {
        $dto = new UpdateEvaluationRequestDto(
            $id,
            $status,
            $assigned_user_id,
            null, // lot_id
            null, // lot_year
            null, // lot_make
            null, // lot_model
            $fit_for_auction,
            $lot_valuation,
            $not_consigned_reason,
            $recommended_auction_id,
            $consigned_id,
            $sold,
            $sold_price
        );

        return $this->repository->update($dto);
    }

    /* ===================== BUSINESS ACTIONS ===================== */

    public function assignToUser(int $id, int $user_id)
    {
        $dto = new UpdateEvaluationRequestDto(
            $id,
            null,
            $user_id
        );

        return $this->repository->update($dto);
    }

    public function changeStatus(int $id, string $status)
    {
        $dto = new UpdateEvaluationRequestDto(
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
}
