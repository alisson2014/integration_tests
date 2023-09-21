<?php

declare(strict_types=1);

namespace Alura\Tests\Service;

use Alura\Auction\Model\Auction;
use Alura\Auction\Dao\Auction as DaoAuction;
use Alura\Auction\Service\Terminator;
use PHPUnit\Framework\TestCase;

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

        $mockAuction = $this->createMock(DaoAuction::class);
        $mockAuction->method('recoverUnfinished')
            ->willReturn([$fiatAuction, $variantAuction]);
        $matcher = $this->exactly(2);
        $mockAuction->expects($matcher)
            ->method('update')
            ->willReturnCallback(function (Auction $value) use ($matcher, $fiatAuction, $variantAuction) {
                match ($matcher->numberOfInvocations()) {
                    1 => self::assertEquals($fiatAuction, $value),
                    2 => self::assertEquals($variantAuction, $value)
                };
            }); 

        $terminator = new Terminator($mockAuction);
        $terminator->closes();

        $auctionsClosed = [$fiatAuction, $variantAuction];

        self::assertCount(2, $auctionsClosed);
        self::assertTrue($auctionsClosed[0]->isFinished());
        self::assertTrue($auctionsClosed[1]->isFinished());
    }
}
