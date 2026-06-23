<?php

require_once __DIR__ . '/../bases/BaseDto.php';

class CreateEvaluationRequestDto extends BaseDto
{
    protected int $gf_entry_id;
    protected string $created_at;
    protected string $status;
    protected int $assigned_user_id;

    protected int $lot_id;
    protected ?string $lot_name;
    protected ?string $lot_year;
    protected ?string $lot_make;
    protected ?string $lot_model;

    protected ?int $fit_for_auction;
    protected ?float $lot_valuation;
    protected ?string $not_consigned_reason;
    protected ?int $recommended_auction_id;

    protected ?int $sold;
    protected ?float $sold_price;

    protected ?string $updated_at;

    public function __construct(
        int $gf_entry_id,
        string $created_at,
        string $status,
        int $assigned_user_id,
        int $lot_id,
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
}
