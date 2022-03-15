<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Tests\Symfony\Request\ParamConverter;

use MyOnlineStore\ApiTools\Symfony\Request\ParamConverter\ValinorJsonConverter;

final class StubValinorJsonConverter extends ValinorJsonConverter
{
    protected function getClass(): string
    {
        return StubValue::class;
    }
}
