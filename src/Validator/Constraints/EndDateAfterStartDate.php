<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class EndDateAfterStartDate extends Constraint
{
    public string $message = 'La data di fine deve essere successiva alla data di inizio.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
