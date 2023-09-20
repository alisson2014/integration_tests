<?php

declare(strict_types=1);

namespace Alura\Auction\Model;

class Auction
{
    /** @var Bid[] */
    private array $bids = [];
    private bool $finished = false;
    private \DateTimeInterface $startDate;

    public function __construct(
        private string $description,
        \DateTimeImmutable $startDate = null,
        private int|null $id = null
    ) {
        $this->startDate = $startDate ?? new \DateTimeImmutable();
    }

    public function receivesBid(Bid $bid)
    {
        if ($this->finished) {
            throw new \DomainException('Este leilão já está finalizado');
        }

        $lastMove = empty($this->bids)
            ? null
            : $this->bids[array_key_last($this->bids)];

        if (!empty($this->bids) && $lastMove->getUser() === $bid->getUser()) {
            throw new \DomainException('Usuário já deu o último lance');
        }

        $this->bids[] = $bid;
    }

    public function ends()
    {
        $this->finished = true;
    }

    /**
     * @return Bid[]
     */
    public function getBids(): array
    {
        return $this->bids;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function ItsMoreThanAWeekOld(): bool
    {
        $today = new \DateTime();
        $interval = $this->startDate->diff($today);

        return $interval->days > 7;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
