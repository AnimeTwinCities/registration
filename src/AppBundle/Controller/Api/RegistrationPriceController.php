<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Event;
use AppBundle\Entity\Pricing;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class RegistrationPriceController extends Controller
{
    /**
     * @Route("/api/prices", name="currentRegistrationPrices")
     *
     * @return JsonResponse
     */
    public function getCurrentPrices()
    {
        $data = [];

        $date = new \DateTime();
        $event = $this->getDoctrine()->getRepository(Event::class)->getSelectedEvent();

        $data['preRegistrationStart'] = $event->getPreRegistrationStart();
        $data['preRegistrationEnd'] = $event->getPreRegistrationEnd();

        $registrationOpen  = true;
        if ($date->format('U') < $event->getPreRegistrationStart()->format('U')) {
            $registrationOpen = false;
        }

        if (!$registrationOpen || $date->format('U') > $event->getPreRegistrationEnd()->format('U')) {
            $registrationOpen = false;
        }
        $data['isRegistrationOpen'] = $registrationOpen;

        $prices = $this->getDoctrine()->getRepository(Pricing::class)->findCurrentPricing();

        $data['currentPrices'] = $this->convertPricingObjectToArray($prices);

        $futurePrices = $this->getDoctrine()->getRepository(Pricing::class)->findFuturePricing();
        $data['futurePrices'] = $this->convertPricingObjectToArray($futurePrices);

        return $this->json($data);
    }

    /**
     * @param Pricing[] $prices
     * @return array
     */
    private function convertPricingObjectToArray($prices)
    {
        $pricingArray = [];
        foreach ($prices as $price) {
            $tmp = [
                'id' => $price->getId(),
                'BadgeTypeName' => $price->getBadgeType()->getName(),
                'BadgeTypeDescription' => $price->getBadgeType()->getDescription(),
                'currency' => $price->getCurrency(),
                'price' => $price->getPrice(),
                'pricingBegin' => $price->getPricingBegin(),
                'pricingEnd' => $price->getPricingEnd(),
                'description' => $price->getDescription(),
            ];
            $pricingArray[$price->getId()] = $tmp;
        }
        return $pricingArray;
    }
}
