<?php

declare(strict_types=1);

namespace App\Domain\Common\ValueObject;

use App\Domain\Currency\ValueObject\Currency;

final class Money
{
    public function __construct(private readonly float $amount, private readonly Currency $currency)
    {
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function add(Money $other): Money
    {
        $this->assertSameCurrency($this, $other);

        return new Money($this->amount + $other->getAmount(), $this->currency);
    }

    public function subtract(Money $other): Money
    {
        $this->assertSameCurrency($this, $other);

        return new Money($this->amount - $other->getAmount(), $this->currency);
    }

    private function assertSameCurrency(Money $first, Money $second): void
    {
        if ($first->getCurrency()->getCode() !== $second->getCurrency()->getCode()) {
            throw new \LogicException('Currencies must be the same to perform arithmetic operations.');
        }
    }
}