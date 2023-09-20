<?php

declare(strict_types=1);

namespace Alura\Tests\Domain;

use Alura\Auction\Model\Bid;
use Alura\Auction\Model\Auction;
use Alura\Auction\Model\User;
use DomainException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AuctionTest extends TestCase
{
    public function testProposeBidInFinalizedAuctionMustLaunchException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Este leilão já está finalizado');

        $auction = new Auction('Fiat 147 0KM');
        $auction->ends();

        $auction->receivesBid(new Bid(new User(''), 1000));
    }


    #[DataProvider('dataToProposeBids')]
    public function testProposingBidsAtAuctionShouldWork(
        int $qtdEsperado,
        array $lances
    ): void {
        $auction = new Auction('Fiat 147 0KM');
        foreach ($lances as $lance) {
            $auction->receivesBid($lance);
        }

        self::assertCount($qtdEsperado, $auction->getBids());
    }

    public function testEvenUserCannotProposeTwoBidsInaRow(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Usuário já deu o último lance');
        $user = new User('Ganancioso');

        $auction = new Auction('Objeto inútil');

        $auction->receivesBid(new Bid($user, 1000));
        $auction->receivesBid(new Bid($user, 1100));
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
