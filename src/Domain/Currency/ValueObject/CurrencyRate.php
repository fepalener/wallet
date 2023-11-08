<?php

declare(strict_types=1);

namespace App\Domain\Currency\ValueObject;

use App\Domain\Common\ValueObject\Money;

class CurrencyRate
{
    public function __construct(
        private readonly Currency $baseCurrency,
        private readonly Currency $targetCurrency,
        private readonly float $rate
    ) {
    }

    public function getBaseCurrency(): Currency
    {
        return $this->baseCurrency;
    }

    public function getTargetCurrency(): Currency
    {
        return $this->targetCurrency;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function convertAmount(Money $value): Money
    {
        return new Money($value->getAmount() * $this->rate, $value->getCurrency());
    }
}