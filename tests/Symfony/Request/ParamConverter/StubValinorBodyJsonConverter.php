<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Tests\Symfony\Request\ParamConverter;

use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\MapperBuilder;
use MyOnlineStore\ApiTools\Symfony\Request\ParamConverter\ValinorParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

final class StubValinorBodyJsonConverter extends ValinorParamConverter
{
    protected function getClass(): string
    {
        return StubValue::class;
    }

    protected function getData(Request $request, ParamConverter $configuration): JsonSource
    {
        return new JsonSource($request->getContent());
    }

    protected function getMapperBuilder(): MapperBuilder
    {
        return parent::getMapperBuilder()
            ->registerConstructor(static fn (string $id): StubId => StubId::fromString($id));
    }
}
