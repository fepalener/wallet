<?php

declare(strict_types=1);

namespace App\Domain\Currency\ValueObject;

use App\Domain\Currency\Exception\InvalidCurrencyCodeException;

final class Currency
{
    private string $code;

    public function __construct(string $code)
    {
        $this->setCode($code);
    }

    private function setCode(string $code): void
    {
        if (!preg_match('/^[A-Z]{3}$/', $code)) {
            throw new InvalidCurrencyCodeException();
        }

        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}