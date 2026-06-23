<?php

class ConditionReportRequestEntity
{
    private int $id;
    private int $gf_entry_id;
    private string $created_at;
    private string $status;
    private int $assigned_user_id;

    private int $auction_id;
    private ?string $lot_number;
    private ?string $auction_name;
    private int $lot_id;

    private ?string $lot_name;
    private ?string $lot_year;
    private ?string $lot_make;
    private ?string $lot_model;

    private ?int $sold;
    private ?float $sold_price;

    private ?string $updated_at;

    public function __construct(
        int $id,
        int $gf_entry_id,
        string $created_at,
        string $status = 'new',
        int $assigned_user_id = 0,

        int $auction_id = 0,
        ?string $lot_number = null,
        ?string $auction_name = null,
        int $lot_id = 0,

        ?string $lot_name = null,
        ?string $lot_year = null,
        ?string $lot_make = null,
        ?string $lot_model = null,

        ?int $sold = null,
        ?float $sold_price = null,

        ?string $updated_at = null
    ) {
        $this->id = $id;
        $this->gf_entry_id = $gf_entry_id;
        $this->created_at = $created_at;
        $this->status = $status;
        $this->assigned_user_id = $assigned_user_id;

        $this->auction_id = $auction_id;
        $this->lot_number = $lot_number;
        $this->auction_name = $auction_name;
        $this->lot_id = $lot_id;

        $this->lot_name = $lot_name;
        $this->lot_year = $lot_year;
        $this->lot_make = $lot_make;
        $this->lot_model = $lot_model;

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

    public function getAuctionId(): int
    {
        return $this->auction_id;
    }

    public function getLotNumber(): ?string
    {
        return $this->lot_number;
    }

    public function getAuctionName(): ?string
    {
        return $this->auction_name;
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

    public function setAuctionId(int $auction_id): void
    {
        $this->auction_id = $auction_id;
    }

    public function setLotNumber(?string $lot_number): void
    {
        $this->lot_number = $lot_number;
    }

    public function setAuctionName(?string $auction_name): void
    {
        $this->auction_name = $auction_name;
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