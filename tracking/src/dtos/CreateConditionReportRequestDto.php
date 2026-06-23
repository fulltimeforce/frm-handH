<?php

require_once __DIR__ . '/../bases/BaseDto.php';

class CreateConditionReportRequestDto extends BaseDto
{
    protected int $gf_entry_id;
    protected string $created_at;
    protected string $status;
    protected int $assigned_user_id;

    protected int $auction_id;
    protected ?string $lot_number;
    protected ?string $auction_name;
    protected int $lot_id;

    protected ?string $lot_name;
    protected ?string $lot_year;
    protected ?string $lot_make;
    protected ?string $lot_model;

    protected ?int $sold;
    protected ?float $sold_price;

    protected ?string $updated_at;

    public function __construct(
        int $gf_entry_id,
        string $created_at,
        string $status,
        int $assigned_user_id,

        int $auction_id,
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
}
