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


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use \AppBundle\Entity\User;

/**
 * Badge
 *
 * @ORM\Table(name="organization_department",
 *     indexes={
 *      @ORM\Index(columns={"created_by"}),
 *      @ORM\Index(columns={"modified_by"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Organization\DepartmentRepository")
 */
class Department
{
    public function __construct()
    {
        $this->childDepartments = new ArrayCollection();
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="external_email", type="string", length=255, nullable=true)
     */
    private $externalEmail;

    /**
     * @var boolean
     *
     * @ORM\Column(name="all_staff_receive_external_email", type="boolean", nullable=false)
     */
    private $allStaffReceiveExternalEmail = false;

    /**
     * @var string
     *
     * @ORM\Column(name="internal_email", type="string", length=255, nullable=true)
     */
    private $internalEmail;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = false;

    /**
     * @var Department[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Organization\Department", mappedBy="parentDepartment")
     */
    private $childDepartments;

    /**
     * @var Department
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organization\Department", inversedBy="childDepartments")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parentDepartment;

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
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
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
     * @return string
     */
    public function getExternalEmail(): ?string
    {
        return $this->externalEmail;
    }

    /**
     * @param string $externalEmail
     */
    public function setExternalEmail(string $externalEmail): void
    {
        $this->externalEmail = $externalEmail;
    }

    /**
     * @return bool
     */
    public function isAllStaffReceiveExternalEmail(): bool
    {
        return $this->allStaffReceiveExternalEmail;
    }

    /**
     * @param bool $allStaffReceiveExternalEmail
     */
    public function setAllStaffReceiveExternalEmail(bool $allStaffReceiveExternalEmail): void
    {
        $this->allStaffReceiveExternalEmail = $allStaffReceiveExternalEmail;
    }

    /**
     * @return string
     */
    public function getInternalEmail(): ?string
    {
        return $this->internalEmail;
    }

    /**
     * @param string $internalEmail
     */
    public function setInternalEmail(string $internalEmail): void
    {
        $this->internalEmail = $internalEmail;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return Department[]|\Doctrine\Common\Collections\Collection
     */
    public function getChildDepartments(): array
    {
        return $this->childDepartments;
    }

    /**
     * @param Department[] $childDepartments
     */
    public function setChildDepartments(array $childDepartments): void
    {
        $this->childDepartments = $childDepartments;
    }

    /**
     * @param Department $childDepartments
     */
    public function addChildDepartment(Department $childDepartments)
    {
        $this->childDepartments->add($childDepartments);
    }

    /**
     * @param Department $childDepartments
     */
    public function removeChildDepartment($childDepartments)
    {
        $this->childDepartments->removeElement($childDepartments);
    }

    /**
     * @return Department
     */
    public function getParentDepartment(): ?Department
    {
        return $this->parentDepartment;
    }

    /**
     * @param Department $parentDepartment
     */
    public function setParentDepartment(Department $parentDepartment): void
    {
        $this->parentDepartment = $parentDepartment;
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