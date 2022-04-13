<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Tests\Symfony\Request\ParamConverter;

use Webmozart\Assert\Assert;

final class StubId
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function fromString(string $id): self
    {
        Assert::stringNotEmpty($id);

        return new self($id);
    }

    public function toString(): string
    {
        return $this->id;
    }
}
