<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Extra;
use AppBundle\Entity\Registration;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class ExtraRepository extends EntityRepository
{

    /**
     * @param String $name
     * @return Extra|null
     */
    public function getExtraFromName($name)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('ex')
            ->from('AppBundle:Extra', 'ex')
            ->where("ex.name = :name")
            ->setParameter('name', $name);

        try {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @param Registration $registration
     * @return Extra[]
     */
    public function getExtrasFromRegistration(Registration $registration)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('ex')
            ->from(Extra::class, 'ex')
            ->innerJoin(Registration::class, 'r')
            ->where("r.id = :registrationId")
            ->setParameter('registrationId', $registration)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}