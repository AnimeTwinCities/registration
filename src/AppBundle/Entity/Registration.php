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
 * Registration
 *
 * @ORM\Table(name="registration", indexes={@ORM\Index(name="FK1_Registration_CreatedBy", columns={"created_by"}), @ORM\Index(name="FK2_Registration_ModifiedBy", columns={"modified_by"}), @ORM\Index(name="FK2_Registration_RegistrationType_ID", columns={"registration_type_id"}), @ORM\Index(name="FK2_Registration_RegistrationStatus_ID", columns={"registration_status_id"}), @ORM\Index(name="FK2_Registration_Event_ID", columns={"event_id"}), @ORM\Index(name="FK3_TransferredTo", columns={"transferred_to"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RegistrationRepository")
 */
class Registration
{
    public function __construct()
    {
        $this->badges = new ArrayCollection();
        $this->history = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->registrationShirts = new ArrayCollection();
        $this->extras = new ArrayCollection();
    }

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=255, nullable=false)
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="confirmation_number", type="string", length=255, nullable=true)
     */
    private $confirmationNumber;

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
     * @ORM\Column(name="middle_name", type="string", length=255, nullable=false)
     */
    private $middleName;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=false)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="address2", type="string", length=255, nullable=false)
     */
    private $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=false)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=32, nullable=false)
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=16, nullable=false)
     */
    private $zip;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=false)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="datetime", nullable=true)
     */
    private $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="badge_name", type="string", length=255, nullable=false)
     */
    private $badgeName;

    /**
     * @var boolean
     *
     * @ORM\Column(name="contact_volunteer", type="boolean", nullable=false)
     */
    private $contactVolunteer = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="contact_newsletter", type="boolean", nullable=false)
     */
    private $contactNewsletter = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="xml", type="string", length=8296, nullable=true)
     */
    private $xml;

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
     * @var \AppBundle\Entity\Event
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $event;

    /**
     * @var \AppBundle\Entity\RegistrationStatus
     *
     * @ORM\ManyToOne(targetEntity="RegistrationStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="registration_status_id", referencedColumnName="id")
     * })
     */
    private $registrationStatus;

    /**
     * @var \AppBundle\Entity\RegistrationType
     *
     * @ORM\ManyToOne(targetEntity="RegistrationType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="registration_type_id", referencedColumnName="id")
     * })
     */
    private $registrationType;

    /**
     * @var \AppBundle\Entity\Registration
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Registration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transferred_to", referencedColumnName="id")
     * })
     */
    private $transferredTo;

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
     * One Product has Many Features.
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Badge", mappedBy="registration")
     */
    private $badges;

    /**
     * One Product has Many Features.
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\History", mappedBy="registration")
     */
    private $history;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Group", inversedBy="registrations")
     * @ORM\JoinTable(name="registration_group", joinColumns={@ORM\JoinColumn(name="registration_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")})
     * @ORM\OrderBy({"name" = "DESC"})
     */
    private $groups;

    /**
     * One Product has Many Features.
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\RegistrationShirt", mappedBy="registration")
     */
    private $registrationShirts;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Extra", inversedBy="registrations")
     * @ORM\JoinTable(name="registration_extra", joinColumns={@ORM\JoinColumn(name="registration_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="extra_id", referencedColumnName="id")})
     */
    private $extras;



    /**
     * Set number
     *
     * @param string $number
     *
     * @return Registration
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
     * Set confirmationNumber
     *
     * @param string $confirmationNumber
     *
     * @return Registration
     */
    public function setConfirmationNumber($confirmationNumber)
    {
        $this->confirmationNumber = $confirmationNumber;

        return $this;
    }

    /**
     * Get confirmationNumber
     *
     * @return string
     */
    public function getConfirmationNumber()
    {
        return $this->confirmationNumber;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Registration
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Registration
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set middleName
     *
     * @param string $middleName
     *
     * @return Registration
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * Get middleName
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Registration
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address2
     *
     * @param string $address2
     *
     * @return Registration
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Registration
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return Registration
     */
    public function setState($state)
    {
        if (strlen($state) > 32) {
            $state = substr($state, 0, 32);
        }
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return Registration
     */
    public function setZip($zip)
    {
        if (strlen($zip) > 16) {
            $zip = substr($zip, 0, 16);
        }
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Registration
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Registration
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     *
     * @return Registration
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set badgeName
     *
     * @param string $badgeName
     *
     * @return Registration
     */
    public function setBadgeName($badgeName)
    {
        $this->badgeName = $badgeName;

        return $this;
    }

    /**
     * Get badgeName
     *
     * @return string
     */
    public function getBadgeName()
    {
        if (!$this->badgeName) {
            return $this->getFirstName();
        }
        return $this->badgeName;
    }

    /**
     * Set contactVolunteer
     *
     * @param boolean $contactVolunteer
     *
     * @return Registration
     */
    public function setContactVolunteer($contactVolunteer)
    {
        $this->contactVolunteer = $contactVolunteer;

        return $this;
    }

    /**
     * Get contactVolunteer
     *
     * @return boolean
     */
    public function getContactVolunteer()
    {
        return $this->contactVolunteer;
    }

    /**
     * Set contactNewsletter
     *
     * @param boolean $contactNewsletter
     *
     * @return Registration
     */
    public function setContactNewsletter($contactNewsletter)
    {
        $this->contactNewsletter = $contactNewsletter;

        return $this;
    }

    /**
     * Get contactNewsletter
     *
     * @return boolean
     */
    public function getContactNewsletter()
    {
        return $this->contactNewsletter;
    }

    /**
     * Set xml
     *
     * @param string $xml
     *
     * @return Registration
     */
    public function setXml($xml)
    {
        $this->xml = $xml;

        return $this;
    }

    /**
     * Get xml
     *
     * @return string
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Registration
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
     * @return Registration
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
     * Get registrationId
     *
     * @return integer
     */
    public function getRegistrationId()
    {
        return $this->id;
    }

    /**
     * Set createdBy
     *
     * @param \AppBundle\Entity\User $createdBy
     *
     * @return Registration
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
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return Registration
     */
    public function setEvent(Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \AppBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set registrationStatus
     *
     * @param \AppBundle\Entity\RegistrationStatus $registrationStatus
     *
     * @return Registration
     */
    public function setRegistrationStatus(RegistrationStatus $registrationStatus = null)
    {
        $this->registrationStatus = $registrationStatus;

        return $this;
    }

    /**
     * Get registrationStatus
     *
     * @return \AppBundle\Entity\RegistrationStatus
     */
    public function getRegistrationStatus()
    {
        return $this->registrationStatus;
    }

    /**
     * Set registrationType
     *
     * @param \AppBundle\Entity\RegistrationType $registrationType
     *
     * @return Registration
     */
    public function setRegistrationType(RegistrationType $registrationType = null)
    {
        $this->registrationType = $registrationType;

        return $this;
    }

    /**
     * Get registrationType
     *
     * @return \AppBundle\Entity\RegistrationType
     */
    public function getRegistrationType()
    {
        return $this->registrationType;
    }

    /**
     * Set transferredTo
     *
     * @param \AppBundle\Entity\Registration $transferredTo
     *
     * @return Registration
     */
    public function setTransferredTo(Registration $transferredTo = null)
    {
        $this->transferredTo = $transferredTo;

        return $this;
    }

    /**
     * Get transferredTo
     *
     * @return \AppBundle\Entity\Registration
     */
    public function getTransferredTo()
    {
        return $this->transferredTo;
    }

    /**
     * Set modifiedBy
     *
     * @param \AppBundle\Entity\User $modifiedBy
     *
     * @return Registration
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
     * Get badges
     *
     * @return Badge[]|\Doctrine\Common\Collections\Collection
     */
    public function getBadges()
    {
        return $this->badges;
    }

    /**
     * @param Badge $badge
     */
    public function addBadge(Badge $badge)
    {
        $this->badges->add($badge);
    }

    /**
     * @param Badge $badge
     */
    public function removeBadge(Badge $badge)
    {
        $this->badges->removeElement($badge);
    }

    /**
     * Get history
     *
     * @return History[]|\Doctrine\Common\Collections\Collection
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param History $history
     */
    public function addHistory(History $history)
    {
        $this->history->add($history);
    }

    /**
     * @param History $history
     */
    public function removeHistory(History $history)
    {
        $this->history->removeElement($history);
    }

    /**
     * Get groups
     *
     * @return Group[]|\Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param Group $group
     */
    public function addGroup(Group $group)
    {
        $this->groups->add($group);
    }

    /**
     * @param Group $group
     */
    public function removeGroup($group)
    {
        $this->groups->removeElement($group);
    }

    /**
     * Get events
     *
     * @return RegistrationShirt[]|\Doctrine\Common\Collections\Collection
     */
    public function getRegistrationShirts()
    {
        return $this->registrationShirts;
    }

    /**
     * @param RegistrationShirt $registrationShirt
     */
    public function addRegistrationShirt(RegistrationShirt $registrationShirt)
    {
        $this->registrationShirts->add($registrationShirt);
    }

    /**
     * @param RegistrationShirt $registrationShirt
     */
    public function removeRegistrationShirt(RegistrationShirt $registrationShirt)
    {
        $this->registrationShirts->removeElement($registrationShirt);
    }

    /**
     * Get extras
     *
     * @return Extra[]|\Doctrine\Common\Collections\Collection
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * @param Extra $extra
     */
    public function addExtra(Extra $extra)
    {
        $this->extras->add($extra);
    }

    /**
     * @param Extra $extra
     */
    public function removeExtra($extra)
    {
        $this->extras->removeElement($extra);
    }
}
