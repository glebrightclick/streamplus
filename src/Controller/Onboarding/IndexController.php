<?php

namespace App\Controller\Onboarding;

use App\Entity\Address;
use App\Entity\CreditCardInfo;
use App\Entity\Subscription;
use App\Entity\SubscriptionType;
use App\Entity\User;
use App\Form\ConfirmationType;
use App\Form\UserInfoType;
use App\Form\AddressInfoType;
use App\Form\CreditCardInfoType;
use App\Helper\SubscriptionHelper;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    private const string SESSION_KEY = 'onboarding_user';

    #[Route(path: '/onboarding', name: 'onboarding')]
    public function index(): Response {
        return $this->redirectToRoute('onboarding_user_info');
    }

    /**
     * @return int[] available subscription types
     */
    public function getAvailableSubscriptionTypes(): array
    {
        return [SubscriptionType::TYPE_FREE, SubscriptionType::TYPE_PREMIUM];
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param int[] $list
     * @return SubscriptionType[]
     */
    public function getSubscriptionTypes(EntityManagerInterface $entityManager, array $list): array
    {
        return $entityManager->getRepository(SubscriptionType::class)->findBy(['id' => $list]);
    }

    private function getSessionUser(SessionInterface $session): User
    {
        return $session->get(self::SESSION_KEY) ?? new User();
    }

    /**
     * setting updated version of a user to session
     * @param SessionInterface $session
     * @param User $user
     * @return void
     */
    private function setSessionUser(SessionInterface $session, User $user): void
    {
        $session->set(self::SESSION_KEY, $user);
    }

    private function eraseSessionUser(SessionInterface $session): void
    {
        $session->remove(self::SESSION_KEY);
    }

    #[Route(path: '/onboarding/user_info', name: 'onboarding_user_info')]
    public function userInfo(
        Request $request,
        SessionInterface $session,
        SubscriptionHelper $helper,
        EntityManagerInterface $entityManager,
    ): Response {
        // $this->eraseSessionUser($session);
        // fetch subscription types from database to present during onboarding
        $availableSubscriptionTypes = $this->getAvailableSubscriptionTypes();
        // if subscription types aren't found - throw server error
        if (!$subscriptionTypes = $this->getSubscriptionTypes($entityManager, $availableSubscriptionTypes)) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }
        $choices = [];
        $types = [];
        foreach ($subscriptionTypes as $subscriptionType) {
            $choices[$subscriptionType->getName()] = $subscriptionType->getId();
            $types[$subscriptionType->getId()] = $subscriptionType;
        }

        $user = $this->getSessionUser($session);

        $now = new DateTimeImmutable('now');
        $activeSubscription = $user->getActiveSubscription($now);

        $form = $this->createForm(
            UserInfoType::class,
            [
                UserInfoType::KEY_NAME => $user->getName(),
                UserInfoType::KEY_EMAIL => $user->getEmail(),
                UserInfoType::KEY_PHONE => $user->getPhone(),
                UserInfoType::KEY_SUBSCRIPTION_TYPE => $activeSubscription?->getSubscriptionType()->getId(),
            ],
            ['subscription_types' => $choices],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user->setName($data[UserInfoType::KEY_NAME])
                ->setEmail($data[UserInfoType::KEY_EMAIL])
                ->setPhone($data[UserInfoType::KEY_PHONE]);
            if (!$activeSubscription || $activeSubscription->getId() != $data[UserInfoType::KEY_SUBSCRIPTION_TYPE]) {
                // if user came back to first onboarding screen and chooses other type of subscription, remove it from object
                if ($activeSubscription) {
                    $user->removeSubscription($activeSubscription);
                }

                $subscription = new Subscription();
                $subscription->setUser($user)
                    ->setSubscriptionType($types[$data[UserInfoType::KEY_SUBSCRIPTION_TYPE]])
                    ->setDateStart($now)
                    ->setDateEnd(null);
                $user->addSubscription($subscription);
                // save new subscription
                $activeSubscription = $subscription;
            }

            // if credit card info is set and user chooses free subscription, unset credit card info
            if (!$helper->isPaidSubscription($activeSubscription->getSubscriptionType()) && !$user->getCreditCardInfos()->isEmpty()) {
                foreach ($user->getCreditCardInfos() as $creditCardInfo) {
                    $user->removeCreditCardInfo($creditCardInfo);
                }
            }

            // save onboarding progress
            $this->setSessionUser($session, $user);

            return $this->redirectToRoute('onboarding_address_info');
        }

        return $this->render('onboarding/user_info.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/onboarding/address_info', name: 'onboarding_address_info')]
    public function addressInfo(
        Request $request,
        SessionInterface $session,
        SubscriptionHelper $helper,
    ): Response {
        $user = $this->getSessionUser($session);
        // if user doesn't have email, first step isn't completed
        if (!$user->getEmail()) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        // if onboarding progress already has an address, use it, otherwise create new one
        if ($user->getAddresses()->isEmpty()) {
            $user->addAddress(new Address());
        }

        $form = $this->createForm(
            AddressInfoType::class,
            [
                AddressInfoType::KEY_ADDRESS_LINE_1 => $user->getAddresses()->first()->getAddressLine1(),
                AddressInfoType::KEY_ADDRESS_LINE_2 => $user->getAddresses()->first()->getAddressLine2(),
                AddressInfoType::KEY_CITY => $user->getAddresses()->first()->getCity(),
                AddressInfoType::KEY_POSTAL_CODE => $user->getAddresses()->first()->getPostalCode(),
                AddressInfoType::KEY_STATE => $user->getAddresses()->first()->getState(),
                AddressInfoType::KEY_COUNTRY => $user->getAddresses()->first()->getCountry(),
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user->getAddresses()->first()->setAddressLine1($data[AddressInfoType::KEY_ADDRESS_LINE_1])
                ->setAddressLine2($data[AddressInfoType::KEY_ADDRESS_LINE_2] ?? '')
                ->setCity($data[AddressInfoType::KEY_CITY])
                ->setPostalCode($data[AddressInfoType::KEY_POSTAL_CODE])
                ->setState($data[AddressInfoType::KEY_STATE] ?? '')
                ->setCountry($data[AddressInfoType::KEY_COUNTRY]);

            // save onboarding progress
            $this->setSessionUser($session, $user);

            // redirect from address info to credit card info only if subscription type is premium
            if ($helper->isPaidSubscription($user->getActiveSubscription(new DateTimeImmutable('now'))->getSubscriptionType())) {
                return $this->redirectToRoute('onboarding_credit_card_info');
            }

            return $this->redirectToRoute('onboarding_confirmation');
        }

        return $this->render(
            'onboarding/address_info.html.twig',
            [
                'form' => $form->createView(),
                'back_url' => $this->generateUrl('onboarding_user_info'),
            ],
        );
    }

    #[Route(path: '/onboarding/credit_card_info', name: 'onboarding_credit_card_info')]
    public function creditCardInfo(
        Request $request,
        SessionInterface $session,
        SubscriptionHelper $helper,
    ): Response {
        $user = $this->getSessionUser($session);
        // if user doesn't have address, second step isn't completed
        if ($user->getAddresses()->isEmpty()) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $now = new DateTimeImmutable('now');
        // if user choose unpaid subscription, redirect him to confirmation
        if (!$helper->isPaidSubscription($user->getActiveSubscription($now)->getSubscriptionType())) {
            return $this->redirectToRoute('onboarding_confirmation');
        }

        // if onboarding progress already has a credit card info, use it, otherwise create new one
        if ($user->getCreditCardInfos()->isEmpty()) {
            $user->addCreditCardInfo(new CreditCardInfo());
        }

        $form = $this->createForm(
            CreditCardInfoType::class,
            [
                CreditCardInfoType::KEY_CREDIT_CARD_NUMBER => $user->getCreditCardInfos()->first()->getCreditCardNumber(),
                CreditCardInfoType::KEY_EXPIRATION_MONTH => $user->getCreditCardInfos()->first()->getExpirationMonth() ?? $now->format('m'),
                CreditCardInfoType::KEY_EXPIRATION_YEAR => $user->getCreditCardInfos()->first()->getExpirationYear() ?? $now->format('Y'),
                CreditCardInfoType::KEY_CVV => $user->getCreditCardInfos()->first()->getCvv(),
            ],
            [
                'month_from' => $now->format('m'),
                'year_from' => (int)$now->format('Y'),
                'month_to' => $now->format('m'),
                'year_to' => (int)$now->format('Y') + 10,
            ],
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user->getCreditCardInfos()->first()->setCreditCardNumber($data[CreditCardInfoType::KEY_CREDIT_CARD_NUMBER])
                ->setExpirationMonth($data[CreditCardInfoType::KEY_EXPIRATION_MONTH])
                ->setExpirationYear($data[CreditCardInfoType::KEY_EXPIRATION_YEAR])
                ->setCvv($data[CreditCardInfoType::KEY_CVV]);

            // save onboarding progress
            $this->setSessionUser($session, $user);

            return $this->redirectToRoute('onboarding_confirmation');
        }

        return $this->render(
            'onboarding/credit_card_info.html.twig',
            [
                'form' => $form->createView(),
                'back_url' => $this->generateUrl('onboarding_address_info'),
            ],
        );
    }

    #[Route(path: '/onboarding/confirmation', name: 'onboarding_confirmation')]
    public function confirmation(
        Request $request,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        SubscriptionHelper $helper,
    ): Response {
        $user = $this->getSessionUser($session);
        // if user doesn't have address, second step isn't completed
        if ($user->getAddresses()->isEmpty()) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $form = $this->createForm(ConfirmationType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // getting existing subscription type from db so entity manager sees existing object and doesn't want to add new one
            $repository = $entityManager->getRepository(SubscriptionType::class);
            $type = $repository->findOneBy(['id' => $user->getSubscriptions()->first()->getSubscriptionType()->getId()]);
            $user->getSubscriptions()->first()->setSubscriptionType($type);

            // save user to relation database
            $entityManager->persist($user->getSubscriptions()->first());
            $entityManager->persist($user->getAddresses()->first());
            if ($user->getCreditCardInfos()->first()) {
                $entityManager->persist($user->getCreditCardInfos()->first());
            }
            $entityManager->persist($user);
            $entityManager->flush();

            // clear session cache
            $this->eraseSessionUser($session);

            return $this->render('onboarding/complete.html.twig', [
                'user' => $user,
                'activeSubscription' => $user->getActiveSubscription(new DateTimeImmutable('now')),
                'addressInfo' => $user->getAddresses()->first(),
                'creditCardInfo' => $user->getCreditCardInfos()->first(),
            ]);
        }

        $backButtonRoute = 'onboarding_address_info';
        if ($helper->isPaidSubscription($user->getActiveSubscription(new DateTimeImmutable('now'))->getSubscriptionType())) {
            $backButtonRoute = 'onboarding_credit_card_info';
        }

        return $this->render(
            'onboarding/confirmation.html.twig',
            [
                'form' => $form->createView(),
                'back_url' => $this->generateUrl($backButtonRoute),
                'user' => $user,
                'activeSubscription' => $user->getActiveSubscription(new DateTimeImmutable('now')),
                'addressInfo' => $user->getAddresses()->first(),
                'creditCardInfo' => $user->getCreditCardInfos()->first(),
            ],
        );
    }
}

