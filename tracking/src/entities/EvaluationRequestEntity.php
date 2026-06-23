<?php

class EvaluationRequestEntity
{
    private int $id;
    private int $gf_entry_id;
    private string $created_at;
    private string $status;
    private int $assigned_user_id;

    private int $lot_id;
    private ?string $lot_name;
    private ?string $lot_year;
    private ?string $lot_make;
    private ?string $lot_model;

    private ?int $fit_for_auction;
    private ?float $lot_valuation;
    private ?string $not_consigned_reason;
    private ?int $recommended_auction_id;

    private ?int $sold;
    private ?float $sold_price;

    private ?string $updated_at;

    public function __construct(
        int $id,
        int $gf_entry_id,
        string $created_at,
        string $status = 'new',
        int $assigned_user_id = 0,

        int $lot_id = 0,
        ?string $lot_name = null,
        ?string $lot_year = null,
        ?string $lot_make = null,
        ?string $lot_model = null,

        ?int $fit_for_auction = null,
        ?float $lot_valuation = null,
        ?string $not_consigned_reason = null,
        ?int $recommended_auction_id = null,
        ?int $sold = null,
        ?float $sold_price = null,
        ?string $updated_at = null
    ) {
        $this->id = $id;
        $this->gf_entry_id = $gf_entry_id;
        $this->created_at = $created_at;
        $this->status = $status;
        $this->assigned_user_id = $assigned_user_id;

        $this->lot_id = $lot_id;
        $this->lot_name = $lot_name;
        $this->lot_year = $lot_year;
        $this->lot_make = $lot_make;
        $this->lot_model = $lot_model;

        $this->fit_for_auction = $fit_for_auction;
        $this->lot_valuation = $lot_valuation;
        $this->not_consigned_reason = $not_consigned_reason;
        $this->recommended_auction_id = $recommended_auction_id;

        $this->sold = $sold;
        $this->sold_price = $sold_price;

        $this->updated_at = $updated_at;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGfEntryId(): int
    {
        return $this->gf_entry_id;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAssignedUserId(): int
    {
        return $this->assigned_user_id;
    }

    public function getLotId(): int
    {
        return $this->lot_id;
    }

    public function getLotName(): ?string
    {
        return $this->lot_name;
    }

    public function getLotYear(): ?string
    {
        return $this->lot_year;
    }

    public function getLotMake(): ?string
    {
        return $this->lot_make;
    }

    public function getLotModel(): ?string
    {
        return $this->lot_model;
    }

    public function getFitForAuction(): ?int
    {
        return $this->fit_for_auction;
    }

    public function getLotValuation(): ?float
    {
        return $this->lot_valuation;
    }

    public function getNotConsignedReason(): ?string
    {
        return $this->not_consigned_reason;
    }

    public function getRecommendedAuctionId(): ?int
    {
        return $this->recommended_auction_id;
    }

    public function getSold(): ?int
    {
        return $this->sold;
    }

    public function getSoldPrice(): ?float
    {
        return $this->sold_price;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setAssignedUserId(int $user_id): void
    {
        $this->assigned_user_id = $user_id;
    }

    public function setLotId(int $lot_id): void
    {
        $this->lot_id = $lot_id;
    }

    public function setLotName(?string $name): void
    {
        $this->lot_name = $name;
    }

    public function setLotYear(?string $year): void
    {
        $this->lot_year = $year;
    }

    public function setLotMake(?string $make): void
    {
        $this->lot_make = $make;
    }

    public function setLotModel(?string $model): void
    {
        $this->lot_model = $model;
    }

    public function setFitForAuction(?int $fit): void
    {
        $this->fit_for_auction = $fit;
    }

    public function setLotValuation(?float $value): void
    {
        $this->lot_valuation = $value;
    }

    public function setNotConsignedReason(?string $reason): void
    {
        $this->not_consigned_reason = $reason;
    }

    public function setRecommendedAuctionId(?int $auction_id): void
    {
        $this->recommended_auction_id = $auction_id;
    }

    public function setSold(?int $sold): void
    {
        $this->sold = $sold;
    }

    public function setSoldPrice(?float $price): void
    {
        $this->sold_price = $price;
    }

    public function setUpdatedAt(string $updated_at): void
    {
        $this->updated_at = $updated_at;
    }
}
