<?php

namespace App\Tests\Unit\src\Validator;

use App\Form\AddressInfoType;
use App\Validator\CountrySpecificPostalCodeValidator;
use App\Validator\CountrySpecificPostalCode;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;
use PHPUnit\Framework\TestCase;

class CountrySpecificPostalCodeValidatorTest extends TestCase
{
    private ExecutionContext $context;
    private CountrySpecificPostalCodeValidator $validator;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->validator = new CountrySpecificPostalCodeValidator();
        $this->validator->initialize($this->context);
    }

    public function testValidUSPostalCode(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $constraint = new CountrySpecificPostalCode();
        $value = [
            AddressInfoType::KEY_COUNTRY => 'US',
            AddressInfoType::KEY_POSTAL_CODE => '12345',
        ];

        $this->validator->validate($value, $constraint);
    }

    public function testInvalidUSPostalCode(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())
            ->method('atPath')
            ->with(AddressInfoType::KEY_POSTAL_CODE)
            ->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('validator.country_specific_postal_code.message.us')
            ->willReturn($violationBuilder);

        $constraint = new CountrySpecificPostalCode();
        $value = [
            AddressInfoType::KEY_COUNTRY => 'US',
            AddressInfoType::KEY_POSTAL_CODE => '1234', // Invalid postal code
        ];

        $this->validator->validate($value, $constraint);
    }

    public function testValidCAPostalCode(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $constraint = new CountrySpecificPostalCode();
        $value = [
            AddressInfoType::KEY_COUNTRY => 'CA',
            AddressInfoType::KEY_POSTAL_CODE => 'K1A 0B1',
        ];

        $this->validator->validate($value, $constraint);
    }

    public function testInvalidCAPostalCode(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())
            ->method('atPath')
            ->with(AddressInfoType::KEY_POSTAL_CODE)
            ->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('validator.country_specific_postal_code.message.ca')
            ->willReturn($violationBuilder);

        $constraint = new CountrySpecificPostalCode();
        $value = [
            AddressInfoType::KEY_COUNTRY => 'CA',
            AddressInfoType::KEY_POSTAL_CODE => '1234', // Invalid postal code
        ];

        $this->validator->validate($value, $constraint);
    }

    public function testUnsupportedCountry(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $constraint = new CountrySpecificPostalCode();
        $value = [
            AddressInfoType::KEY_COUNTRY => 'FR',
            AddressInfoType::KEY_POSTAL_CODE => '75008',
        ];

        $this->validator->validate($value, $constraint);
    }
}
