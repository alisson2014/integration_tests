<?php

declare(strict_types=1);

namespace Alura\Tests\Service;

use Alura\Auction\Model\Auction;
use Alura\Auction\Dao\Auction as DaoAuction;
use Alura\Auction\Service\Terminator;
use PHPUnit\Framework\TestCase;

class DaoAuctionMock extends DaoAuction
{
    private array $auctions = [];

    public function save(Auction $auction): void
    {
        $this->auctions[] = $auction;
    }

    public function recoverFinished(): array
    {
        return array_filter($this->auctions, function (Auction $auction): bool {
            return $auction->isFinished();
        });
    }

    public function recoverUnfinished(): array
    {
        return array_filter($this->auctions, function (Auction $auction): bool {
            return !$auction->isFinished();
        });
    }

    public function update(Auction $auction): void
    {
        return;
    }
}

class TerminatorTest extends TestCase
{
    public function testAuctionsOlderThanAWeekMustBeCanceled(): void
    {
        $fiatAuction = new Auction(
            'Fiat 147 0Km',
            new \DateTimeImmutable('8 days ago')
        );

        $variantAuction = new Auction(
            'Variante 0Km',
            new \DateTimeImmutable('10 days ago')
        );

        $mockAuction = new DaoAuctionMock();
        $mockAuction->save($fiatAuction);
        $mockAuction->save($variantAuction);

        $terminator = new Terminator($mockAuction);
        $terminator->closes();

        $auctionsClosed = $mockAuction->recoverFinished();

        self::assertCount(2, $auctionsClosed);
        self::assertEquals(
            'Fiat 147 0Km',
            $auctionsClosed[0]->getDescription()
        );
        self::assertEquals(
            'Variante 0Km',
            $auctionsClosed[1]->getDescription()
        );
    }
}
