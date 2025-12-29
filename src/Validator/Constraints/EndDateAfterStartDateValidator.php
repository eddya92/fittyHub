<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class EndDateAfterStartDateValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EndDateAfterStartDate) {
            throw new UnexpectedTypeException($constraint, EndDateAfterStartDate::class);
        }

        if (null === $value) {
            return;
        }

        // Assumiamo che l'oggetto abbia metodi getStartDate() e getEndDate()
        if (!method_exists($value, 'getStartDate') || !method_exists($value, 'getEndDate')) {
            return;
        }

        $startDate = $value->getStartDate();
        $endDate = $value->getEndDate();

        if ($startDate && $endDate && $endDate <= $startDate) {
            $this->context->buildViolation($constraint->message)
                ->atPath('endDate')
                ->addViolation();
        }
    }
}
