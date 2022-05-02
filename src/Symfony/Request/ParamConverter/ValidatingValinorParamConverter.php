<?php
declare(strict_types=1);

namespace MyOnlineStore\ApiTools\Symfony\Request\ParamConverter;

use MyOnlineStore\ApiTools\Symfony\HttpKernel\Exception\JsonApiProblem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class ValidatingValinorParamConverter extends ValinorParamConverter
{
    public function __construct(protected ValidatorInterface $validator)
    {
    }

    protected function map(Request $request, ParamConverter $configuration): mixed
    {
        $mapped = parent::map($request, $configuration);

        $violations = $this->validator->validate($mapped);

        if (0 === \count($violations)) {
            return $mapped;
        }

        $errors = [];

        foreach ($violations as $violation) {
            \assert($violation instanceof ConstraintViolation);

            if (!$scope = $violation->getPropertyPath()) {
                $errors[] = $violation->getMessage();
            } else {
                $errors[$scope] = $violation->getMessage();
            }
        }

        throw new JsonApiProblem(
            'Invalid Request',
            'Invalid data provided.',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            ['errors' => $errors]
        );
    }
}
