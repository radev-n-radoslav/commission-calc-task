<?php

declare(strict_types=1);

namespace RadevNRadoslav\CommissionTask\Service;

class CommissionFeeCalculator
{
    protected $operations;
    protected $customers;

    /**
     * If an array of operations is supplied on object initialization, load them to the object. Else just initialize an empty array in it.
     *
     * @return void
     */
    public function __construct(array $operations = [])
    {
        $this->operations = $this->normalizeOperations($operations);
        $this->customers = [];
    }

    /**
     * Transform operations from arrays to objects.
     */
    protected function normalizeOperations(array $operations): array
    {
        $normalizedOperations = [];

        foreach ($operations as $index => $operation) {
            $normalizedOperations[] = new Operation($operation, $index);
        }

        return $normalizedOperations;
    }

    /**
     * Append operations to the objects operations array.
     */
    public function addOperations(array $operations): void
    {
        $this->operations = array_merge($this->operations, $this->normalizeOperations($operations));
    }

    /**
     * Get currently loaded operations.
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * Return calculated fees.
     */
    protected function getResults(): array
    {
        $results = [];
        foreach ($this->operations as $operation) {
            $results[] = $operation->getFee();
        }

        return $results;
    }

    /**
     * Calculate the commissions for all supplied operations.
     */
    public function calculate(): array
    {
        foreach ($this->operations as $index => $operation) {
            $userId = $operation->getUserId();
            if (!array_key_exists($userId, $this->customers)) {
                $this->customers[$userId] = ($operation->getUserType() === 'private') ? new CustomerPrivate($userId) : new CustomerBusiness($userId);
            }
            $this->customers[$operation->getUserId()]->addOperation($operation);
        }

        foreach ($this->customers as $customer) {
            $customer->calculate();
        }

        return $this->getResults();
    }
}
