<?php

declare(strict_types=1);

namespace RadevNRadoslav\CommissionTask\Service;

class CustomerBase
{
    protected $userId;
    protected $depositPercentageMultiplier = 0.03;
    protected $operations;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
        $this->operations = [];
    }

    /**
     * Get operation by reference for further manipulation.
     */
    public function addOperation(object &$operation): void
    {
        $this->operations[] = $operation;
    }

    /**
     * Round 2nd decimal place of a fee to the nearest largest number, but only if
     * decimal remainder is 3 digits or longer.
     */
    protected function roundFee(float $fee): float
    {
        $feeWholeVal = intval($fee);
        $feeDecimalVal = $fee - $feeWholeVal;
        $roundedDecimalVal = ceil($feeDecimalVal * 100) / 100;

        // Sum the intval and rounded decimal val of the fee
        return $feeWholeVal + $roundedDecimalVal;
    }

    /**
     * Calc deposit fee for a given operation.
     */
    protected function calcDepositFee(object $operation): float
    {
        return $this->roundFee($operation->getAmount() * ($this->depositPercentageMultiplier / 100));
    }
}
