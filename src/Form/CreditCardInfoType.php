<?php

namespace App\Form;

use App\Validator\NotEmpty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CreditCardInfoType extends AbstractType
{
    public const string KEY_CREDIT_CARD_NUMBER = 'credit_card_number';
    public const string KEY_EXPIRATION_MONTH = 'credit_card_expiration_month';
    public const string KEY_EXPIRATION_YEAR = 'credit_card_expiration_year';
    public const string KEY_CVV = 'cvv';

    // pairs (monthFrom, yearFrom) and (monthTo, yearTo) are controlling
    // expiration fields - setting maximum and minimum values
    private int $monthFrom;
    private int $yearFrom;

    private int $monthTo;
    private int $yearTo;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // remember passed options to use them in validation method
        $this->monthFrom = $options['month_from'];
        $this->yearFrom = $options['year_from'];
        $this->monthTo = $options['month_to'];
        $this->yearTo = $options['year_to'];

        $builder
            ->add(
                self::KEY_CREDIT_CARD_NUMBER,
                TextType::class,
                [
                    'label' => "onboarding.credit_card_info.credit_card_number",
                    'constraints' => [
                        new Assert\CardScheme([
                            'schemes' => [Assert\CardScheme::VISA, Assert\CardScheme::MASTERCARD],
                            'message' => 'validator.credit_card_number.error.invalid_number',
                        ]),
                        new NotEmpty(),
                    ],
                    'required' => false,
                ],
            )
            ->add(
                self::KEY_EXPIRATION_MONTH,
                ChoiceType::class,
                [
                    'label' => 'onboarding.credit_card_info.expiration_month',
                    'choices' => array_combine(range(1, 12), range(1, 12)),
                    'constraints' => [
                        new NotEmpty(),
                    ],
                ],
            )
            ->add(
                self::KEY_EXPIRATION_YEAR,
                ChoiceType::class,
                [
                    'label' => 'onboarding.credit_card_info.expiration_year',
                    'choices' => array_combine(range($this->yearFrom, $this->yearTo), range($this->yearFrom, $this->yearTo)),
                    'constraints' => [
                        new NotEmpty(),
                    ],
                ],
            )
            ->add(
                self::KEY_CVV,
                PasswordType::class,
                [
                    'label' => 'onboarding.credit_card_info.cvv',
                    'constraints' => [
                        new NotEmpty(),
                        new Assert\Length([
                            'min' => 3,
                            'max' => 3,
                            'exactMessage' => 'validator.cvv.error.three_digits',
                        ]),
                        new Assert\Regex([
                            'pattern' => '/^\d+$/',
                            'message' => 'validator.cvv.error.only_digits',
                        ]),
                    ],
                    'required' => false,
                ],
            )
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validateExpirationDate']);;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'month_from' => 0,
            'year_from' => 0,
            'month_to' => 0,
            'year_to' => 0,
        ]);
    }

    public function validateExpirationDate(FormEvent $event): void
    {
        $form = $event->getForm();
        $data = $form->getData();

        $month = $data[self::KEY_EXPIRATION_MONTH] ?? null;
        $year = $data[self::KEY_EXPIRATION_YEAR] ?? null;

        if ($month && $year) {
            // check if expiration date is between pairs (monthFrom, yearFrom) and (monthTo, yearTo)
            if ($year < $this->yearFrom || $year > $this->yearTo || $year == $this->yearFrom && $month < $this->monthFrom || $year == $this->yearTo && $month > $this->monthTo) {
                $form->get(self::KEY_EXPIRATION_MONTH)->addError(new FormError('validator.expiration_month.error.invalid_date'));
                // highlight year for better ui
                $form->get(self::KEY_EXPIRATION_YEAR)->addError(new FormError(''));
            }
        }
    }
}

