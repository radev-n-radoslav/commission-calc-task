<?php

declare(strict_types=1);

namespace RadevNRadoslav\CommissionTask\Service;

class Operation
{
    protected $index;
    protected $date;
    protected $userId;
    protected $userType;
    protected $type;
    protected $amount;
    protected $currency;
    protected $fee;

    public function __construct(array $operation, int $index)
    {
        $this->date = $operation[0];
        $this->userId = intval($operation[1]);
        $this->userType = $operation[2];
        $this->type = $operation[3];
        $this->amount = floatval($operation[4]);
        $this->currency = $operation[5];
    }

    /**
     * Get operation date.
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * Get operation userId.
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Get operation user type.
     */
    public function getUserType(): string
    {
        return $this->userType;
    }

    /**
     * Get operation type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get operation amount.
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Get operation currency.
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Set operation fee.
     */
    public function setFee(float $fee): void
    {
        $this->fee = $fee;
    }

    /**
     * Get operation fee.
     */
    public function getFee(): string
    {
        // Append a 0 if the decimal part of the fee is .1 for example.
        $fee = strval($this->fee);
        if (!$this->fee) {
            $fee = '0';
        }
        $numberSeparated = explode('.', $fee);
        if (array_key_exists(1, $numberSeparated)) {
            if (strlen($numberSeparated[1]) < 2) {
                $fee .= '0';
            }
        } else {
            $fee .= '.00';
        }

        return $fee;
    }
}
