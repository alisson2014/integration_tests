<?php

declare(strict_types=1);

namespace Alura\Tests\Service;

use Alura\Auction\Model\Auction;
use Alura\Auction\Dao\Auction as DaoAuction;
use Alura\Auction\Service\EmailSender;
use Alura\Auction\Service\Terminator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TerminatorTest extends TestCase
{
    private Terminator $terminator;
    private $mockEmailSender;
    private Auction $fiatAuction;
    private Auction $variantAuction;

    public function setUp(): void
    {
        $this->fiatAuction = new Auction(
            'Fiat 147 0Km',
            new \DateTimeImmutable('8 days ago')
        );

        $this->variantAuction = new Auction(
            'Variante 0Km',
            new \DateTimeImmutable('10 days ago')
        );

        $mockAuction = $this->createMock(DaoAuction::class);
        $mockAuction->method('recoverUnfinished')
            ->willReturn([$this->fiatAuction, $this->variantAuction]);
        $matcher = $this->exactly(2);
        $mockAuction->expects($matcher)
            ->method('update')
            ->willReturnCallback(function (Auction $value) use ($matcher) {
                match ($matcher->numberOfInvocations()) {
                    1 => self::assertEquals($this->fiatAuction, $value),
                    2 => self::assertEquals($this->variantAuction, $value)
                };
            }); 

        $this->mockEmailSender = $this->createMock(EmailSender::class);
        $this->terminator = new Terminator($mockAuction, $this->mockEmailSender);
    }

    public function testAuctionsWithMoreThanAWeekMustBeClosed()
    {
        $this->terminator->closes();
        $auctionsClosed = [$this->fiatAuction, $this->variantAuction];

        self::assertCount(2, $auctionsClosed);
        self::assertTrue($auctionsClosed[0]->isFinished());
        self::assertTrue($auctionsClosed[1]->isFinished());
    }

    public function testMustContinueProcessingWhenEncounteringErrorWhenSendingEmail()
    {
        $e = new \DomainException('Erro ao enviar e-mail');
        $this->mockEmailSender->expects(self::exactly(2))
            ->method('notifyEndOfAuction')
            ->willThrowException($e);

        $this->terminator->closes();
    }

    public function testThenYouShouldSendAuctionByEmailOnceCompleted()
    {
        $this->mockEmailSender->expects(self::exactly(2))
            ->method('notifyEndOfAuction')
            ->willReturnCallback(function (Auction $auction) {
                self::assertTrue($auction->isFinished());
            });

        $this->terminator->closes();
    }
}
