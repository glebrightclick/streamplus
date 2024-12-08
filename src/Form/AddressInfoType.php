<?php

namespace App\Form;

use App\Validator\CountrySpecificPostalCode;
use App\Validator\CountrySpecificState;
use App\Validator\NotEmpty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddressInfoType extends AbstractType
{
    private ValidatorInterface $validator;

    public const string KEY_ADDRESS_LINE_1 = 'address_line_1';
    public const string KEY_ADDRESS_LINE_2 = 'address_line_2';
    public const string KEY_CITY = 'address_city';
    public const string KEY_POSTAL_CODE = 'address_postal_code';
    public const string KEY_STATE = 'address_state';
    public const string KEY_COUNTRY = 'address_country';

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                self::KEY_ADDRESS_LINE_1,
                TextType::class,
                [
                    'label' => 'onboarding.address_info.address_line_1',
                    'constraints' => [new NotEmpty()],
                    'required' => false,
                ],
            )
            ->add(
                self::KEY_ADDRESS_LINE_2,
                TextType::class,
                [
                    'label' => 'onboarding.address_info.address_line_2',
                    'required' => false,
                ],
            )
            ->add(
                self::KEY_CITY,
                TextType::class,
                [
                    'label' => 'onboarding.address_info.city',
                    'constraints' => [new NotEmpty()],
                    'required' => false,
                ],
            )
            ->add(
                self::KEY_POSTAL_CODE,
                TextType::class,
                [
                    'label' => 'onboarding.address_info.postal_code',
                    'constraints' => [new NotEmpty()],
                    'required' => false,
                ],
            )
            ->add(
                self::KEY_STATE,
                TextType::class,
                [
                    'label' => 'onboarding.address_info.state',
                    'required' => false,
                ],
            )
            ->add(
                self::KEY_COUNTRY,
                CountryType::class,
                [
                    'label' => 'onboarding.address_info.country',
                    'constraints' => [new NotEmpty()],
                    'required' => false,
                ],
            )
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validateAddress']);
    }

    public function validateAddress(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $form->getData();

        $violations = $this->validator->validate($data, new CountrySpecificPostalCode());
        foreach ($violations as $violation) {
            $form->get(self::KEY_POSTAL_CODE)->addError(new FormError($violation->getMessage()));
        }

        $violations = $this->validator->validate($data, new CountrySpecificState());
        foreach ($violations as $violation) {
            $form->get(self::KEY_STATE)->addError(new FormError($violation->getMessage()));
        }
    }
}

