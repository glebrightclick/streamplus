<?php

namespace App\Form;

use App\Validator\NotEmpty;
use App\Validator\UniqueEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserInfoType extends AbstractType
{
    public const string KEY_NAME = 'name';
    public const string KEY_EMAIL = 'email';
    public const string KEY_PHONE = 'phone';
    public const string KEY_SUBSCRIPTION_TYPE = 'subscription_type';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                self::KEY_NAME,
                TextType::class,
                [
                    'label' => 'onboarding.user_info.name',
                    'constraints' => [new NotEmpty()],
                    'required' => false,
                ],
            )
            ->add(
                self::KEY_EMAIL,
                EmailType::class,
                [
                    'label' => 'onboarding.user_info.email',
                    'constraints' => [
                        new NotEmpty(),
                        new Assert\Email(),
                        new UniqueEmail(),
                    ],
                    'required' => false,
                ],
            )
            ->add(
                self::KEY_PHONE,
                TextType::class,
                [
                    'label' => 'onboarding.user_info.phone',
                    'constraints' => [
                        new NotEmpty(),
                        new Assert\Length(['min' => 10]),
                    ],
                    'required' => false,
                ],
            )
            ->add(
                self::KEY_SUBSCRIPTION_TYPE,
                ChoiceType::class,
                [
                    'label' => 'onboarding.user_info.subscription_type',
                    'choices' => $options['subscription_types'],
                    'choice_value' => fn ($choice) => $choice,
                    'choice_label' => fn ($choice, $key, $value) => $key,
                    'expanded' => true,
                    'constraints' => [new NotEmpty()],
                ],
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'subscription_types' => [],
        ]);
    }
}

