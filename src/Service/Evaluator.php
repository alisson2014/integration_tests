<?php

declare(strict_types=1);

namespace Alura\Auction\Service;

use Alura\Auction\Model\Bid;
use Alura\Auction\Model\Auction;

class Evaluator
{
    /** @var Bid[] */
    private array $bigger;
    private float $lowerValue = INF;
    private float $highestValue = 0;

    public function evaluate(Auction $auction): void
    {
        $auction->ends();

        foreach ($auction->getBids() as $lance) {
            if ($lance->getValue() > $this->highestValue) {
                $this->highestValue = $lance->getValue();
            }

            if ($lance->getValue() < $this->lowerValue) {
                $this->lowerValue = $lance->getValue();
            }

            $this->bigger = $this->evaluatesThreeBiggestBids($auction);
        }
    }

    public function getLowerValue(): float
    {
        return $this->lowerValue;
    }

    public function getHighestValue(): float
    {
        return $this->highestValue;
    }

    /** @return Bid[] */
    public function getThreeHighestBids(): array
    {
        return $this->bigger;
    }

    private function evaluatesThreeBiggestBids(Auction $auction): array
    {
        $bids = $auction->getBids();
        usort($bids, function (Bid $bid1, Bid $bid2): float {
            return $bid2->getValue() - $bid1->getValue();
        });

        return array_slice($bids, 0, 3);
    }
}
