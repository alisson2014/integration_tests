<?php

declare(strict_types=1);

namespace Alura\Auction\Tests\Integration\Web;

use PHPUnit\Framework\TestCase;

class RestTest extends TestCase
{
    private const API_URL_BASE = 'http://localhost:8080/';
    public function testApiRestMustReturnAuctionArray(): void
    {
        $response = file_get_contents(self::API_URL_BASE . 'rest.php');

        self::assertStringContainsString('200 OK', $http_response_header[0]);
        self::assertIsArray(json_decode($response));
    }
}
