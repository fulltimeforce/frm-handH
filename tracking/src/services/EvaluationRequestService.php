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

    public function passToClientContacted(int $requestId): bool
    {
        if ($requestId <= 0) return false;

        // Solo NEW -> CLIENT_CONTACTED
        return $this->repository->updateStatusIfCurrent($requestId, 'new', 'client_contacted');
    }

    public function passToAssigned(int $requestId, int $assignedUserId): bool
    {
        if ($requestId <= 0 || $assignedUserId <= 0) return false;

        // Validar usuario
        $u = get_user_by('id', $assignedUserId);
        if (!$u) return false;

        // Solo CLIENT_CONTACTED -> ASSIGNED
        return $this->repository->assignUserAndMoveStatusIfCurrent($requestId, $assignedUserId, 'client_contacted', 'assigned');
    }

    public function passToUnderReview(int $requestId): bool
    {
        // Ideal: delega al repo, y fuerza transición desde "assigned"
        return $this->repository->updateStatusIfCurrent($requestId, 'assigned', 'under_review');
    }
}
