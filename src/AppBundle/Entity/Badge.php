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
 * Badge
 *
 * @ORM\Table(name="badge", indexes={@ORM\Index(name="FK1_Badge_CreatedBy", columns={"created_by"}), @ORM\Index(name="FK2_Badge_ModifiedBy", columns={"modified_by"}), @ORM\Index(name="FK2_Badge_Registration_ID", columns={"registration_id"}), @ORM\Index(name="FK2_Registration_BadgeType_ID", columns={"badge_type_id"}), @ORM\Index(name="FK2_Registration_BadgeStatus_ID", columns={"badge_status_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BadgeRepository")
 */
class Badge
{
    /**
     * @var string
     *
     * @ORM\Column(name="Number", type="string", length=255, nullable=false)
     */
    private $number;

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
     * @var \AppBundle\Entity\BadgeStatus
     *
     * @ORM\ManyToOne(targetEntity="BadgeStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="badge_status_id", referencedColumnName="id")
     * })
     */
    private $badgeStatus;

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

    /**
     * @var \AppBundle\Entity\Registration
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Registration", inversedBy="badges")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="registration_id", referencedColumnName="id")
     * })
     */
    private $registration;



    /**
     * Set number
     *
     * @param string $number
     *
     * @return Badge
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Badge
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
     * @return Badge
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modifiedDate = $modifiedDate;

        return $this;
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
     * Get badgeId
     *
     * @return integer
     */
    public function getBadgeId()
    {
        return $this->id;
    }

    /**
     * Set badgeStatus
     *
     * @param \AppBundle\Entity\BadgeStatus $badgeStatus
     *
     * @return Badge
     */
    public function setBadgeStatus(BadgeStatus $badgeStatus = null)
    {
        $this->badgeStatus = $badgeStatus;

        return $this;
    }

    /**
     * Get badgeStatus
     *
     * @return \AppBundle\Entity\BadgeStatus
     */
    public function getBadgeStatus()
    {
        return $this->badgeStatus;
    }

    /**
     * Set badgeType
     *
     * @param \AppBundle\Entity\BadgeType $badgeType
     *
     * @return Badge
     */
    public function setBadgeType(BadgeType $badgeType = null)
    {
        $this->badgeType = $badgeType;

        return $this;
    }

    /**
     * Get badgeType
     *
     * @return \AppBundle\Entity\BadgeType
     */
    public function getBadgeType()
    {
        return $this->badgeType;
    }

    /**
     * Set createdBy
     *
     * @param \AppBundle\Entity\User $createdBy
     *
     * @return Badge
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
     * @return Badge
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

    /**
     * Set registration
     *
     * @param \AppBundle\Entity\Registration $registration
     *
     * @return Badge
     */
    public function setRegistration(Registration $registration = null)
    {
        $this->registration = $registration;

        return $this;
    }

    /**
     * Get registration
     *
     * @return \AppBundle\Entity\Registration
     */
    public function getRegistration()
    {
        return $this->registration;
    }
}
