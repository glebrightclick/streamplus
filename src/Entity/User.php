<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    /**
     * @var Collection<int, Address>
     */
    #[ORM\OneToMany(targetEntity: Address::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $addresses;

    /**
     * @var Collection<int, CreditCardInfo>
     */
    #[ORM\OneToMany(targetEntity: CreditCardInfo::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $creditCardInfos;

    /**
     * @var Collection<int, Subscription>
     */
    #[ORM\OneToMany(targetEntity: Subscription::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $subscriptions;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->creditCardInfos = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection<int, Address>
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): static
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setUser($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): static
    {
        if ($this->addresses->removeElement($address)) {
            // set the owning side to null (unless already changed)
            if ($address->getUser() === $this) {
                $address->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CreditCardInfo>
     */
    public function getCreditCardInfos(): Collection
    {
        return $this->creditCardInfos;
    }

    public function addCreditCardInfo(CreditCardInfo $creditCardInfo): static
    {
        if (!$this->creditCardInfos->contains($creditCardInfo)) {
            $this->creditCardInfos->add($creditCardInfo);
            $creditCardInfo->setUser($this);
        }

        return $this;
    }

    public function removeCreditCardInfo(CreditCardInfo $creditCardInfo): static
    {
        if ($this->creditCardInfos->removeElement($creditCardInfo)) {
            // set the owning side to null (unless already changed)
            if ($creditCardInfo->getUser() === $this) {
                $creditCardInfo->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscriptions(): Collection
    {
        return $this->subscriptions;
    }

    public function addSubscription(Subscription $subscription): static
    {
        if (!$this->subscriptions->contains($subscription)) {
            $this->subscriptions->add($subscription);
            $subscription->setUser($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): static
    {
        if ($this->subscriptions->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getUser() === $this) {
                $subscription->setUser(null);
            }
        }

        return $this;
    }

    public function getActiveSubscription(DateTimeInterface $time): ?Subscription
    {
        if ($this->getSubscriptions()->isEmpty()) {
            return null;
        }

        // find first active subscription: date_end as a null means that subscription is active in present
        foreach ($this->getSubscriptions() as $subscription) {
            // if date start is in future in comparison to passed time
            if ($subscription->getDateStart()->getTimestamp() > $time->getTimestamp()) {
                continue;
            }

            // if date end is in past in comparison to passed time (and not null)
            if ($subscription->getDateEnd() && $subscription->getDateEnd()->getTimestamp() < $time->getTimestamp()) {
                continue;
            }

            return $subscription;
        }

        return null;
    }
}
