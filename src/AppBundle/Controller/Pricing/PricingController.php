<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Controller\Pricing;

use AppBundle\Entity\BadgeType;
use AppBundle\Entity\Event;
use AppBundle\Entity\Pricing;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PricingController extends Controller
{
    /**
     * @Route("/pricing", name="pricing")
     * @Security("has_role('ROLE_ADMIN')")
     * @return Response
     */
    public function pricingEditor()
    {
        $parameters = [];

        $parameters['event'] = $this->getDoctrine()->getRepository(Event::class)->getSelectedEvent();

        $parameters['badgeTypes'] = $this->getDoctrine()->getRepository(BadgeType::class)->findBy([], ['description' => 'ASC']);

        $parameters['pricing'] = [];
        $parameters['badgeTypeNames'] = [];
        $parameters['badgeTypeDescriptions'] = [];
        $parameters['displayBadgeTypes'] = [];
        $emptyBadgeTypes = [];
        foreach ($parameters['badgeTypes'] as $badgeType) {
            $parameters['badgeTypeNames'][] = $badgeType->getName();
            $parameters['badgeTypeDescriptions'][$badgeType->getName()] = [
                'color' => $badgeType->getColor(),
                'name' => $badgeType->getDescription(),
            ];

            $parameters['pricing'][$badgeType->getName()] = $this->getPricingDataForBadgeType($badgeType);

            if (count($parameters['pricing'][$badgeType->getName()]['pricing']) > 0) {
                $parameters['displayBadgeTypes'][] = $badgeType;
            } else {
                $emptyBadgeTypes[] = $badgeType;
            }
        }

        foreach ($emptyBadgeTypes as $badgeType) {
            $parameters['displayBadgeTypes'][] = $badgeType;
        }

        return $this->render('pricing/pricingEditor.html.twig', $parameters);
    }

    /**
     * @param int $id
     * @Route("/pricing/delete/{id}", name="pricingDelete")
     * @Route("/pricing/delete/", name="pricingDelete_Slash")
     * @Security("has_role('ROLE_ADMIN')")
     * @return JsonResponse
     */
    public function pricingDelete($id)
    {
        $returnData  = [
            'success' => false,
        ];
        try {
            $em = $this->getDoctrine()->getManager();

            $pricing = $em->getRepository(Pricing::class)->find($id);
            $badgeType = $pricing->getBadgeType();

            $em->remove($pricing);
            $em->flush();

            $returnData['success'] = true;
            $returnData['badgeTypeName'] = $badgeType->getName();
            $returnData['data'] = $this->getPricingDataForBadgeType($badgeType);
        } catch (\Exception $e) {
            $returnData['message'] = $e->getMessage();
        }

        return new JsonResponse($returnData);
    }

    /**
     * @param Request $request
     * @param int $id
     * @Route("/pricing/edit/{id}", name="pricingEdit")
     * @Route("/pricing/edit/", name="pricingEdit_Slash")
     * @Security("has_role('ROLE_ADMIN')")
     * @return JsonResponse
     */
    public function pricingEdit(Request $request, $id)
    {
        $returnData  = [
            'success' => false,
        ];
        try {
            if (!$request->request->has('priceStart')
                || !$request->request->has('priceEnd')
                || !$request->request->has('price')
                || !$request->request->has('description')
            ) {
                throw new \Exception('Missing required fields to save data');
            }
            $em = $this->getDoctrine()->getManager();

            $priceStart = $request->request->get('priceStart');
            $priceEnd = $request->request->get('priceEnd');
            $price = $request->request->get('price');
            $description = $request->request->get('description');

            $startDateTime = new \DateTime($priceStart);
            $endDateTime = new \DateTime($priceEnd);

            $pricing = $em->getRepository(Pricing::class)->find($id);
            $badgeType = $pricing->getBadgeType();

            $isValidPricePoint = $this->getDoctrine()
                ->getRepository(Pricing::class)
                ->isValidPricePoint($badgeType, $startDateTime, $pricing);
            if (!$isValidPricePoint) {
                throw new \Exception('Invalid Start Price');
            }

            $isValidPricePoint = $this->getDoctrine()
                ->getRepository(Pricing::class)
                ->isValidPricePoint($badgeType, $endDateTime, $pricing);
            if (!$isValidPricePoint) {
                throw new \Exception('Invalid End Price');
            }

            $pricing->setPricingBegin($startDateTime);
            $pricing->setPricingEnd($endDateTime);
            $pricing->setPrice((int)$price);
            $pricing->setDescription($description);
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $returnData['success'] = true;
            $returnData['badgeTypeName'] = $badgeType->getName();
            $returnData['data'] = $this->getPricingDataForBadgeType($badgeType);
        } catch (\Exception $e) {
            $returnData['message'] = $e->getMessage();
        }

        return new JsonResponse($returnData);
    }

    /**
     * @param Request $request
     * @Route("/pricing/add", name="pricingAdd")
     * @Security("has_role('ROLE_ADMIN')")
     * @return JsonResponse
     */
    public function pricingAdd(Request $request)
    {
        $returnData  = [
            'success' => false,
        ];
        try {
            if (!$request->request->has('badgeType')
                || !$request->request->has('priceStart')
                || !$request->request->has('priceEnd')
                || !$request->request->has('price')
                || !$request->request->has('description')
            ) {
                throw new \Exception('Missing required fields to save data');
            }

            $badgeTypeName = $request->request->get('badgeType');
            $priceStart = $request->request->get('priceStart');
            $priceEnd = $request->request->get('priceEnd');
            $price = $request->request->get('price');
            $description = $request->request->get('description');

            $startDateTime = new \DateTime($priceStart);
            $endDateTime = new \DateTime($priceEnd);

            $event = $this->getDoctrine()->getRepository(Event::class)->getSelectedEvent();
            $badgeType = $this->getDoctrine()->getRepository(BadgeType::class)->getBadgeTypeFromType($badgeTypeName);

            $isValidPricePoint = $this->getDoctrine()
                ->getRepository(Pricing::class)
                ->isValidPricePoint($badgeType, $startDateTime);
            if (!$isValidPricePoint) {
                throw new \Exception('Invalid Start Price');
            }

            $isValidPricePoint = $this->getDoctrine()
                ->getRepository(Pricing::class)
                ->isValidPricePoint($badgeType, $endDateTime);
            if (!$isValidPricePoint) {
                throw new \Exception('Invalid End Price');
            }

            $pricing = new Pricing();
            $pricing->setEvent($event);
            $pricing->setBadgeType($badgeType);
            $pricing->setPricingBegin($startDateTime);
            $pricing->setPricingEnd($endDateTime);
            $pricing->setPrice((int)$price);
            $pricing->setDescription($description);
            $em = $this->getDoctrine()->getManager();
            $em->persist($pricing);
            $em->flush();

            $returnData['success'] = true;
            $returnData['badgeTypeName'] = $badgeType->getName();
            $returnData['data'] = $this->getPricingDataForBadgeType($badgeType);
        } catch (\Exception $e) {
            $returnData['message'] = $e->getMessage();
        }

        return new JsonResponse($returnData);
    }

    /**
     * @param BadgeType $badgeType
     * @return mixed[]
     */
    private function  getPricingDataForBadgeType(BadgeType $badgeType) : array
    {
        $pricing = $this->getDoctrine()->getRepository(Pricing::class)->getPricingForBadgeType($badgeType);

        $pricingArray = [];
        $pricingKeys = [];
        foreach ($pricing as $price) {
            $tmp = [
                'id' => $price->getId(),
                'start' => $price->getPricingBegin()->format('U'),
                'end' => $price->getPricingEnd()->format('U'),
                'currency' => $price->getCurrency(),
                'price' => $price->getPrice(),
                'description' => $price->getDescription(),
            ];
            $pricingArray[$price->getPricingBegin()->format('U')] = $tmp;
            $pricingKeys[] = (int) $price->getPricingBegin()->format('U');
        }

        return [
            'pricing' => $pricingArray,
            'pricingKeys' => $pricingKeys,
        ];
    }
}
