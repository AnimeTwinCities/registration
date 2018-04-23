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
}