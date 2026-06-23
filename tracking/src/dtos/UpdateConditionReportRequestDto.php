<?php

require_once __DIR__ . '/../bases/BaseDto.php';

class UpdateConditionReportRequestDto extends BaseDto
{
    private int $id;

    protected ?string $status;
    protected ?int $assigned_user_id;

    protected ?int $auction_id;
    protected ?string $lot_number;
    protected ?string $auction_name;
    protected ?int $lot_id;

    protected ?string $lot_name;
    protected ?string $lot_year;
    protected ?string $lot_make;
    protected ?string $lot_model;

    protected ?int $sold;
    protected ?float $sold_price;

    protected ?string $updated_at;

    public function __construct(
        int $id,
        $status = null,
        $assigned_user_id = null,

        $auction_id = null,
        $lot_number = null,
        $auction_name = null,
        $lot_id = null,

        $lot_name = null,
        $lot_year = null,
        $lot_make = null,
        $lot_model = null,

        $sold = null,
        $sold_price = null,

        $updated_at = null
    ) {
        $this->id = $id;

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

    public function getId()
    {
        return $this->id;
    }
}
