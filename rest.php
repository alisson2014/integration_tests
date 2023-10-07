<?php

declare(strict_types=1);

use Alura\Auction\Dao\Auction as DaoAuction;
use Alura\Auction\Model\Auction;

require_once __DIR__ . '/vendor/autoload.php';

$pdo = new \PDO('sqlite::memory:');
$pdo->exec('CREATE TABLE leiloes (
    id INTEGER primary key,
    descricao TEXT,
    finalizado BOOL,
    dataInicio TEXT
);');
$daoAuction = new DaoAuction($pdo);

$auction1 = new Auction('Leilão 1');
$auction2 = new Auction('Leilão 2');
$auction3 = new Auction('Leilão 3');
$auction4 = new Auction('Leilão 4');

$daoAuction->save($auction1);
$daoAuction->save($auction2);
$daoAuction->save($auction3);
$daoAuction->save($auction4);

header('Content-type: application/json');
echo json_encode(array_map(function (Auction $auction): array {
    return [
        'descricao' => $auction->getDescription(),
        'estaFinalizado' => $auction->isFinished(),
    ];
}, $daoAuction->recoverUnfinished()));
