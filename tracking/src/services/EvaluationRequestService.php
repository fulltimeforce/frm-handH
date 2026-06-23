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
        ?string $lot_name = null,
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
        $fit_for_auction = null,
        $lot_valuation = null,
        $not_consigned_reason = null,
        $recommended_auction_id = null,
        $sold = null,
        $sold_price = null
    ) {
        $dto = new UpdateEvaluationRequestDto(
            $id,
            $status,
            $assigned_user_id,
            null, // lot_id
            null, // lot_name
            null, // lot_year
            null, // lot_make
            null, // lot_model
            $fit_for_auction,
            $lot_valuation,
            $not_consigned_reason,
            $recommended_auction_id,
            $sold,
            $sold_price
        );

        return $this->repository->update($dto);
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function getById(int $id)
    {
        return $this->repository->findById($id);
    }

    public function updateStatus(int $id, string $status, array $extra = []): bool
    {
        // Validación básica
        $allowedStatuses = [
            'new',
            'client_contacted',
            'assigned',
            'under_review',
            'consignment_confirmed',
            'not_consigned',
            'in_progress',
            'finalised',
        ];

        if ($id <= 0) return false;
        if (!in_array($status, $allowedStatuses, true)) return false;

        // Si "not_consigned", debería venir reason (opcionalmente lo fuerzas aquí)
        if ($status === 'not_consigned') {
            $reason = isset($extra['not_consigned_reason']) ? trim((string)$extra['not_consigned_reason']) : '';
            if ($reason === '') return false;

            // Asegura fit_for_auction = 0
            $extra['fit_for_auction'] = 0;
        }

        // Si "consignment_confirmed", asegura fit_for_auction = 1 y limpia reason
        if ($status === 'consignment_confirmed') {
            $extra['fit_for_auction'] = 1;
            $extra['not_consigned_reason'] = null;
        }

        return $this->repository->updateStatus($id, $status, $extra);
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

    public function setConsignmentDecision(int $requestId, bool $accepted, string $reason, int $actorUserId, bool $isAdminOrSalesManager): bool
    {
        $row = $this->repository->findById($requestId);
        if (!$row) return false;

        // Solo desde Under Review
        if ((string)$row->status !== 'under_review') return false;

        $assignedUserId = isset($row->assigned_user_id) ? (int)$row->assigned_user_id : 0;

        // Permisos: o admin/sales manager o el assigned
        if (!$isAdminOrSalesManager && ($assignedUserId <= 0 || $assignedUserId !== $actorUserId)) {
            return false;
        }

        $newStatus = $accepted ? 'consignment_confirmed' : 'not_consigned';
        $fitForAuction = $accepted ? 1 : 0;

        $finalReason = $accepted ? null : trim($reason);

        return $this->repository->updateConsignmentDecision($requestId, $newStatus, $fitForAuction, $finalReason);
    }

    public function setConsignmentResult(
        int $requestId,
        bool $accepted,
        ?string $reason,
        $lotValuation = null,
        ?int $recommendedAuctionId = null
    ): bool {
        $row = $this->repository->findById($requestId);
        if (!$row) {
            throw new \RuntimeException('Request not found.');
        }

        if ($accepted) {
            return $this->repository->updateConsignmentYes(
                $requestId,
                'consignment_confirmed',
                $lotValuation,
                (int)$recommendedAuctionId
            );
        }

        return $this->repository->updateConsignmentNo(
            $requestId,
            'not_consigned',
            $reason
        );
    }

    public function finaliseWithVehicle(int $requestId, int $vehicleId, int $currentUserId): bool
    {
        $req = $this->repository->findById($requestId);
        if (!$req) {
            throw new Exception('Evaluation request not found.');
        }

        // (Opcional) valida que SOLO assigned_user o admin/sales manager pueda finalizar
        // aquí puedes replicar tus reglas actuales si quieres.

        // ACF fields
        $year = get_field('year_vehicle', $vehicleId);
        $make = get_field('artist_maker_brand', $vehicleId); // post object
        $model = get_field('model_vehicle', $vehicleId);     // post object
        $status = get_field('status', $vehicleId);           // select
        $soldPrice = get_field('sold_price', $vehicleId);

        // Normalizar "make" a string
        $makeTitle = null;
        if (is_object($make) && isset($make->ID)) {
            $makeTitle = get_the_title((int) $make->ID);
        } elseif (is_numeric($make)) {
            $makeTitle = get_the_title((int) $make);
        } elseif (is_string($make) && $make !== '') {
            $makeTitle = $make;
        }

        // Normalizar "model" a string
        $modelTitle = null;
        if (is_object($model) && isset($model->ID)) {
            $modelTitle = get_the_title((int) $model->ID);
        } elseif (is_numeric($model)) {
            $modelTitle = get_the_title((int) $model);
        } elseif (is_string($model) && $model !== '') {
            $modelTitle = $model;
        }

        // Sold?
        $isSold = 0;
        if (is_string($status) && strtolower(trim($status)) === 'sold') {
            $isSold = 1;
        }

        $data = [
            'status'      => 'finalised',
            'lot_id'      => $vehicleId,
            'lot_year'    => (is_string($year) || is_numeric($year)) ? (string) $year : null,
            'lot_make'    => $makeTitle,
            'lot_model'   => $modelTitle,
            'sold'        => $isSold,
            'sold_price'  => (is_string($soldPrice) || is_numeric($soldPrice)) ? (string) $soldPrice : null,
        ];

        // Si algún campo viene vacío, dejamos null (como pediste)
        foreach ($data as $k => $v) {
            if ($v === '' || $v === false) $data[$k] = null;
        }

        return $this->repository->updateFields($requestId, $data);
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

        // ====== Update en tu tabla ======
        return $this->repository->updateByLotId($vehicleId, [
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
