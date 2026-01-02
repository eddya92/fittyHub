<?php

namespace App\Validator\Constraints;

use App\Domain\Medical\Repository\MedicalCertificateRepositoryInterface;
use App\Domain\User\Entity\User;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidMedicalCertificateValidator extends ConstraintValidator
{
    public function __construct(
        private MedicalCertificateRepositoryInterface $certificateRepository
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
