<?php

namespace App\Tests\Unit\src\Validator;

use App\Form\AddressInfoType;
use App\Validator\CountrySpecificStateValidator;
use App\Validator\CountrySpecificState;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;
use PHPUnit\Framework\TestCase;

class CountrySpecificStateValidatorTest extends TestCase
{
    private ExecutionContext $context;
    private CountrySpecificStateValidator $validator;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->validator = new CountrySpecificStateValidator();
        $this->validator->initialize($this->context);
    }

    public function testValidUSState(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $constraint = new CountrySpecificState();
        $value = [
            AddressInfoType::KEY_COUNTRY => 'US',
            AddressInfoType::KEY_STATE => 'California',
        ];

        $this->validator->validate($value, $constraint);
    }

    public function testInvalidUSState(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())->method('atPath')->with(AddressInfoType::KEY_STATE)->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('validator.country_specific_state.message.us')
            ->willReturn($violationBuilder);

        $constraint = new CountrySpecificState();
        $value = [
            AddressInfoType::KEY_COUNTRY => 'US',
            AddressInfoType::KEY_STATE => '', // Invalid state
        ];

        $this->validator->validate($value, $constraint);
    }

    public function testValidCAState(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $constraint = new CountrySpecificState();
        $value = [
            AddressInfoType::KEY_COUNTRY => 'CA',
            AddressInfoType::KEY_STATE => 'Ontario',
        ];

        $this->validator->validate($value, $constraint);
    }

    public function testInvalidCAState(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())->method('atPath')->with(AddressInfoType::KEY_STATE)->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('validator.country_specific_state.message.ca')
            ->willReturn($violationBuilder);

        $constraint = new CountrySpecificState();
        $value = [
            AddressInfoType::KEY_COUNTRY => 'CA',
            AddressInfoType::KEY_STATE => '', // Invalid state
        ];

        $this->validator->validate($value, $constraint);
    }

    public function testUnsupportedCountry(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $constraint = new CountrySpecificState();
        $value = [
            AddressInfoType::KEY_COUNTRY => 'FR', // Unsupported country
            AddressInfoType::KEY_STATE => 'Île-de-France',
        ];

        $this->validator->validate($value, $constraint);
    }

    public function testMissingCountryKey(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $constraint = new CountrySpecificState();
        $value = [
            AddressInfoType::KEY_STATE => 'California', // Missing country key
        ];

        $this->validator->validate($value, $constraint);
    }
}
