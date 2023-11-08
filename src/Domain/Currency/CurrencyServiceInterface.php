<?php

declare(strict_types=1);

namespace App\Domain\Currency;

use App\Domain\Currency\ValueObject\Currency;
use App\Domain\Currency\ValueObject\CurrencyRate;

interface CurrencyServiceInterface
{
    public function getRate(Currency $currencyFrom, Currency $currencyTo): CurrencyRate;
}