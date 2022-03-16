<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Symfony\Request\ParamConverter;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\MapperBuilder;
use MyOnlineStore\ApiTools\Symfony\HttpKernel\Exception\JsonApiProblem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class ValinorJsonConverter implements ParamConverterInterface
{
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        try {
            $request->attributes->set(
                $configuration->getName(),
                (new MapperBuilder())
                    ->mapper()
                    ->map($this->getClass(), new JsonSource($request->getContent()))
            );
        } catch (MappingError $mappingError) {
            throw JsonApiProblem::fromValinorMappingError('Invalid Request', 'Invalid JSON provided.', $mappingError);
        } catch (\Throwable) {
            throw new JsonApiProblem('Invalid Request', 'Invalid JSON provided.', 422);
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
}
