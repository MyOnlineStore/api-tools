<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Tests\Symfony\HttpKernel\Exception;

use MyOnlineStore\ApiTools\Symfony\HttpKernel\Exception\JsonApiProblem;
use PHPUnit\Framework\TestCase;

final class JsonApiProblemTest extends TestCase
{
    public function testAccessorsWithMinimalVariables(): void
    {
        $title = 'title';
        $detail = 'detail';

        $jsonApiProblem = new JsonApiProblem($title, $detail);

        self::assertEquals($title, $jsonApiProblem->getTitle());
        self::assertEquals($detail, $jsonApiProblem->getDetail());
        self::assertEquals(500, $jsonApiProblem->getStatusCode());
        self::assertEquals(500, $jsonApiProblem->getCode());
        self::assertEquals([], $jsonApiProblem->getAdditionalInformation());
        self::assertEquals('https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html', $jsonApiProblem->getType());
        self::assertEquals(['Content-Type' => 'application/json'], $jsonApiProblem->getHeaders());
        self::assertNull($jsonApiProblem->getPrevious());
    }

    public function testAccessorsWithMaximalVariables(): void
    {
        $title = 'title';
        $detail = 'detail';
        $statusCode = 400;
        $additionalInformation = ['foo', 'bar'];
        $type = 'type';
        $throwable = $this->createMock(\Throwable::class);

        $jsonApiProblem = new JsonApiProblem($title, $detail, $statusCode, $additionalInformation, $type, $throwable);

        self::assertEquals($title, $jsonApiProblem->getTitle());
        self::assertEquals($detail, $jsonApiProblem->getDetail());
        self::assertEquals($statusCode, $jsonApiProblem->getStatusCode());
        self::assertEquals($statusCode, $jsonApiProblem->getCode());
        self::assertEquals($additionalInformation, $jsonApiProblem->getAdditionalInformation());
        self::assertEquals($type, $jsonApiProblem->getType());
        self::assertEquals(['Content-Type' => 'application/json'], $jsonApiProblem->getHeaders());
        self::assertSame($throwable, $jsonApiProblem->getPrevious());
    }
}
