<?php

declare(strict_types=1);

namespace App\Domain\Transaction;

use App\Domain\Common\ValueObject\Money;
use App\Domain\Currency\ValueObject\Currency;

interface TransactionServiceInterface
{
    public function sellCurrency(Currency $targetCurrency, Money $operation): Money;

    public function buyCurrency(Currency $targetCurrency, Money $operation): Money;
}