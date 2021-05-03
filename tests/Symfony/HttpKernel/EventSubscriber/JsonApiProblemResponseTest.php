<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Tests\Symfony\HttpKernel\EventSubscriber;

use MyOnlineStore\ApiTools\Symfony\HttpKernel\EventSubscriber\JsonApiProblemResponse;
use MyOnlineStore\ApiTools\Symfony\HttpKernel\Exception\JsonApiProblem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class JsonApiProblemResponseTest extends TestCase
{
    private JsonApiProblemResponse $jsonApiProblemResponse;

    protected function setUp(): void
    {
        $this->jsonApiProblemResponse = new JsonApiProblemResponse();
    }

    public function testInvokeWithoutJsonApiProblem(): void
    {
        $exceptionEvent = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $this->createMock(Request::class),
            0,
            $this->createMock(\Throwable::class)
        );

        $response = $exceptionEvent->getResponse();

        ($this->jsonApiProblemResponse)($exceptionEvent);

        self::assertSame($response, $exceptionEvent->getResponse());
    }

    public function testInvokeWithJsonApiProblem(): void
    {
        $jsonApiProblem = new JsonApiProblem(
            'title',
            'detail',
            550,
            ['foo' => 'bar'],
            'type',
        );

        $exceptionEvent = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $this->createMock(Request::class),
            0,
            $jsonApiProblem
        );

        $response = $exceptionEvent->getResponse();

        ($this->jsonApiProblemResponse)($exceptionEvent);

        self::assertNotSame($response, $exceptionEvent->getResponse());

        self::assertEquals(
            new JsonResponse(
                \array_merge(
                    [
                        'type' => 'type',
                        'title' => 'title',
                        'detail' => 'detail',
                        'status' => 550,
                    ],
                    ['foo' => 'bar'],
                ),
                550,
            ),
            $exceptionEvent->getResponse()
        );
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertEquals(
            [KernelEvents::EXCEPTION => '__invoke'],
            $this->jsonApiProblemResponse::getSubscribedEvents(),
        );
    }
}
