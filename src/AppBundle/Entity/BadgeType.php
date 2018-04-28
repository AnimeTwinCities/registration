<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * BadgeType
 *
 * @ORM\Table(name="badge_type", indexes={@ORM\Index(name="FK1_BadgeType_CreatedBy", columns={"created_by"}), @ORM\Index(name="FK2_BadgeType_ModifiedBy", columns={"modified_by"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BadgeTypeRepository")
 */
class BadgeType
{
    public function __construct()
    {
        $this->eventBadgeTypes = new ArrayCollection();
    }

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=255, nullable=true)
     */
    private $color;

    /**
     * @var boolean
     *
     * @ORM\Column(name="staff", type="boolean", length=255, nullable=false)
     */
    private $staff = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="sponsor", type="boolean", length=255, nullable=false)
     */
    private $sponsor = '0';

    /**
     * One Product has Many Features.
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\EventBadgeType", mappedBy="badgeType")
     */
    private $eventBadgeTypes;

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
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * })
     */
    private $createdBy;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modified_by", referencedColumnName="id")
     * })
     */
    private $modifiedBy;


    public function __toString()
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getId(): ?int
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
     * Get modifiedDate
     *
     * @return \DateTime
     */
    public function getModifiedDate()
    {
        return $this->modifiedDate;
    }

    /**
     * Get badgeTypeId
     *
     * @return integer
     */
    public function getBadgeTypeId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return BadgeType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return BadgeType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    /**
     * @return bool
     */
    public function isStaff(): bool
    {
        return $this->staff;
    }

    /**
     * @param bool $staff
     */
    public function setStaff(bool $staff): void
    {
        $this->staff = $staff;
    }

    /**
     * @return bool
     */
    public function isSponsor(): bool
    {
        return $this->sponsor;
    }

    /**
     * @param bool $sponsor
     */
    public function setSponsor(bool $sponsor): void
    {
        $this->sponsor = $sponsor;
    }

    /**
     * Get events
     *
     * @return EventBadgeType[]|\Doctrine\Common\Collections\Collection
     */
    public function getEventBadgeTypes()
    {
        return $this->eventBadgeTypes;
    }

    /**
     * @param EventBadgeType $eventBadgeType
     */
    public function addRegistrationShirt(EventBadgeType $eventBadgeType)
    {
        $this->eventBadgeTypes->add($eventBadgeType);
    }

    /**
     * @param EventBadgeType $eventBadgeType
     */
    public function removeRegistrationShirt(EventBadgeType $eventBadgeType)
    {
        $this->eventBadgeTypes->removeElement($eventBadgeType);
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return BadgeType
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set modifiedDate
     *
     * @param \DateTime $modifiedDate
     *
     * @return BadgeType
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modifiedDate = $modifiedDate;

        return $this;
    }

    /**
     * Set createdBy
     *
     * @param \AppBundle\Entity\User $createdBy
     *
     * @return BadgeType
     */
    public function setCreatedBy(User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \AppBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set modifiedBy
     *
     * @param \AppBundle\Entity\User $modifiedBy
     *
     * @return BadgeType
     */
    public function setModifiedBy(User $modifiedBy = null)
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    /**
     * Get modifiedBy
     *
     * @return \AppBundle\Entity\User
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }
}
