<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Tests\Symfony\Request\ParamConverter;

use MyOnlineStore\ApiTools\Symfony\Request\ParamConverter\ValinorParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

final class StubValinorParamConverterWithoutRegisteredConstructor extends ValinorParamConverter
{
    protected function getClass(): string
    {
        return StubId::class;
    }

    protected function getData(Request $request, ParamConverter $configuration): mixed
    {
        /** @psalm-suppress InternalMethod */
        return $request->get($configuration->getName());
    }
}
