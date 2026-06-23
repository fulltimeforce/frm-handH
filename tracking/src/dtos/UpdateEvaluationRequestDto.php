<?php

require_once __DIR__ . '/../bases/BaseDto.php';

class UpdateEvaluationRequestDto extends BaseDto
{
    private int $id;

    protected ?string $status;
    protected ?int $assigned_user_id;

    protected ?int $lot_id;
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
        int $id,
        $status = null,
        $assigned_user_id = null,

        $lot_id = null,
        $lot_name = null,
        $lot_year = null,
        $lot_make = null,
        $lot_model = null,

        $fit_for_auction = null,
        $lot_valuation = null,
        $not_consigned_reason = null,
        $recommended_auction_id = null,

        $sold = null,
        $sold_price = null,

        $updated_at = null
    ) {
        $this->id = $id;

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

    public function getId()
    {
        return $this->id;
    }
}
