<?php

namespace App\Tests\Unit\src\Validator;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Validator\UniqueEmail;
use App\Validator\UniqueEmailValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class UniqueEmailValidatorTest extends TestCase
{
    private ExecutionContext $context;
    private UniqueEmailValidator $validator;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->validator = new UniqueEmailValidator($this->userRepository);
        $this->validator->initialize($this->context);
    }

    public function testValidEmail(): void
    {
        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'valid@example.com'])
            ->willReturn(null);

        $this->context->expects($this->never())->method('buildViolation');

        $constraint = new UniqueEmail(['message' => 'This email is already used.']);
        $this->validator->validate('valid@example.com', $constraint);
    }

    public function testInvalidEmail(): void
    {
        $user = $this->createMock(User::class);

        $this->userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'duplicate@example.com'])
            ->willReturn($user);

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder->expects($this->once())->method('setParameter')->with('{value}', 'duplicate@example.com')->willReturnSelf();
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with('This email is already used.')
            ->willReturn($violationBuilder);

        $constraint = new UniqueEmail(['message' => 'This email is already used.']);
        $this->validator->validate('duplicate@example.com', $constraint);
    }

    public function testNullEmail(): void
    {
        $this->userRepository->expects($this->never())->method('findOneBy');
        $this->context->expects($this->never())->method('buildViolation');

        $constraint = new UniqueEmail(['message' => 'This email is already used.']);
        $this->validator->validate(null, $constraint);
    }
}
