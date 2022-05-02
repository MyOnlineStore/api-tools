<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Symfony\Request\ParamConverter;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener;
use CuyZ\Valinor\MapperBuilder;
use MyOnlineStore\ApiTools\Symfony\HttpKernel\Exception\JsonApiProblem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class ValinorParamConverter implements ParamConverterInterface
{
    public function __construct(
        private bool $debug = false,
        private ?string $valinorCacheDir = null,
    ) {
    }

    /**
     * @throws JsonApiProblem
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $request->attributes->set(
            $configuration->getName(),
            $this->map($request, $configuration)
        );

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

    abstract protected function getData(Request $request, ParamConverter $configuration): mixed;

    protected function getMapperBuilder(): MapperBuilder
    {
        $mapperBuilder = new MapperBuilder();

        if (null !== $this->valinorCacheDir) {
            $mapperBuilder = $mapperBuilder->withCacheDir($this->valinorCacheDir);
        }

        return $mapperBuilder;
    }

    /**
     * @throws JsonApiProblem
     */
    protected function map(Request $request, ParamConverter $configuration): mixed
    {
        try {
            return $this->getMapperBuilder()
                ->mapper()
                ->map($this->getClass(), $this->getData($request, $configuration));
        } catch (MappingError $mappingError) {
            $errors = [];
            $flattenedMessages = (new MessagesFlattener($mappingError->node()))->errors();

            foreach ($flattenedMessages as $message) {
                $errors[\str_replace(\sprintf('.%s', $message->name()), '', $message->path())] = $message->__toString();
            }

            throw new JsonApiProblem(
                'Invalid Request',
                'Invalid data provided.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                ['errors' => $errors]
            );
        } catch (\Throwable $exception) {
            throw new JsonApiProblem(
                'Invalid Request',
                $this->debug ? $exception->getMessage() : 'Invalid data provided.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}
