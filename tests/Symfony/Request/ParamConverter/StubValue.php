<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Tests\Symfony\Request\ParamConverter;

/**
 * @psalm-immutable
 */
final class StubValue
{
    private int $bar;
    private string $foo;
    private StubId $id;

    public function __construct(StubId $id, string $foo, int $bar)
    {
        $this->id = $id;
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
