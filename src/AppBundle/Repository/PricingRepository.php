<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Repository;


use AppBundle\Entity\BadgeType;
use AppBundle\Entity\Event;
use AppBundle\Entity\Pricing;
use Doctrine\ORM\EntityRepository;


class PricingRepository extends EntityRepository
{
    /**
     * @param BadgeType $badgeType
     * @param Event $event
     * @return Pricing[]
     */
    public function getPricingForBadgeType(BadgeType $badgeType, Event $event = null)
    {
        if (!$event) {
            $event = $this->getEntityManager()->getRepository(Event::class)->getSelectedEvent();
        }

        $pricing = $this->findBy(['badgeType' => $badgeType, 'event' => $event], ['pricingBegin' => 'ASC']);

        return $pricing;
    }

    /**
     * @return Pricing[]
     */
    public function findCurrentPricing()
    {
        $event = $this->getEntityManager()->getRepository(Event::class)->getSelectedEvent();

        $date = new \DateTime();

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Pricing::class, 'p')
            ->where("p.event = :event")
            ->andWhere("p.pricingBegin < :date1")
            ->andWhere("p.pricingEnd > :date2")
            ->setParameter('event', $event)
            ->setParameter('date1', $date)
            ->setParameter('date2', $date);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return Pricing[]
     */
    public function findFuturePricing()
    {
        $event = $this->getEntityManager()->getRepository(Event::class)->getSelectedEvent();

        $date = new \DateTime();

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Pricing::class, 'p')
            ->where("p.event = :event")
            ->andWhere("p.pricingBegin > :date1")
            ->andWhere("p.pricingEnd > :date2")
            ->setParameter('event', $event)
            ->setParameter('date1', $date)
            ->setParameter('date2', $date);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param BadgeType $badgeType
     * @param \DateTime $date
     * @param Pricing $pricing
     * @return bool
     */
    public function isValidPricePoint(BadgeType $badgeType, \DateTime $date, ?Pricing $pricing = null) : bool
    {
        $event = $this->getEntityManager()->getRepository(Event::class)->getSelectedEvent();

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('p')
            ->from(Pricing::class, 'p')
            ->where("p.event = :event")
            ->andWhere("p.badgeType = :badgeType")
            ->andWhere("p.pricingBegin < :date1")
            ->andWhere("p.pricingEnd > :date2")
            ->setParameter('event', $event)
            ->setParameter('badgeType', $badgeType)
            ->setParameter('date1', $date)
            ->setParameter('date2', $date);

        if ($pricing) {
            $queryBuilder->andWhere('p.id != :pricing')
                ->setParameter(':pricing', $pricing);
        }

        if ($date->format('U') < $event->getPreRegistrationStart()->format('U')) {
            return false;
        }

        if ($date->format('U') > $event->getPreRegistrationEnd()->format('U')) {
            return false;
        }

        $results = $queryBuilder->getQuery()->getResult();

        if (count($results) > 0) {
            return false;
        }

        return true;
    }
}