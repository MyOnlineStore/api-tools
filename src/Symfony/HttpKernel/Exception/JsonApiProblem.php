<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Symfony\HttpKernel\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class JsonApiProblem extends HttpException
{
    /** @var array<array-key, mixed> */
    private array $additionalInformation;
    private string $detail;
    private string $title;
    private string $type;

    /**
     * @param array<array-key, mixed> $additionalInformation
     */
    public function __construct(
        string $title,
        string $detail,
        int $statusCode = 500,
        array $additionalInformation = [],
        ?string $type = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            $statusCode,
            $title,
            $previous,
            ['Content-Type' => 'application/json'],
            $statusCode,
        );

        $this->title = $title;
        $this->detail = $detail;
        $this->additionalInformation = $additionalInformation;
        $this->type = $type ?? 'https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html';
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getAdditionalInformation(): array
    {
        return $this->additionalInformation;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
