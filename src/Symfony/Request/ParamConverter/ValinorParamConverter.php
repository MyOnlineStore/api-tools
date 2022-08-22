<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Symfony\Request\ParamConverter;

use CuyZ\Valinor\Cache\FileSystemCache;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage;
use CuyZ\Valinor\MapperBuilder;
use MyOnlineStore\ApiTools\Symfony\HttpKernel\Exception\JsonApiProblem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\InvalidArgumentException;

abstract class ValinorParamConverter implements ParamConverterInterface
{
    public function __construct(
        private bool $debug = false,
        private ?string $valinorCacheDir = null,
    ) {
    }

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
            throw new JsonApiProblem(
                'Invalid Request',
                $this->debug ? $exception->getMessage() : 'Invalid data provided.',
                422
            );
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

    abstract protected function getData(Request $request, ParamConverter $configuration): mixed;

    protected function getMapperBuilder(): MapperBuilder
    {
        $mapperBuilder = (new MapperBuilder())
            ->filterExceptions(static function (\Throwable $exception) {
                if ($exception instanceof InvalidArgumentException) {
                    return ThrowableMessage::from($exception);
                }

                throw $exception;
            });

        if (null !== $this->valinorCacheDir) {
            $mapperBuilder = $mapperBuilder->withCache(new FileSystemCache($this->valinorCacheDir));
        }

        return $mapperBuilder;
    }
}
