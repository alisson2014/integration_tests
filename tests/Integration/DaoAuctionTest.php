<?php

declare(strict_types=1);

namespace Alura\Tests\Integration\Dao;

use Alura\Auction\Dao\Auction as DaoAuction;
use Alura\Auction\Model\Auction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DaoAuctionTest extends TestCase
{
    private static \PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new \PDO('sqlite::memory:');
        self::$pdo->exec('CREATE TABLE leiloes (
            id INTEGER PRIMARY KEY, 
            descricao TEXT, 
            finalizado BOOL, 
            dataInicio TEXT
        )');
    }

    public function setUp(): void
    {
        self::$pdo->beginTransaction();
    }

    #[DataProvider('auctions')]
    public function testSearchUnfinishedAuctions(array $auctions): void
    {
        //Arrange
        $daoAuction = new DaoAuction(self::$pdo);

        foreach ($auctions as $auction) {
            $daoAuction->save($auction);
        }

        //Act
        $auctions = $daoAuction->recoverUnfinished();

        //Assert
        self::assertCount(1, $auctions);
        self::assertContainsOnlyInstancesOf(Auction::class, $auctions);
        self::assertSame('Variante 0km', $auctions[0]->getDescription());
        self::assertFalse($auctions[0]->isFinished());
    }

    #[DataProvider('auctions')]
    public function testSearchFinishedAuctions(array $auctions): void
    {
        //Arrange
        $daoAuction = new DaoAuction(self::$pdo);

        foreach ($auctions as $auction) {
            $daoAuction->save($auction);
        }

        //Act
        $auctions = $daoAuction->recoverFinished();

        //Assert
        self::assertCount(1, $auctions);
        self::assertContainsOnlyInstancesOf(Auction::class, $auctions);
        self::assertSame('Fiat 147 0km', $auctions[0]->getDescription());
        self::assertTrue($auctions[0]->isFinished());
    }

    protected function tearDown(): void
    {
        self::$pdo->rollBack();
    }

    public static function auctions(): array
    {
        $unFinished = new Auction('Variante 0km');
        $finished = new Auction('Fiat 147 0km');
        $finished->ends();
        $auctions = [$unFinished, $finished];

        return [
            [$auctions]
        ];
    }
}
