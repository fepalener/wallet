<?php

declare(strict_types=1);

namespace App\Domain\Wallet;

use App\Domain\Common\ValueObject\Money;
use App\Domain\Currency\ValueObject\Currency;
use App\Domain\Wallet\Exception\BalanceNotAvailableException;
use App\Domain\Wallet\Exception\InsufficientFundsException;

class UserWallet {

    private array $balances;

    public function __construct() {
        $this->balances = [];
    }

    public function deposit(Money $money): void
    {
        $currencyCode = $money->getCurrency()->getCode();

        if (!isset($this->balances[$currencyCode])) {
            $this->balances[$currencyCode] = new Money(0, $money->getCurrency());
        }

        $this->balances[$currencyCode] = $this->balances[$currencyCode]->add($money);
    }

    public function withdraw(Money $money): void
    {
        $currencyCode = $money->getCurrency()->getCode();

        if (!isset($this->balances[$currencyCode])) {
            throw new BalanceNotAvailableException(sprintf('No balance available for %s', $currencyCode));
        }

        $this->balances[$currencyCode] = $this->balances[$currencyCode]->subtract($money);

        if ($this->balances[$currencyCode]->getAmount() < 0) {
            throw new InsufficientFundsException(sprintf('Insufficient funds in %s', $currencyCode));
        }
    }

    public function getBalance(Currency $currency): Money
    {
        $currencyCode = $currency->getCode();
        return $this->balances[$currencyCode] ?? new Money(0, $currency);
    }
}