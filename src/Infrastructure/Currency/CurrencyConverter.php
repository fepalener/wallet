<?php

namespace App\Infrastructure\Currency;

use App\Domain\Common\ValueObject\Money;
use App\Domain\Currency\CurrencyConverterInterface;
use App\Domain\Currency\CurrencyServiceInterface;
use App\Domain\Currency\Exception\CurrencyRateNotFoundException;
use App\Domain\Currency\Exception\InvalidCurrencyConversionException;
use App\Domain\Currency\ValueObject\Currency;
use App\Domain\Currency\ValueObject\CurrencyRate;

class CurrencyConverter implements CurrencyConverterInterface
{
    public function __construct(private readonly CurrencyServiceInterface $currencyService)
    {
    }

    public function convert(Currency $currencyFrom, Currency $currencyTo, Money $value): Money
    {
        return $this->ratio($currencyFrom, $currencyTo)->convertAmount($value);
    }

    public function ratio(Currency $currencyFrom, Currency $currencyTo): CurrencyRate
    {
        try {
            return $this->currencyService->getRate($currencyFrom, $currencyTo);
        } catch (CurrencyRateNotFoundException $e) {
            throw new InvalidCurrencyConversionException(sprintf('Rate not found for %s -> %s', $currencyFrom->getCode(), $currencyTo->getCode()));
        }
    }
}