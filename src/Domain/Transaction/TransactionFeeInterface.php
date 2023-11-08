<?php

declare(strict_types=1);

namespace App\Domain\Transaction;

use App\Domain\Common\ValueObject\Money;

interface TransactionFeeInterface
{
    public function calculateFee(Money $value): Money;
}