<?php

namespace App\Infrastructure;

use App\Domain\Common\ValueObject\Money;
use App\Domain\Currency\ValueObject\Currency;
use App\Domain\Currency\CurrencyConverterInterface;
use App\Domain\Transaction\TransactionFeeInterface;
use App\Domain\Transaction\TransactionServiceInterface;
use App\Domain\Wallet\UserWallet;

class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        private readonly UserWallet $userWallet,
        private readonly CurrencyConverterInterface $currencyConverter,
        private readonly TransactionFeeInterface $transactionFee
    ) {
    }

    public function sellCurrency(Currency $targetCurrency, Money $operation): Money
    {
        $this->userWallet->withdraw($operation);

        $fee = $this->transactionFee->calculateFee($operation);
        $netAmount = $operation->subtract($fee);

        $convertedAmount = $this->currencyConverter->convert($operation->getCurrency(), $targetCurrency, $netAmount);
        $this->userWallet->deposit($convertedAmount);

        return $convertedAmount;
    }

    public function buyCurrency(Currency $targetCurrency, Money $operation): Money
    {
        $convertedAmount = $this->currencyConverter->convert($operation->getCurrency(), $targetCurrency, $operation);

        $fee = $this->transactionFee->calculateFee($convertedAmount);

        $totalCost = $convertedAmount->add($fee);

        // Withdraw the total cost from the user's wallet
        $this->userWallet->withdraw($totalCost);

        // Add the original converted amount without fee to the user's wallet
        $this->userWallet->deposit(new Money($convertedAmount->getAmount(), $targetCurrency));

        return $convertedAmount;
    }
}