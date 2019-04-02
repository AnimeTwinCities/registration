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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Organization\Staff")
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