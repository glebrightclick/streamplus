<?php

namespace App\Validator;

use App\Form\AddressInfoType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CountrySpecificStateValidator extends ConstraintValidator
{
    /**
     * @var CountrySpecificState $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!is_array($value) || !isset($value[AddressInfoType::KEY_COUNTRY])) {
            return;
        }

        $country = $value[AddressInfoType::KEY_COUNTRY];
        $state = $value[AddressInfoType::KEY_STATE] ?? null;

        switch ($country) {
            case 'US':
                if (empty($state)) {
                    $this->context->buildViolation('validator.country_specific_state.message.us')
                        ->atPath(AddressInfoType::KEY_STATE)
                        ->addViolation();
                }
                break;

            case 'CA':
                if (empty($state)) {
                    $this->context->buildViolation('validator.country_specific_state.message.ca')
                        ->atPath(AddressInfoType::KEY_STATE)
                        ->addViolation();
                }
                break;
        }
    }
}
