<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Tests\Symfony\Request\ParamConverter;

final class StubValue
{
    public function __construct(
        public string $foo,
        public int $bar,
    ) {
    }
}
