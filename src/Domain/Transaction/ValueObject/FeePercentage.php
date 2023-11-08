<?php

declare(strict_types=1);

namespace App\Domain\Transaction\ValueObject;

use App\Domain\Common\ValueObject\Money;
use App\Domain\Transaction\TransactionFeeInterface;

class FeePercentage implements TransactionFeeInterface
{
    private float $percentage;

    public function __construct(float $percentage)
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new \InvalidArgumentException('Fee percentage must be between 0 and 100.');
        }

        $this->percentage = $percentage;
    }

    public function calculateFee(Money $value): Money
    {
        return new Money(($value->getAmount() / 100) * $this->percentage, $value->getCurrency());
    }
}