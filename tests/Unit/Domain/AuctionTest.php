<?php

declare(strict_types=1);

namespace Alura\Tests\Unit\Domain;

use Alura\Auction\Model\Bid;
use Alura\Auction\Model\Auction;
use Alura\Auction\Model\User;
use DomainException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AuctionTest extends TestCase
{
    private User $user1;

    public function setUp(): void
    {
        $this->user1 = new User('Irineu');
    }

    public function testProposeBidInFinalizedAuctionMustLaunchException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Este leilão já está finalizado');

        $auction = new Auction('Fiat 147 0KM');
        $auction->ends();

        $auction->receivesBid(new Bid($this->user1, 1000));
    }


    #[DataProvider('dataToProposeBids')]
    public function testProposingBidsAtAuctionShouldWork(
        int $expected,
        array $bids
    ): void {
        $auction = new Auction('Fiat 147 0KM');
        foreach ($bids as $bid) {
            $auction->receivesBid($bid);
        }

        self::assertCount($expected, $auction->getBids());
    }

    public function testEvenUserCannotProposeTwoBidsInaRow(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Usuário já deu o último lance');

        $auction = new Auction('Objeto inútil');

        $auction->receivesBid(new Bid($this->user1, 1000));
        $auction->receivesBid(new Bid($this->user1, 1100));
    }

    public static function dataToProposeBids(): array
    {
        $user1 = new User('Usuário 1');
        $user2 = new User('Usuário 2');

        return [
            [1, [new Bid($user1, 1000)]],
            [2, [new Bid($user1, 1000), new Bid($user2, 2000)]],
        ];
    }
}
