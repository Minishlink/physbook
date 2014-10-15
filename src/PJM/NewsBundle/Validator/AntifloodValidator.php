<?php

namespace PJM\NewsBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AntifloodValidator extends ConstraintValidator
{
    private $request;
    private $em;

    public function __construct(Request $request, EntityManager $em)
    {
        $this->request = $request;
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        // TODO checker si ça fait moins de 10 secondes que l'utilisateur a posté
        /*
        $isFlood = $this->em->getRepository('PJMNewsBundle:Commentaire')
            ->isFlood(10);

        if (!$isFlood) {
            $this->context->addViolation($constraint->message);
        }
        */
    }
}
