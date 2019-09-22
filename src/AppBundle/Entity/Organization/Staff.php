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


use AppBundle\Entity\Registration;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use \AppBundle\Entity\User;
use Ramsey\Uuid\UuidInterface;

/**
 * Badge
 *
 * @ORM\Table(name="organization_staff",
 *     indexes={
 *      @ORM\Index(columns={"created_by"}),
 *      @ORM\Index(columns={"modified_by"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Organization\StaffRepository")
 */
class Staff
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=false)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=false)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="nick_name", type="string", length=255, nullable=true)
     */
    private $nickName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=255, nullable=true)
     */
    private $phoneNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_of_birth", type="datetime", nullable=false)
     */
    private $dateOfBirth;

    /**
     * @var string
     *
     * @ORM\Column(name="personal_email", type="string", length=255, nullable=false)
     */
    private $personalEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="official_email", type="string", length=255, nullable=true)
     */
    private $officialEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="shirt_size", type="string", length=255, nullable=true)
     */
    private $shirtSize;

    /**
     * @var string
     *
     * @ORM\Column(name="shirt_type", type="string", length=255, nullable=true)
     */
    private $shirtType;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="completed_intake", type="boolean", nullable=false)
     */
    private $completedIntake = false;

    /**
     * @var Registration[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Registration", mappedBy="staffMember")
     */
    private $registrations;

    /**
     * @var StaffDepartment[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Organization\StaffDepartment", mappedBy="staff")
     */
    private $departments;

    /**
     * @var StaffFile[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="StaffFile", mappedBy="staff")
     * @ORM\OrderBy({"createdDate" = "DESC"})
     */
    private $files;

    /**
     * @var StaffHistory[]|\Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Organization\StaffHistory", mappedBy="staff")
     * @ORM\OrderBy({"createdDate" = "DESC"})
     */
    private $history;

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

    public function __construct()
    {
        $this->departments = new ArrayCollection();
        $this->registrations = new ArrayCollection();
        $this->history = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    /**
     * @return UuidInterface
     */
    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        $nickname = $this->getNickName() ? "\"{$this->getNickName()}\" ": '';

        return "{$this->getFirstName()} $nickname{$this->getLastName()}";
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getNickName(): ?string
    {
        return $this->nickName;
    }

    /**
     * @param string $nickName
     */
    public function setNickName(?string $nickName): void
    {
        $this->nickName = $nickName;
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return \DateTime
     */
    public function getDateOfBirth(): ?\DateTime
    {
        return $this->dateOfBirth;
    }

    /**
     * @param \DateTime $dateOfBirth
     */
    public function setDateOfBirth(\DateTime $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * @return string
     */
    public function getPersonalEmail(): ?string
    {
        return $this->personalEmail;
    }

    /**
     * @param string $personalEmail
     */
    public function setPersonalEmail(?string $personalEmail): void
    {
        $this->personalEmail = $personalEmail;
    }

    /**
     * @return string
     */
    public function getOfficialEmail(): ?string
    {
        return $this->officialEmail;
    }

    /**
     * @param string $officialEmail
     */
    public function setOfficialEmail(?string $officialEmail): void
    {
        $this->officialEmail = $officialEmail;
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
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getShirt(): ?string
    {
        if (!$this->getShirtSize()) {
            return null;
        }

        return "{$this->getShirtType()} {$this->getShirtSize()}";
    }

    /**
     * @return string
     */
    public function getShirtSize(): ?string
    {
        return $this->shirtSize;
    }

    /**
     * @param string $shirtSize
     */
    public function setShirtSize(?string $shirtSize): void
    {
        $this->shirtSize = $shirtSize;
    }

    /**
     * @return string
     */
    public function getShirtType(): ?string
    {
        return $this->shirtType;
    }

    /**
     * @param string $shirtType
     */
    public function setShirtType(?string $shirtType): void
    {
        $this->shirtType = $shirtType;
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
     * @return bool
     */
    public function isCompletedIntake(): bool
    {
        return $this->completedIntake;
    }

    /**
     * @return string
     */
    public function getCheckedIntake(): string
    {
        return $this->completedIntake ? 'checked' : '';
    }

    /**
     * @return string
     */
    public function getCheckedActive(): string
    {
        return $this->active ? 'checked' : '';
    }

    /**
     * @param bool $completedIntake
     */
    public function setCompletedIntake(bool $completedIntake): void
    {
        $this->completedIntake = $completedIntake;
    }

    /**
     * @return Registration[]
     */
    public function getRegistrations()
    {
        return $this->registrations;
    }

    /**
     * @return Registration
     */
    public function getActiveRegistration() : ?Registration
    {
        foreach ($this->getRegistrations() as $registration) {
            if ($registration->getEvent()->getActive()) {
                return $registration;
            }
        }

        return null;
    }

    /**
     * @param Registration $registrations
     */
    public function setRegistrations($registrations): void
    {
        $this->registrations = $registrations;
    }

    /**
     * @param Registration $registration
     */
    public function addRegistration(Registration $registration)
    {
        $this->departments->add($registration);
    }

    /**
     * @param Registration $registration
     */
    public function removeRegistration(Registration $registration)
    {
        $this->departments->removeElement($registration);
    }

    /**
     * @return StaffDepartment[]
     */
    public function getDepartments()
    {
        return $this->departments;
    }

    /**
     * @param StaffDepartment[] $departments
     */
    public function setDepartments(array $departments): void
    {
        $this->departments = $departments;
    }

    /**
     * @param StaffDepartment $staffDepartment
     */
    public function addDepartment(StaffDepartment $staffDepartment)
    {
        $this->departments->add($staffDepartment);
    }

    /**
     * @param StaffDepartment $staffDepartment
     */
    public function removeDepartment(StaffDepartment $staffDepartment)
    {
        $this->departments->removeElement($staffDepartment);
    }

    /**
     * @return StaffFile[]|\Doctrine\Common\Collections\Collection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param StaffFile[]|\Doctrine\Common\Collections\Collection $files
     */
    public function setFiles($files): void
    {
        $this->files = $files;
    }

    /**
     * @param StaffFile $file
     */
    public function addFile(StaffFile $file)
    {
        $this->history->add($file);
    }

    /**
     * @return StaffHistory[]|\Doctrine\Common\Collections\Collection
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param StaffHistory[]|\Doctrine\Common\Collections\Collection $history
     */
    public function setHistory($history): void
    {
        $this->history = $history;
    }

    /**
     * @param StaffHistory $history
     */
    public function addHistory(StaffHistory $history)
    {
        $this->history->add($history);
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