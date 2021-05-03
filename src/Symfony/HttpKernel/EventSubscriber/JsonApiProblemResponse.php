<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Symfony\HttpKernel\EventSubscriber;

use MyOnlineStore\ApiTools\Symfony\HttpKernel\Exception\JsonApiProblem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class JsonApiProblemResponse implements EventSubscriberInterface
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof JsonApiProblem) {
            return;
        }

        $status = $exception->getStatusCode();

        $event->setResponse(
            new JsonResponse(
                \array_merge(
                    [
                        'type' => $exception->getType(),
                        'title' => $exception->getTitle(),
                        'detail' => $exception->getDetail(),
                        'status' => $status,
                    ],
                    $exception->getAdditionalInformation(),
                ),
                $status,
            )
        );
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => '__invoke'];
    }
}
