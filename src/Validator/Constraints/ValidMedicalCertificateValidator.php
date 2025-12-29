<?php

namespace App\Validator\Constraints;

use App\Domain\Medical\Repository\MedicalCertificateRepository;
use App\Domain\User\Entity\User;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidMedicalCertificateValidator extends ConstraintValidator
{
    public function __construct(
        private MedicalCertificateRepository $certificateRepository
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidMedicalCertificate) {
            throw new UnexpectedTypeException($constraint, ValidMedicalCertificate::class);
        }

        if (!$value instanceof User) {
            return;
        }

        $certificate = $this->certificateRepository->findValidCertificateForUser($value);

        if (!$certificate) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
