<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Tests\Symfony\Request\ParamConverter;

use MyOnlineStore\ApiTools\Symfony\HttpKernel\Exception\JsonApiProblem;
use PHPUnit\Framework\MockObject\MockObject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ValidatingValinorParamConverterTest extends KernelTestCase
{
    /** @var MockObject&ValidatorInterface */
    private $validator;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->converter = new StubValidatingValinorParamConverter($this->validator);
    }

    public function testConvert(): void
    {
        $this->validator->expects(self::once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $request = new Request(
            content: \json_encode(['id' => '12', 'foo' => 'qux', 'bar' => 12], \JSON_THROW_ON_ERROR)
        );
        $configuration = new ParamConverter(['name' => 'value']);

        self::assertTrue($this->converter->apply($request, $configuration));
        self::assertTrue($request->attributes->has('value'));
        self::assertEquals(
            new StubValue(StubId::fromString('12'), 'qux', 12),
            $request->attributes->get('value')
        );
    }

    public function testValidationError(): void
    {
        $this->validator->expects(self::once())
            ->method('validate')
            ->willReturn(
                new ConstraintViolationList(
                    [
                        new ConstraintViolation('foo', null, [], '', '', null),
                        new ConstraintViolation('bar', null, [], '', 'id', 12),
                    ]
                )
            );

        $request = new Request(
            content: \json_encode(['id' => '12', 'foo' => 'qux', 'bar' => 12], \JSON_THROW_ON_ERROR)
        );
        $configuration = new ParamConverter(['name' => 'value']);

        try {
            $this->converter->apply($request, $configuration);

            self::fail('JsonApiProblem should be thrown');
        } catch (JsonApiProblem $exception) {
            self::assertEquals(422, $exception->getStatusCode());
            self::assertEquals(
                [
                    'errors' => [
                        0 => 'foo',
                        'id' => 'bar',
                    ],
                ],
                $exception->getAdditionalInformation()
            );
        }
    }
}
