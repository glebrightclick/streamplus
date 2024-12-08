<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotEmptyValidator extends ConstraintValidator
{
    /**
     * @param NotEmpty $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
