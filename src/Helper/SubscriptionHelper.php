<?php

namespace App\Helper;

use App\Entity\SubscriptionType;

class SubscriptionHelper
{
    public function isPaidSubscription(SubscriptionType $subscriptionType): bool
    {
        return $subscriptionType->getId() === SubscriptionType::TYPE_PREMIUM;
    }
}
