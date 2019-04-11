<?php
/**
 * Copyright (c) 2019. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */
declare(strict_types=1);


namespace AppBundle\Entity\Organization;


use Doctrine\ORM\Mapping as ORM;
use \AppBundle\Entity\User;


/**
 * Badge
 *
 * @ORM\Table(name="organization_staff_department",
 *     indexes={
 *      @ORM\Index(columns={"created_by"}),
 *      @ORM\Index(columns={"modified_by"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Organization\StaffDepartmentRepository")
 */
class StaffDepartment
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
     * @var Staff
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organization\Staff", inversedBy="departments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="staff_id", referencedColumnName="id")
     * })
     */
    private $staff;

    /**
     * @var Department
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organization\Department")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="department_id", referencedColumnName="id")
     * })
     */
    private $department;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="string", length=255, nullable=true)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="string", length=8400, nullable=true)
     */
    private $notes;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_head", type="boolean", nullable=false)
     */
    private $isHead = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_sub_head", type="boolean", nullable=false)
     */
    private $isSubHead = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_temporary", type="boolean", nullable=false)
     */
    private $isTemporary = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=false)
     */
    private $isPrimary = false;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * })
     */
    private $createdBy;

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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="modified_by", referencedColumnName="id")
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
     * @return Staff
     */
    public function getStaff(): Staff
    {
        return $this->staff;
    }

    /**
     * @param Staff $staff
     */
    public function setStaff(Staff $staff): void
    {
        $this->staff = $staff;
    }

    /**
     * @return Department
     */
    public function getDepartment(): Department
    {
        return $this->department;
    }

    /**
     * @param Department $department
     */
    public function setDepartment(Department $department): void
    {
        $this->department = $department;
    }

    /**
     * @return string
     */
    public function getPosition(): ?string
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     */
    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @return bool
     */
    public function isHead(): bool
    {
        return $this->isHead;
    }

    /**
     * @param bool $isHead
     */
    public function setIsHead(bool $isHead): void
    {
        $this->isHead = $isHead;
    }

    /**
     * @return bool
     */
    public function isSubHead(): bool
    {
        return $this->isSubHead;
    }

    /**
     * @param bool $isSubHead
     */
    public function setIsSubHead(bool $isSubHead): void
    {
        $this->isSubHead = $isSubHead;
    }

    /**
     * @return bool
     */
    public function isTemporary(): bool
    {
        return $this->isTemporary;
    }

    /**
     * @param bool $isTemporary
     */
    public function setIsTemporary(bool $isTemporary): void
    {
        $this->isTemporary = $isTemporary;
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * @param bool $isPrimary
     */
    public function setIsPrimary(bool $isPrimary): void
    {
        $this->isPrimary = $isPrimary;
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