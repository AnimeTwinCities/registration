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
     * @ORM\Column(name="active", type="boolean", nullable=false)
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