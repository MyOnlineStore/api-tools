<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Symfony\Request\ParamConverter;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use MyOnlineStore\ApiTools\Symfony\HttpKernel\Exception\JsonApiProblem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class ValinorParamConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        try {
            $request->attributes->set(
                $configuration->getName(),
                $this->getMapperBuilder()
                    ->mapper()
                    ->map($this->getClass(), $this->getData($request, $configuration))
            );
        } catch (MappingError $mappingError) {
            throw JsonApiProblem::fromValinorMappingError('Invalid Request', 'Invalid data provided.', $mappingError);
        } catch (\Throwable $exception) {
            throw new JsonApiProblem('Invalid Request', 'Invalid data provided.', 422);
        }

        return true;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $this->getClass() === $configuration->getClass();
    }

    /**
     * @return class-string
     */
    abstract protected function getClass(): string;

    /**
     * @return mixed
     */
    abstract protected function getData(Request $request, ParamConverter $configuration);

    protected function getMapperBuilder(): MapperBuilder
    {
        return new MapperBuilder();
    }
}
