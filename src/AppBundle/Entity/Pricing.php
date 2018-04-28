<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pricing
 *
 * @ORM\Table(name="pricing",
 *     indexes={@ORM\Index(name="FK_Pricing_CreatedBy", columns={"created_by"}),
 *          @ORM\Index(name="FK_Pricing_CreatedBy", columns={"created_by"}),
 *          @ORM\Index(name="FK_Pricing_ModifiedBy", columns={"modified_by"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PricingRepository")
 */
class Pricing
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Event
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * })
     */
    private $event;

    /**
     * @var \AppBundle\Entity\BadgeType
     *
     * @ORM\ManyToOne(targetEntity="BadgeType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="badge_type_id", referencedColumnName="id")
     * })
     */
    private $badgeType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pricing_begin", type="datetime")
     */
    private $pricingBegin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pricing_end", type="datetime")
     */
    private $pricingEnd;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=255, options={"default" : "USD"})
     */
    private $currency = 'USD';

    /**
     * @var integer
     *
     * @ORM\Column(name="price", type="string", length=255)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified_date", type="datetime", nullable=true)
     */
    private $modifiedDate;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $createdBy;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modified_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $modifiedBy;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    /**
     * @return BadgeType
     */
    public function getBadgeType(): BadgeType
    {
        return $this->badgeType;
    }

    /**
     * @param BadgeType $badgeType
     */
    public function setBadgeType(BadgeType $badgeType): void
    {
        $this->badgeType = $badgeType;
    }

    /**
     * @return \DateTime
     */
    public function getPricingBegin(): \DateTime
    {
        return $this->pricingBegin;
    }

    /**
     * @param \DateTime $pricingBegin
     */
    public function setPricingBegin(\DateTime $pricingBegin): void
    {
        $this->pricingBegin = $pricingBegin;
    }

    /**
     * @return \DateTime
     */
    public function getPricingEnd(): \DateTime
    {
        return $this->pricingEnd;
    }

    /**
     * @param \DateTime $pricingEnd
     */
    public function setPricingEnd(\DateTime $pricingEnd): void
    {
        $this->pricingEnd = $pricingEnd;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDate(): \DateTime
    {
        return $this->createdDate;
    }

    /**
     * @param \DateTime $createdDate
     */
    public function setCreatedDate(\DateTime $createdDate): void
    {
        $this->createdDate = $createdDate;
    }

    /**
     * @return \DateTime
     */
    public function getModifiedDate(): \DateTime
    {
        return $this->modifiedDate;
    }

    /**
     * @param \DateTime $modifiedDate
     */
    public function setModifiedDate(\DateTime $modifiedDate): void
    {
        $this->modifiedDate = $modifiedDate;
    }

    /**
     * @return User
     */
    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     */
    public function setCreatedBy(User $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return User
     */
    public function getModifiedBy(): User
    {
        return $this->modifiedBy;
    }

    /**
     * @param User $modifiedBy
     */
    public function setModifiedBy(User $modifiedBy): void
    {
        $this->modifiedBy = $modifiedBy;
    }
}