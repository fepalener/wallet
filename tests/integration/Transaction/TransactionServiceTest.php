<?php

declare(strict_types=1);

namespace Tests\Integration\Transaction;

use App\Domain\Common\ValueObject\Money;
use App\Domain\Currency\CurrencyServiceInterface;
use App\Domain\Currency\Exception\CurrencyRateNotFoundException;
use App\Domain\Currency\Exception\InvalidCurrencyConversionException;
use App\Domain\Currency\ValueObject\Currency;
use App\Domain\Currency\ValueObject\CurrencyRate;
use App\Domain\Transaction\ValueObject\FeePercentage;
use App\Domain\Wallet\Exception\BalanceNotAvailableException;
use App\Domain\Wallet\Exception\InsufficientFundsException;
use App\Domain\Wallet\UserWallet;
use App\Infrastructure\Currency\CurrencyConverter;
use App\Infrastructure\TransactionService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

final class TransactionServiceTest extends TestCase
{
    const EUR_TO_GBP_RATE = 1.5678;
    const GBP_TO_EUR_RATE = 1.5432;

    const TRANSACTION_FEE_PERCENT = 1;

    /**
     * @throws Exception
     */
    #[DataProvider('dataProvider')]
    public function testTransactionService(string $operation, Money $transactionValue, Currency $targetCurrency, CurrencyRate $expectedRate, float $earnedAmount)
    {
        $userWallet = new UserWallet();
        $userWallet->deposit(new Money(200, new Currency('EUR')));
        $userWallet->deposit(new Money(200, new Currency('GBP')));

        $mockRepo = $this->createMock(CurrencyServiceInterface::class);
        $mockRepo->method('getRate')
            ->with($transactionValue->getCurrency(), $targetCurrency)
            ->willReturn($expectedRate);

        $currencyConverter = new CurrencyConverter($mockRepo);

        $transactionFee = new FeePercentage(self::TRANSACTION_FEE_PERCENT);

        $sut = new TransactionService($userWallet, $currencyConverter, $transactionFee);

        $result = null;

        switch ($operation)
        {
            case 'buy':
                $result = $sut->buyCurrency($targetCurrency, $transactionValue);
                break;
            case 'sell':
                $result = $sut->sellCurrency($targetCurrency, $transactionValue);
                break;
        }

        $this->assertSame($earnedAmount, round($result->getAmount(), 2));
    }

    public function testExceptionIsThrownForInsufficientFunds()
    {
        $this->expectException(InsufficientFundsException::class);
        $this->expectExceptionMessage('Insufficient funds in EUR');

        $eur = new Currency('EUR');
        $gbp = new Currency('GBP');

        $eur_to_gbp = new CurrencyRate($eur, $gbp,self::EUR_TO_GBP_RATE);

        $userWallet = new UserWallet();
        $userWallet->deposit(new Money(0, $eur));

        $mockRepo = $this->createMock(CurrencyServiceInterface::class);
        $mockRepo->method('getRate')
            ->with($eur, $gbp)
            ->willReturn($eur_to_gbp);

        $currencyConverter = new CurrencyConverter($mockRepo);
        $transactionFee = new FeePercentage(self::TRANSACTION_FEE_PERCENT);

        $sut = new TransactionService($userWallet, $currencyConverter, $transactionFee);
        $sut->sellCurrency($gbp, new Money(100, $eur));
    }

    public function testExceptionIsThrownForBalanceNotAvailable()
    {
        $this->expectException(BalanceNotAvailableException::class);
        $this->expectExceptionMessage('No balance available for EUR');

        $eur = new Currency('EUR');
        $gbp = new Currency('GBP');

        $eur_to_gbp = new CurrencyRate($eur, $gbp,self::EUR_TO_GBP_RATE);

        $userWallet = new UserWallet();

        $mockRepo = $this->createMock(CurrencyServiceInterface::class);
        $mockRepo->method('getRate')
            ->with($eur, $gbp)
            ->willReturn($eur_to_gbp);

        $currencyConverter = new CurrencyConverter($mockRepo);
        $transactionFee = new FeePercentage(self::TRANSACTION_FEE_PERCENT);

        $sut = new TransactionService($userWallet, $currencyConverter, $transactionFee);
        $sut->sellCurrency($gbp, new Money(100, $eur));
    }

    public function testExceptionIsThrownForRateNotFound()
    {
        $this->expectException(InvalidCurrencyConversionException::class);
        $this->expectExceptionMessage('Rate not found for EUR -> GBP');

        $eur = new Currency('EUR');
        $gbp = new Currency('GBP');

        $userWallet = new UserWallet();
        $userWallet->deposit(new Money(100, $eur));

        $mockRepo = $this->createMock(CurrencyServiceInterface::class);
        $mockRepo->method('getRate')
            ->with($eur, $gbp)
            ->willThrowException(new CurrencyRateNotFoundException());

        $currencyConverter = new CurrencyConverter($mockRepo);
        $transactionFee = new FeePercentage(self::TRANSACTION_FEE_PERCENT);

        $sut = new TransactionService($userWallet, $currencyConverter, $transactionFee);
        $sut->sellCurrency($gbp, new Money(100, $eur));
    }

    /**
     * Klient sprzedaje 100 EUR za GBP
     * Klient kupuje 100 GBP za EUR
     * Klient sprzedaje 100 GBP za EUR
     * Klient kupuje 100 EUR za GBP
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        $eur = new Currency('EUR');
        $gbp = new Currency('GBP');

        $eur_to_gbp = new CurrencyRate($eur, $gbp,self::EUR_TO_GBP_RATE);
        $gbp_to_eur = new CurrencyRate($gbp, $eur,self::GBP_TO_EUR_RATE);

        return [
            ['sell', new Money(100, $eur), $gbp, $eur_to_gbp, 155.21],
            ['buy', new Money(100, $eur), $gbp, $gbp_to_eur, 154.32],
            ['sell', new Money(100, $gbp), $eur, $gbp_to_eur, 152.78],
            ['buy', new Money(100, $gbp), $eur, $eur_to_gbp, 156.78]
        ];
    }
}