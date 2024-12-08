<?php

namespace App\Validator;

use App\Form\AddressInfoType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CountrySpecificPostalCodeValidator extends ConstraintValidator
{
    /**
     * @var CountrySpecificPostalCode $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!is_array($value) || !isset($value[AddressInfoType::KEY_COUNTRY])) {
            return;
        }

        $country = $value[AddressInfoType::KEY_COUNTRY];
        $postalCode = $value[AddressInfoType::KEY_POSTAL_CODE] ?? null;

        switch ($country) {
            case 'US':
                // Validate US postal codes (e.g., 5 digits)
                if (!preg_match('/^\d{5}$/', $postalCode)) {
                    $this->context->buildViolation('validator.country_specific_postal_code.message.us')
                        ->atPath(AddressInfoType::KEY_POSTAL_CODE)
                        ->addViolation();
                }
                break;

            case 'CA':
                if (!preg_match('/^[A-Za-z]\d[A-Za-z] \d[A-Za-z]\d$/', $postalCode)) {
                    $this->context->buildViolation('validator.country_specific_postal_code.message.ca')
                        ->atPath(AddressInfoType::KEY_POSTAL_CODE)
                        ->addViolation();
                }
                break;

            case 'UK':
                // Validate UK postal codes
                if (!preg_match('/^[A-Z]{1,2}\d[A-Z\d]? \d[A-Z]{2}$/i', $postalCode)) {
                    $this->context->buildViolation('validator.country_specific_postal_code.message.uk')
                        ->atPath(AddressInfoType::KEY_POSTAL_CODE)
                        ->addViolation();
                }
                break;

            case 'DE':
                // Validate German postal codes (5 digits)
                if (!preg_match('/^\d{5}$/', $postalCode)) {
                    $this->context->buildViolation('validator.country_specific_postal_code.message.de')
                        ->atPath(AddressInfoType::KEY_POSTAL_CODE)
                        ->addViolation();
                }
                break;
        }
    }
}
