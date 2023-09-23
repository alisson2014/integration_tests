<?php

declare(strict_types=1);

namespace Alura\Auction\Service;

use Alura\Auction\Dao\Auction as DaoAuction;

class Terminator
{
    public function __construct(
        private DaoAuction $dao,
        private EmailSender $emailSender
    ) {
    }

    public function closes(): void
    {
        $auctions = $this->dao->recoverUnfinished();

        foreach ($auctions as $auction) {
            if ($auction->ItsMoreThanAWeekOld()) {
                try {
                    $auction->ends();
                    $this->dao->update($auction);
                    $this->emailSender->notifyEndOfAuction($auction);
                } catch (\DomainException $e) {
                    error_log($e->getMessage());
                }
            }
        }
    }
}
