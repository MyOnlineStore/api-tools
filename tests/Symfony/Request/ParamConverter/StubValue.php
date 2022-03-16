<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Tests\Symfony\Request\ParamConverter;

final class StubValue
{
    public function __construct(
        public StubId $id,
        public string $foo,
        public int $bar,
    ) {
    }
}
