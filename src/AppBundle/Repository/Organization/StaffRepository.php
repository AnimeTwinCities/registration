<?php
/**
 * Copyright (c) 2019. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

declare(strict_types=1);

namespace AppBundle\Repository\Organization;


use AppBundle\Entity\Organization\Staff;
use Doctrine\ORM\EntityRepository;

class StaffRepository extends EntityRepository
{
    /**
     * @return Staff[]
     */
    public function findAllActive()
    {
        return $this->findBy(['active' => true]);
    }
}