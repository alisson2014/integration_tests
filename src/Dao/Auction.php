<?php

declare(strict_types=1);

namespace Alura\Auction\Dao;

use Alura\Auction\Infrastructure\ConnectionCreator;
use Alura\Auction\Model\Auction as ModelAuction;

class Auction
{

    public function __construct(private \PDO $conn)
    {
    }

    public function save(ModelAuction $auction): void
    {
        $sql = 'INSERT INTO leiloes (descricao, finalizado, dataInicio) VALUES (?, ?, ?)';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $auction->getDescription(), \PDO::PARAM_STR);
        $stmt->bindValue(2, $auction->isFinished(), \PDO::PARAM_BOOL);
        $stmt->bindValue(3, $auction->getStartDate()->format('Y-m-d'));
        $stmt->execute();
    }

    /** @return ModelAuction[] */
    public function recoverUnfinished(): array
    {
        return $this->recoverAuctionsIfCompleted(false);
    }

    /** @return ModelAuction[] */
    public function recoverFinished(): array
    {
        return $this->recoverAuctionsIfCompleted(true);
    }

    /** @return ModelAuction[] */
    private function recoverAuctionsIfCompleted(bool $finished): array
    {
        $sql = 'SELECT * FROM leiloes WHERE finalizado = ' . ($finished ? 1 : 0);
        $stmt = $this->conn->query($sql, \PDO::FETCH_ASSOC);

        $data = $stmt->fetchAll();
        $auctions = [];
        foreach ($data as $given) {
            $auction = new ModelAuction($given['descricao'], new \DateTimeImmutable($given['dataInicio']), $given['id']);
            if ($given['finalizado']) {
                $auction->ends();
            }
            $auctions[] = $auction;
        }

        return $auctions;
    }

    public function update(ModelAuction $auction): void
    {
        $sql = 'UPDATE leiloes SET descricao = :descricao, dataInicio = :dataInicio, finalizado = :finalizado WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':descricao', $auction->getDescription());
        $stmt->bindValue(':dataInicio', $auction->getStartDate()->format('Y-m-d'));
        $stmt->bindValue(':finalizado', $auction->isFinished(), \PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $auction->getId(), \PDO::PARAM_INT);
        $stmt->execute();
    }
}
