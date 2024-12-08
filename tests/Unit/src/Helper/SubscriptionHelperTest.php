<?php

namespace App\Tests\Unit\src\Helper;

use App\Entity\SubscriptionType;
use App\Helper\SubscriptionHelper;
use PHPUnit\Framework\TestCase;

class SubscriptionHelperTest extends TestCase
{
    private SubscriptionHelper $subscriptionHelper;

    protected function setUp(): void
    {
        $this->subscriptionHelper = new SubscriptionHelper();
    }

    public function testIsPaidSubscriptionReturnsTrueForPremium(): void
    {
        $subscriptionType = $this->createMock(SubscriptionType::class);
        $subscriptionType->expects($this->once())
            ->method('getId')
            ->willReturn(SubscriptionType::TYPE_PREMIUM);

        $result = $this->subscriptionHelper->isPaidSubscription($subscriptionType);

        $this->assertTrue($result, 'isPaidSubscription should return true for premium subscription types.');
    }

    public function testIsPaidSubscriptionReturnsFalseForFree(): void
    {
        $subscriptionType = $this->createMock(SubscriptionType::class);
        $subscriptionType->expects($this->once())
            ->method('getId')
            ->willReturn(SubscriptionType::TYPE_FREE);

        $result = $this->subscriptionHelper->isPaidSubscription($subscriptionType);

        $this->assertFalse($result, 'isPaidSubscription should return false for free subscription types.');
    }
}
