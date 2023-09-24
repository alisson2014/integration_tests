<?php

declare(strict_types=1);

namespace Alura\Auction\Service;

use Alura\Auction\Model\Auction;

class EmailSender
{
    private const USER_EMAIL = "usuario@email.com";
    public function notifyEndOfAuction(Auction $auction): void
    {
        $success = mail(
            self::USER_EMAIL, 
            "Leilão finalizado", 
            "O leilão para {$auction->getDescription()} foi finalizado."
        );

        if (!$success) {
            throw new \DomainException("Erro ao enviar email.");
        }
    }
}