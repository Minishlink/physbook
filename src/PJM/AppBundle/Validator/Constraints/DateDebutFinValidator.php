<?php

namespace PJM\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DateDebutFinValidator extends ConstraintValidator
{
    public function validate($dateDebutFin, Constraint $constraint)
    {
        if ($dateDebutFin->getDateFin() < $dateDebutFin->getDateDebut()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('dateFin')
                ->addViolation();
        }
    }
}
