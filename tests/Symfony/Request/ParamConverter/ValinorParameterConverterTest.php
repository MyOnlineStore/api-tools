<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Tests\Symfony\Request\ParamConverter;

use MyOnlineStore\ApiTools\Symfony\HttpKernel\Exception\JsonApiProblem;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

final class ValinorParameterConverterTest extends TestCase
{
    private StubValinorParamConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new StubValinorParamConverter();
    }

    /**
     * @return \Generator<int, array{Request, string}>
     */
    public function validRequestProvider(): \Generator
    {
        yield [new Request(query: ['id' => '34']), '34'];
        yield [new Request(request: ['id' => '45']), '45'];
        yield [new Request(attributes: ['id' => '56']), '56'];
    }

    /**
     * @dataProvider validRequestProvider
     */
    public function testConvert(Request $request, string $expectedValue): void
    {
        $configuration = new ParamConverter(['name' => 'id']);

        self::assertTrue($this->converter->apply($request, $configuration));
        self::assertTrue($request->attributes->has('id'));
        self::assertEquals(StubId::fromString($expectedValue), $request->attributes->get('id'));
    }

    /**
     * @return \Generator<int, array{string, list<string>}>
     */
    public function mappingErrorDataProvider(): \Generator
    {
        yield ['', ['id']];
    }

    /**
     * @dataProvider mappingErrorDataProvider
     *
     * @param list<string> $errorFields
     */
    public function testMappingError(string $requestContent, array $errorFields): void
    {
        $request = new Request(query: ['id' => '']);
        $configuration = new ParamConverter(['name' => 'id']);

        try {
            $this->converter->apply($request, $configuration);

            self::fail('JsonApiProblem should be thrown');
        } catch (JsonApiProblem $exception) {
            self::assertEquals(422, $exception->getStatusCode());
            self::assertEquals(
                [
                    'errors' => [
                        '' => 'Expected a different value than "".',
                    ],
                ],
                $exception->getAdditionalInformation()
            );
        }
    }

    public function testSupports(): void
    {
        self::assertTrue($this->converter->supports(new ParamConverter(['class' => StubId::class])));
        self::assertFalse($this->converter->supports(new ParamConverter(['class' => \stdClass::class])));
    }
}
