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
     * @var string
     *
     * @ORM\Column(name="intake_form_file", type="string", length=255, nullable=true)
     */
    private $intakeFormFile;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Registration", mappedBy="staffMember")
     */
    private $registrations;

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
}