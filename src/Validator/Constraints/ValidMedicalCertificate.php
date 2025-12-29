<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ValidMedicalCertificate extends Constraint
{
    public string $message = 'Devi avere un certificato medico valido per procedere.';
    public string $mode = 'strict';

    public function __construct(mixed $options = null, array $groups = null, mixed $payload = null)
    {
        parent::__construct($options, $groups, $payload);
    }
}
