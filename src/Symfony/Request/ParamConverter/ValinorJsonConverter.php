<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Symfony\Request\ParamConverter;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\JsonSource;
use CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
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
            $messages = \iterator_to_array((new MessagesFlattener($mappingError->node()))->errors());

            throw new JsonApiProblem(
                'Invalid Request',
                'Invalid JSON provided.',
                422,
                [
                    'errors' => \array_combine(
                        \array_map(static fn (NodeMessage $message): string => $message->path(), $messages),
                        \array_map(static fn (NodeMessage $message): string => $message->__toString(), $messages),
                    ),
                ],
            );
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
