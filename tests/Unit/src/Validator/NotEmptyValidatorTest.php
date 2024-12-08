<?php

namespace App\Tests\Unit\src\Validator;

use App\Validator\NotEmpty;
use App\Validator\NotEmptyValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class NotEmptyValidatorTest extends TestCase
{
    private ExecutionContext $context;
    private NotEmptyValidator $validator;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->validator = new NotEmptyValidator();
        $this->validator->initialize($this->context);
    }

    public function testValidValue(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $constraint = new NotEmpty(['message' => 'This value should not be empty.']);
        $this->validator->validate('Some value', $constraint);
    }

    public function testInvalidValue(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('This value should not be empty.')
            ->willReturn($violationBuilder);

        $constraint = new NotEmpty(['message' => 'This value should not be empty.']);
        $this->validator->validate('', $constraint);
    }

    public function testWhitespaceValue(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('This value should not be empty.')
            ->willReturn($violationBuilder);

        $constraint = new NotEmpty(['message' => 'This value should not be empty.']);
        $this->validator->validate('   ', $constraint);
    }
}
