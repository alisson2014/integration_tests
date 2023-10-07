<?php

declare(strict_types=1);

namespace Alura\Tests\Integration\Dao;

use Alura\Auction\Dao\Auction as DaoAuction;
use Alura\Auction\Model\Auction;
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

    public function testInsertionShouldWork(): void
    {
        $auction = new Auction('Variante 0km');
        $daoAuction = new DaoAuction(self::$pdo);

        $daoAuction->save($auction);

        $auctions = $daoAuction->recoverUnfinished();

        self::assertCount(1, $auctions);
        self::assertContainsOnlyInstancesOf(Auction::class, $auctions);
        self::assertSame('Variante 0km', $auctions[0]->getDescription());
    }

    protected function tearDown(): void
    {
        self::$pdo->rollBack();
    }
}
