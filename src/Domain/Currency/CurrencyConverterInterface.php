<?php

declare(strict_types=1);

namespace App\Domain\Currency;

use App\Domain\Common\ValueObject\Money;
use App\Domain\Currency\ValueObject\Currency;
use App\Domain\Currency\ValueObject\CurrencyRate;

interface CurrencyConverterInterface
{
    public function convert(Currency $currencyFrom, Currency $currencyTo, Money $value): Money;

    public function ratio(Currency $currencyFrom, Currency $currencyTo): CurrencyRate;
}