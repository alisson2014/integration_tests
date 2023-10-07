<?php

declare(strict_types=1);

namespace Alura\Tests\Service;

use Alura\Auction\Model\Bid;
use Alura\Auction\Model\Auction;
use Alura\Auction\Model\User;
use Alura\Auction\Service\Evaluator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EvaluatorTest extends TestCase
{
    private readonly Evaluator $evaluator;

    protected function setUp(): void
    {
        $this->evaluator = new Evaluator();
    }

    #[DataProvider('auctionWithBidsInRandomOrder')]
    #[DataProvider('auctionWithBidsInDescendingOrder')]
    #[DataProvider('auctionWithBidsInAscendingOrder')]
    public function testAppraiserMustFindGreaterValue(Auction $auction): void
    {
        $this->evaluator->evaluate($auction);

        self::assertEquals(2000, $this->evaluator->getHighestValue());
    }

    #[DataProvider('auctionWithBidsInRandomOrder')]
    #[DataProvider('auctionWithBidsInDescendingOrder')]
    #[DataProvider('auctionWithBidsInAscendingOrder')]
    public function testAppraiserMustFindLowestValue(Auction $auction): void
    {
        $this->evaluator->evaluate($auction);

        self::assertEquals(1000, $this->evaluator->getLowerValue());
    }


    #[DataProvider('auctionWithBidsInRandomOrder')]
    #[DataProvider('auctionWithBidsInDescendingOrder')]
    #[DataProvider('auctionWithBidsInAscendingOrder')]
    public function testEvaluatorMustOrderTheThreeBids(Auction $auction): void
    {
        $this->evaluator->evaluate($auction);

        $bids = $this->evaluator->getThreeHighestBids();

        self::assertCount(3, $bids);
        self::assertEquals(2000, $bids[0]->getValue());
        self::assertEquals(1500, $bids[1]->getValue());
        self::assertEquals(1000, $bids[2]->getValue());
    }

    public function testEvaluatorMustReturnHighestBidsAvailable(): void
    {
        $auction = new Auction('Fiat 147 0KM');

        $auction->receivesBid(new Bid(new User('João'), 1000));
        $auction->receivesBid(new Bid(new User('Maria'), 1500));

        $this->evaluator->evaluate($auction);

        self::assertCount(2, $this->evaluator->getThreeHighestBids());
    }

    public static function auctionWithBidsInAscendingOrder(): array
    {
        $auction = new Auction('Fiat 147 0KM');
        $joao = new User('João');
        $maria = new User('Maria');
        $ana = new User('Ana');

        $auction->receivesBid(new Bid($joao, 1000));
        $auction->receivesBid(new Bid($maria, 1500));
        $auction->receivesBid(new Bid($ana, 2000));

        return [
            [$auction]
        ];
    }

    public static function auctionWithBidsInDescendingOrder(): array
    {
        $auction = new Auction('Fiat 147 0KM');
        $joao = new User('João');
        $maria = new User('Maria');
        $ana = new User('Ana');

        $auction->receivesBid(new Bid($ana, 2000));
        $auction->receivesBid(new Bid($maria, 1500));
        $auction->receivesBid(new Bid($joao, 1000));

        return [
            [$auction]
        ];
    }

    public static function auctionWithBidsInRandomOrder(): array
    {
        $auction = new Auction('Fiat 147 0KM');
        $joao = new User('João');
        $maria = new User('Maria');
        $ana = new User('Ana');

        $auction->receivesBid(new Bid($maria, 1500));
        $auction->receivesBid(new Bid($ana, 2000));
        $auction->receivesBid(new Bid($joao, 1000));

        return [
            [$auction]
        ];
    }
}
