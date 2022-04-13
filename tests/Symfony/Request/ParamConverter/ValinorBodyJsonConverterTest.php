<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Tests\Symfony\Request\ParamConverter;

use MyOnlineStore\ApiTools\Symfony\HttpKernel\Exception\JsonApiProblem;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

final class ValinorBodyJsonConverterTest extends TestCase
{
    private StubValinorBodyJsonConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new StubValinorBodyJsonConverter();
    }

    public function testConvert(): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            \json_encode(['id' => '12', 'foo' => 'qux', 'bar' => 12], \JSON_THROW_ON_ERROR)
        );
        $configuration = new ParamConverter(['name' => 'value']);

        self::assertTrue($this->converter->apply($request, $configuration));
        self::assertTrue($request->attributes->has('value'));
        self::assertEquals(
            new StubValue(StubId::fromString('12'), 'qux', 12),
            $request->attributes->get('value')
        );
    }

    /**
     * @return \Generator<int, array{string, list<string>}>
     */
    public function mappingErrorDataProvider(): \Generator
    {
        yield ['', []];
        yield [\json_encode(['foo' => 'qux'], \JSON_THROW_ON_ERROR), ['id', 'bar']];
        yield [\json_encode(['bar' => 12], \JSON_THROW_ON_ERROR), ['id', 'foo']];
        yield [\json_encode(['id' => '12', 'foo' => 'qux'], \JSON_THROW_ON_ERROR), ['bar']];
        yield [\json_encode(['id' => '12', 'bar' => 12], \JSON_THROW_ON_ERROR), ['foo']];
        yield [\json_encode(['foo' => 'qux', 'bar' => 12], \JSON_THROW_ON_ERROR), ['id']];
    }

    /**
     * @dataProvider mappingErrorDataProvider
     *
     * @param list<string> $errorFields
     */
    public function testMappingError(string $requestContent, array $errorFields): void
    {
        $request = new Request([], [], [], [], [], [], $requestContent);
        $configuration = new ParamConverter(['name' => 'value']);

        try {
            $this->converter->apply($request, $configuration);

            self::fail('JsonApiProblem should be thrown');
        } catch (JsonApiProblem $exception) {
            self::assertEquals(422, $exception->getStatusCode());

            $additionalInformation = $exception->getAdditionalInformation();

            if (empty($errorFields)) {
                self::assertArrayNotHasKey('errors', $additionalInformation);
            } else {
                self::assertArrayHasKey('errors', $additionalInformation);
                self::assertIsArray($additionalInformation['errors']);

                foreach ($errorFields as $errorField) {
                    self::assertArrayHasKey($errorField, $additionalInformation['errors']);
                    self::assertIsString($additionalInformation['errors'][$errorField]);
                }
            }
        }
    }

    public function testSupports(): void
    {
        self::assertTrue($this->converter->supports(new ParamConverter(['class' => StubValue::class])));
        self::assertFalse($this->converter->supports(new ParamConverter(['class' => \stdClass::class])));
    }
}
