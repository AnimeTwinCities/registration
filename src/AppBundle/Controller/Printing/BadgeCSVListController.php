<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Controller\Printing;

use AppBundle\Entity\Badge;
use AppBundle\Entity\BadgeType;
use AppBundle\Entity\Event;
use AppBundle\Entity\Registration;
use AppBundle\Entity\RegistrationStatus;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\ORM\Query\Expr\Join;

class BadgeCSVListController extends Controller
{
    /**
     * @Route("/print/csv/{type}", name="print_csv")
     * @Security("has_role('ROLE_SUBHEAD')")
     *
     * @param string $type
     */
    public function printingList($type)
    {
        $event = $this->getDoctrine()->getRepository(Event::class)->getSelectedEvent();
        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.default_entity_manager');
        $queryBuilder = $entityManager->createQueryBuilder();

        $order = [];

        $badgeTypeRepository = $this->getDoctrine()->getRepository(BadgeType::class);

        $badgeTypes = [];
        $show_group = false;
        switch ($type) {
            case 'staff':
                $badgeTypes[] = $badgeTypeRepository->getBadgeTypeFromType('STAFF');
                break;
            case 'sponsor':
                $badgeTypes[] = $badgeTypeRepository->getBadgeTypeFromType('ADREGSPONSOR');
                $badgeTypes[] = $badgeTypeRepository->getBadgeTypeFromType('ADREGCOMMSPONSOR');
                break;
            case 'standard':
                $badgeTypes[] = $badgeTypeRepository->getBadgeTypeFromType('ADREGSTANDARD');
                $badgeTypes[] = $badgeTypeRepository->getBadgeTypeFromType('MINOR');
                break;
            case 'group':
                $show_group = true;
                $order[] = ['regGroupName', 'ASC'];
                break;
            case 'guest':
                $badgeTypes[] = $badgeTypeRepository->getBadgeTypeFromType('GUEST');
                break;
            case 'exhibitor':
                $badgeTypes[] = $badgeTypeRepository->getBadgeTypeFromType('EXHIBITOR');
                break;
            case 'vendor':
                $badgeTypes[] = $badgeTypeRepository->getBadgeTypeFromType('VENDOR');
                break;
        }

        $badgesSubQuery = $this->get('doctrine.orm.default_entity_manager')->createQueryBuilder()
            ->select('IDENTITY(b2.registration)')
            ->from('AppBundle:Badge', 'b2');

        for ($i = 0; $i < count($badgeTypes); $i++) {
            if ($i == 0) {
                $badgesSubQuery
                    ->where("b2.badgeType = :type$i");
            } else {
                $badgesSubQuery
                    ->orWhere("b2.badgeType = :type$i");
            }
            $queryBuilder->setParameter("type$i", $badgeTypes[$i]);
        }
        $badgesSubQueryDQL = $badgesSubQuery->getDQL();

        $registrationStatusSubQueryDQL = $this->get('doctrine.orm.default_entity_manager')->createQueryBuilder()
            ->select('rs.id')
            ->from(RegistrationStatus::class, 'rs')
            ->where('rs.active = :active')
            ->getDQL();

        $queryBuilder
            ->select([
                'r.number',
                'r.badgeName',
                'r.firstName',
                'r.lastName',
                'b.number as badgeNumber',
                'bt.name as badgeType',
                'g.name as groupName',
                'r.confirmationNumber',
                'e.name as extra'
            ])
            ->from(Registration::class, 'r')
            ->innerJoin('r.badges', 'b')
            ->innerJoin('b.badgeStatus', 'bs')
            ->innerJoin('b.badgeType', 'bt')
            ->leftJoin('r.groups', 'g')
            ->leftJoin('r.extras', 'e')
            ->where($queryBuilder->expr()->in('r.registrationStatus', $registrationStatusSubQueryDQL))
            ->andWhere($queryBuilder->expr()->in('r.id', $badgesSubQueryDQL))
            ->andWhere('r.event = :event')
            ->andWhere('bs.active = :bsActive')
            ->setParameter('event', $event)
            ->setParameter('active', true)
            ->setParameter('bsActive', true);


        if ($type != 'staff') {
            $staffBadge = $badgeTypeRepository->getBadgeTypeFromType('STAFF');

            $allStaffBadges = $this->get('doctrine.orm.default_entity_manager')->createQueryBuilder()
                ->select('IDENTITY(b3.registration)')
                ->from(Badge::class, 'b3')
                ->where("b3.badgeType = :staffType")
                ->innerJoin('b3.registration', 'b3r')
                ->andWhere('b3r.event = :b3rEvent')
                ->getDQL();

            $queryBuilder->andWhere($queryBuilder->expr()->notIn('r.id', $allStaffBadges));
            $queryBuilder->setParameter('staffType', $staffBadge)
                ->setParameter('b3rEvent', $event);
        }

        if ($show_group) {
            $queryBuilder->andWhere($queryBuilder->expr()->isNotNull('g.id'));
        } else {
            $queryBuilder->andWhere($queryBuilder->expr()->isNull('g.id'));
        }

        $order[] = ['r.number', 'ASC'];
        $order[] = ['bt.id', 'DESC'];
        for ($i = 0; $i < count($order); $i++) {
            if ($i == 0) {
                $queryBuilder->orderBy($order[$i][0], $order[$i][1]);
            } else {
                $queryBuilder->addOrderBy($order[$i][0], $order[$i][1]);
            }
        }

        $registrations = $queryBuilder
            ->groupBy('r.id')
            ->getQuery()
            ->getArrayResult();

        $filename = "{$type}_Badge_Spreadsheet.csv";

        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        $handle = fopen('php://output', 'w');

        $fields = [
            'Badge',
            'Confirmation Number',
            'Last Name',
            'First Name',
            'Badge Name',
            'Badge Type',
            'Group',
            'Extra',
            'Signature',
        ];

        fputcsv($handle, $fields);

        foreach ($registrations as $registration) {
            $regNumber = $registration['number'];
            $regName = $registration['badgeName'];
            $firstName = $registration['firstName'];
            $lastName = $registration['lastName'];
            $badgeType = $registration['badgeType'];
            $groupName = $registration['groupName'];
            $extra = $registration['extra'];
            $confirmationNumber = $registration['confirmationNumber'];

            $data = [
                $regNumber,
                $confirmationNumber,
                $firstName,
                $lastName,
                $regName,
                $badgeType,
                $groupName,
                $extra,
                'X_______________________',
            ];

            fputcsv($handle, $data);
        }

        fclose($handle);
        exit;
    }
}
