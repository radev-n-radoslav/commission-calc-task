<?php

declare(strict_types=1);

namespace RadevNRadoslav\CommissionTask\Service;

class CustomerBusiness extends CustomerBase
{
    protected $businessWithdrawalPercentageMultiplier = 0.5;

    /**
     * Calc withdrawal fee for a business client operation.
     */
    protected function calcWithdrawalFee(object $operation): float
    {
        return $this->roundFee($operation->getAmount() * ($this->businessWithdrawalPercentageMultiplier / 100));
    }

    /**
     * Calculate fees for current customer operations.
     */
    public function calculate(): void
    {
        foreach ($this->operations as $key => $operation) {
            if ($operation->getType() === 'deposit') {
                $this->operations[$key]->setFee($this->calcDepositFee($operation));
            } else {
                $this->operations[$key]->setFee($this->calcWithdrawalFee($operation));
            }
        }
    }
}
