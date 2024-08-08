<?php

declare(strict_types=1);

namespace App\Application\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Exception;

class ValidationException extends Exception
{
    private ConstraintViolationListInterface $violations;

    public function __construct(ConstraintViolationListInterface $violations)
    {
        parent::__construct('Validation failed.');
        $this->violations = $violations;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}