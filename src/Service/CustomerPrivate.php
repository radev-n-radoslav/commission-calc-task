<?php

declare(strict_types=1);

namespace RadevNRadoslav\CommissionTask\Service;

use Carbon\Carbon;

class CustomerPrivate extends CustomerBase
{
    protected $privateWithdrawalPercentageMultiplier = 0.3;

    /**
     * Get a weeks start and end dates from a given date.
     */
    protected function getWeekStartEndDates(string $date, $startDayIndex = Carbon::MONDAY): array
    {
        $date = Carbon::parse($date);
        $date::setWeekStartsAt($startDayIndex);

        $from = $date->startOfWeek();
        $date::setWeekEndsAt($from->copy()->addDays(6)->dayOfWeek);
        $to = $date->copy()->endOfWeek();

        return [
            'start' => $from,
            'end' => $to,
        ];
    }

    /**
     * Check if two dates are in the same week.
     */
    protected function checkTwoDatesInSameWeek(string $date1, string $date2): bool
    {
        $week = $this->getWeekStartEndDates($date1);

        // Check if second date is outside the week of the first one
        if (Carbon::parse($week['start'])->greaterThan(Carbon::parse($date2)) || Carbon::parse($week['end'])->lessThan(Carbon::parse($date2))) {
            return false;
        }

        return true;
    }

    /**
     * Get indexes of all withdraw operations for customer.
     */
    protected function getWithdrawIndexes(): array
    {
        $withdrawalIndexes = [];
        foreach ($this->operations as $key => $operation) {
            if ($operation->getType() === 'withdraw') {
                $withdrawalIndexes[] = $key;
            }
        }

        return $withdrawalIndexes;
    }

    /**
     * Get all operations grouped with otherones in the same week.
     */
    protected function groupWithdrawsByWeek(array $withdrawalIndexes): array
    {
        $withdrawalsByWeek = [];

        foreach ($withdrawalIndexes as $key => &$index) {
            $currentWeekWithdrawals = [];
            $currentWeekWithdrawals[] = $index;
            unset($withdrawalIndexes[$key]);

            foreach ($withdrawalIndexes as $subKey => &$subIndex) {
                if ($this->checkTwoDatesInSameWeek($this->operations[$index]->getDate(), $this->operations[$subIndex]->getDate())) {
                    $currentWeekWithdrawals[] = $subIndex;
                    unset($withdrawalIndexes[$subKey]);
                }
            }

            $withdrawalsByWeek[] = $currentWeekWithdrawals;
        }

        return $withdrawalsByWeek;
    }

    /**
     * Calculate and set fee for given operation.
     */
    public function calcSetFee($operationIndex, $amount): void
    {
        $this->operations[$operationIndex]
            ->setFee($this->roundFee(
                $amount * ($this->privateWithdrawalPercentageMultiplier / 100)
            ));
    }

    /**
     * Calculate withdrawal fees for user.
     */
    protected function calcWithdrawalBillableAmounts(): void
    {
        // Get withdrawals by week
        $withdrawalsByWeek = $this->groupWithdrawsByWeek($this->getWithdrawIndexes());

        foreach ($withdrawalsByWeek as $currentWeekOperations) {
            // Go trough the operations in a given week. Check if they need to be assigned a fee and assign proper fees to every operation
            $euroSumOfAmounts = 0;
            $operationsInWeek = 0;
            foreach ($currentWeekOperations as $key => $operationIndex) {
                $currencyConverter = new CurrencyConverter();
                $convertedAmount = $currencyConverter->convert(
                    $this->operations[$operationIndex]->getCurrency(),
                    'EUR',
                    $this->operations[$operationIndex]->getAmount()
                );
                ++$operationsInWeek;

                if ($operationsInWeek > 3 || $euroSumOfAmounts > 1000) {
                    $this->calcSetFee(
                        $operationIndex,
                        $this->operations[$operationIndex]->getAmount()
                    );
                    continue;
                }

                // If first operation to go over 1000 EUR calc fee only for nesessary amount
                if (($euroSumOfAmounts + $convertedAmount) > 1000 && $euroSumOfAmounts <= 1000) {
                    $euroSumOfAmounts += $convertedAmount;
                    $billableAmount = $euroSumOfAmounts - 1000;
                    $billableAmount = $currencyConverter->convert(
                        'EUR',
                        $this->operations[$operationIndex]->getCurrency(),
                        $billableAmount
                    );
                    $this->calcSetFee(
                        $operationIndex,
                        $billableAmount
                    );
                    continue;
                }

                $euroSumOfAmounts += $convertedAmount;
            }
        }
    }

    /**
     * Calculate fees for current customer operations.
     */
    public function calculate(): void
    {
        foreach ($this->operations as $key => $operation) {
            if ($operation->getType() === 'deposit') {
                $this->operations[$key]->setFee($this->calcDepositFee($operation));
            }
        }
        $this->calcWithdrawalBillableAmounts();
    }
}
