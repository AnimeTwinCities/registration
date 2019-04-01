<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Controller\Statistics;

ini_set('max_execution_time', 300);

use AppBundle\Entity\Badge;
use AppBundle\Entity\BadgeType;
use AppBundle\Entity\Event;
use AppBundle\Entity\Registration;
use Doctrine\ORM\NonUniqueResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class StatisticsController extends Controller
{
    /**
     * @Route("/stats", name="statistics")
     * @Security("has_role('ROLE_STATISTICS')")
     */
    public function showStatisticsForCurrentYear()
    {
        $vars = [];
        $event = $this->getDoctrine()->getRepository(Event::class)->getSelectedEvent();
        $vars['year'] = $event->getStartDate()->format('Y');

        $eventYearEnd = $event->getStartDate()->format('Y');
        $eventYearOpen = $event->getStartDate()->format('Y') - 1;

        $remaining  = $event->getAttendancecap();

        $startIntervalSeconds = strtotime("June 1st $eventYearOpen");
        $startInterval = new \DateTime("@$startIntervalSeconds");
        $endIntervalSeconds = strtotime("May 1st $eventYearEnd");
        $endInterval = new \DateTime("@$endIntervalSeconds");

        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($startInterval, $interval, $endInterval);
        $months = [];
        foreach ($period as $dateTime) {
            /* @var $dateTime \DateTime */
            $months[] = $dateTime->format("F Y");
        }

        $counts = array();
        $dataByType = [];
        $staffBadge = $this
            ->getDoctrine()
            ->getRepository(BadgeType::class)
            ->getBadgeTypeFromType('STAFF');

        $allStaffBadges = $this->get('doctrine.orm.default_entity_manager')->createQueryBuilder()
            ->select('IDENTITY(b.registration)')
            ->from(Badge::class, 'b')
            ->where("b.badgeType = :staffType")
            ->getDQL();

        $badgeTypes = $this->getDoctrine()->getRepository(BadgeType::class)->findAll();
        foreach ($badgeTypes as $badgeType) {
            $tmp = [];
            $tmpData = [];

            $total_count = 0;
            foreach ($months as $month) {
                $start   = date('Y-m-01 H:i:s.u',strtotime($month));
                $endDateTime = new \Datetime($month);
                $endDateTime->modify('first day of next month');
                $end = $endDateTime->format('Y-m-01 H:i:s.u');

                $allBadgesSubQuery = $this->get('doctrine.orm.default_entity_manager')->createQueryBuilder()
                    ->select('IDENTITY(b2.registration)')
                    ->from(Badge::class, 'b2')
                    ->where("b2.badgeType = :type")
                    ->getDQL();

                $queryBuilder = $this->get('doctrine.orm.default_entity_manager')->createQueryBuilder();
                $queryBuilder
                    ->select('count(r.id)')
                    ->from(Registration::class, 'r')
                    ->where($queryBuilder->expr()->in('r.id', $allBadgesSubQuery))
                    ->andWhere('r.event = :event')
                    ->andWhere('r.createdDate > :start')
                    ->andWhere('r.createdDate <= :end')
                    ->setParameter('type', $badgeType->getBadgetypeId())
                    ->setParameter('start', $start)
                    ->setParameter('end', $end)
                    ->setParameter('event', $event)
                ;
                if ($badgeType->getName() != 'STAFF') {
                    $queryBuilder
                        ->andWhere($queryBuilder->expr()->notIn('r.id', $allStaffBadges))
                        ->setParameter('staffType', $staffBadge->getBadgetypeId());
                }

                try {
                    $count = (int) $queryBuilder->getQuery()->getSingleScalarResult();
                } catch (NonUniqueResultException $e) {
                    $count = 0;
                }
                $total_count += $count;
                $tmpData[] = $count;
            }

            $counts[$badgeType->getName()] = $total_count;

            if (array_sum($tmpData) == 0) {
                continue;
            }

            $tmp['name'] = $badgeType->getDescription();
            $tmp['data'] = $tmpData;
            $dataByType[] = $tmp;
        }
        $dataByTypeMonths = $months;

        $events = $this->getDoctrine()->getRepository(Event::class)->findAll();
        $eventNames = [];
        foreach ($events as $loopEvent) {
            $eventNames[] = $loopEvent->getYear();
        }
        $monthsWithoutYear = [
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
            'January',
            'February',
            'March',
            'April',
        ];
        $dataByYear = $this->getDataByEventByDay($events);

        $staffCount   = (int)$counts[$staffBadge->getName()];
        $vars['staff_percent'] = $staffCount;

        $badgeTypeRepository = $this->getDoctrine()->getRepository(BadgeType::class);

        $tmpBadge   = $badgeTypeRepository->getBadgeTypeFromType('ADREGSTANDARD');
        $tmpCount   = (int)$counts[$tmpBadge->getName()];
        $remaining  = $remaining - $tmpCount;
        $vars['standard_percent'] = $tmpCount;

        $tmpBadge   = $badgeTypeRepository->getBadgeTypeFromType('MINOR');
        $tmpCount   = (int)$counts[$tmpBadge->getName()];
        $remaining  = $remaining - $tmpCount;
        $vars['minor_percent'] = $tmpCount;

        $tmpBadge   = $badgeTypeRepository->getBadgeTypeFromType('ADREGSPONSOR');
        $tmpCount   = (int)$counts[$tmpBadge->getName()];
        $remaining  = $remaining - $tmpCount;
        $vars['sponsor_percent'] = $tmpCount;

        $tmpBadge   = $badgeTypeRepository->getBadgeTypeFromType('ADREGCOMMSPONSOR');
        $tmpCount = 0;
        if (array_key_exists($tmpBadge->getName(), $counts)) {
            $tmpCount = (int)$counts[$tmpBadge->getName()];
        }
        $remaining  = $remaining - $tmpCount;
        $vars['community_percent'] = $tmpCount;

        $vars['avail_percent'] = $remaining;

        $vars['data_by_type'] = json_encode($dataByType);
        $vars['categories'] = json_encode($dataByTypeMonths);

        $vars['data_by_year'] = json_encode($dataByYear);
        $vars['months'] = json_encode($monthsWithoutYear);

        $vars['graphByAge'] = $this->graphByAge($event);
        $vars['graphByZip'] = $this->graphByZip($event);

        $vars['countByCurrentDate'] = $this->countByCurrentDate($events, $event);

        return $this->render('statistics/statistics.html.twig', $vars);
    }


    /**
     * @param Event[] $events
     * @return mixed[]
     */
    public function getDataByEventByDay($events) {
        $data = [];
        $currentEvent = $this->getDoctrine()->getRepository(Event::class)->getCurrentEvent();

        foreach ($events as $event) {
            $cache = new FilesystemCache();
            $statsField = "stats.cache.eventsByDay.{$event->getId()}";

            if ($cache->has($statsField))
            {
                $tmpData = $cache->get($statsField);
                $tmp['name'] = $event->getYear();
                $tmp['data'] = unserialize($tmpData);
                $data[]      = $tmp;

                continue;
            }

            $tmp             = [];
            $tmpData         = [];

            $lastCount = 0;
            $eventEndInSeconds = $event->getEnddate()->getTimestamp();
            $year = ((int) $event->getStartdate()->format('Y')) - 1;

            $statsStart = strtotime("May 1st $year");
            $secondsInADay = 86400;

            $totalForYear = self::getStatsByRange($event, $statsStart, $eventEndInSeconds);

            for ($day = $statsStart; $day < $eventEndInSeconds; $day += $secondsInADay) {
                $statsYear = 2016;
                if ((int)date('n', $day) < 5) {
                    $statsYear++;
                }
                $count = (int) $this->getStatsByRange($event, $statsStart, $day);
                if ($lastCount != $count && $count < $totalForYear) {
                    $dayFormat = date("$statsYear-m-d H:i:s", $day);
                    $tmpData[] = [strtotime($dayFormat) * 1000, $count];
                }
                $lastCount = $count;
            }

            if (array_sum(array_column($tmpData, 1)) == 0) {
                continue;
            }

            if ($currentEvent->getId() != $event->getId()) {
                $cache->set($statsField, serialize($tmpData));
            }

            $tmp['name'] = $event->getYear();
            $tmp['data'] = $tmpData;
            $data[]      = $tmp;
        }

        return $data;
    }

    /**
     * @param Event $event
     * @param int $startSecs
     * @param int $endSecs
     * @return int
     */
    public function getStatsByRange($event, $startSecs, $endSecs) {
        $start = date('Y-m-d H:i:s', $startSecs);
        $end = date('Y-m-d H:i:s', $endSecs);

        $queryBuilder = $this->get('doctrine.orm.default_entity_manager')->createQueryBuilder();
        $queryBuilder
            ->select('count(r.id)')
            ->from(Registration::class, 'r')
            ->where('r.event = :event')
            ->andWhere('r.createdDate > :start')
            ->andWhere('r.createdDate <= :end')
            ->setParameter('event', $event)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
        ;

        try {
            return (int) $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * @param Event $event
     * @return int[]
     */
    public function graphByAge($event) {
        $queryBuilder = $this->get('doctrine.orm.default_entity_manager')->createQueryBuilder();
        $queryBuilder
            ->select('(year(CURRENT_TIMESTAMP()) - YEAR(r.birthday)) as age, count(r.id) as ageCount')
            ->from(Registration::class, 'r')
            ->where('r.event = :event')
            ->setParameter('event', $event)
            ->groupBy('age')
        ;

        $ageData = $queryBuilder->getQuery()->getArrayResult();

        $cleanedData = [
            ['name' => '1-5', 'y' => 0],
            ['name' => '6-9', 'y' => 0],
            ['name' => '10-12', 'y' => 0],
            ['name' => '13-17', 'y' => 0],
            ['name' => '18-20', 'y' => 0],
            ['name' => '21-25', 'y' => 0],
            ['name' => '25-29', 'y' => 0],
            ['name' => '30-39', 'y' => 0],
            ['name' => '40-49', 'y' => 0],
            ['name' => '50-59', 'y' => 0],
            ['name' => '60+', 'y' => 0],
        ];
        foreach ($ageData as $age) {
            $ageInt = (int) $age['age'];
            switch (true) {
                case $ageInt <= 1:
                    break;
                case $ageInt <= 5:
                    $cleanedData[0]['y'] += $age['ageCount'];
                    break;
                case $ageInt <= 9:
                    $cleanedData[1]['y'] += $age['ageCount'];
                    break;
                case $ageInt <= 12:
                    $cleanedData[2]['y'] += $age['ageCount'];
                    break;
                case $ageInt <= 17:
                    $cleanedData[3]['y'] += $age['ageCount'];
                    break;
                case $ageInt <= 20:
                    $cleanedData[4]['y'] += $age['ageCount'];
                    break;
                case $ageInt <= 25:
                    $cleanedData[5]['y'] += $age['ageCount'];
                    break;
                case $ageInt <= 29:
                    $cleanedData[6]['y'] += $age['ageCount'];
                    break;
                case $ageInt <= 39:
                    $cleanedData[7]['y'] += $age['ageCount'];
                    break;
                case $ageInt <= 49:
                    $cleanedData[8]['y'] += $age['ageCount'];
                    break;
                case $ageInt <= 59:
                    $cleanedData[9]['y'] += $age['ageCount'];
                    break;
                default:
                    $cleanedData[10]['y'] += $age['ageCount'];
                    break;
            }
        }

        return $cleanedData;
    }

    /**
     * @param Event $event
     * @return int[]
     */
    public function graphByZip($event) {
        $queryBuilder = $this->get('doctrine.orm.default_entity_manager')->createQueryBuilder();
        $queryBuilder
            ->select('r.zip, count(r.zip) as zipCount')
            ->from(Registration::class, 'r')
            ->where('r.event = :event')
            ->andWhere("r.zip != ''")
            ->setParameter('event', $event)
            ->groupBy('r.zip')
        ;

        $zipData = $queryBuilder->getQuery()->getArrayResult();

        $fipsFolder = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR
                . 'data' . DIRECTORY_SEPARATOR . 'fips' . DIRECTORY_SEPARATOR;

        $fipsStateJson = @file_get_contents($fipsFolder . 'stateFips.json', 'r');
        $fipsZipJson = @file_get_contents($fipsFolder . 'zip2fips.json', 'r');
        $fipsStateArray = json_decode($fipsStateJson, true);
        $fipsZipArray = json_decode($fipsZipJson, true);
        if ($fipsStateArray === false) {
            return [];
        }
        if ($fipsZipArray === false) {
            return [];
        }
        $fipsStateArray = array_flip($fipsStateArray);

        $rawData = [];

        foreach ($zipData as $zip) {
            $zipCode = substr(trim($zip['zip']),0, 5);
            if (!array_key_exists($zipCode, $fipsZipArray)) {
                continue;
            }
            $fipsId = $fipsZipArray[$zipCode];

            $stateCode = substr($fipsId, 0, 2);
            $stateLetters = strtolower($fipsStateArray[$stateCode]);
            $subCode = substr($fipsId, 2);

            $index = "us-$stateLetters-$subCode";
            if (!array_key_exists($index, $rawData)) {
                $rawData[$index] = [
                    'code' => $index,
                    'name' => $zipCode,
                    'value' => 0,
                ];
            }

            $rawData[$index]['value'] += $zip['zipCount'];
        }

        return array_values($rawData);
    }

    /**
     * @param Event[] $events
     * @param Event $selectedEvent
     * @return mixed
     */
    private function countByCurrentDate($events, $selectedEvent)
    {
        $countByCurrentDate = [];
        $selectedCount = 0;
        foreach ($events as $event) {
            $currentEvent = $this->getDoctrine()->getRepository(Event::class)->getCurrentEvent();
            $yearDifference = (int)$currentEvent->getYear() - $event->getYear();
            $end = date('Y-m-d H:i:s', strtotime('now'));
            if ($yearDifference < 0) {
                $end = date('Y-m-d H:i:s', strtotime("now +{$yearDifference} YEARS"));
            } elseif ($yearDifference > 0) {
                $end = date('Y-m-d H:i:s', strtotime("now -{$yearDifference} YEARS"));
            }

            $subQueryBuilder = $this->get('doctrine.orm.default_entity_manager')->createQueryBuilder();
            $subQuery = $subQueryBuilder
                ->select('IDENTITY(b.registration)')
                ->from(Badge::class, 'b')
                ->innerJoin('b.badgeStatus', 'bs')
                ->andWhere('bs.active = :active')
                ->getDQL();


            $queryBuilder = $this->get('doctrine.orm.default_entity_manager')->createQueryBuilder();
            $queryBuilder
                ->select('count(r.id)')
                ->from(Registration::class, 'r')
                ->where('r.event = :event')
                ->andwhere($queryBuilder->expr()->in('r.id', $subQuery))
                ->andWhere('r.createdDate <= :end')
                ->setParameter('active', true)
                ->setParameter('event', $event)
                ->setParameter('end', $end)
            ;
            try {
                $count = (int) $queryBuilder->getQuery()->getSingleScalarResult();
            } catch (NonUniqueResultException $e) {
                $count = 0;
            }

            if ($event->getId() == $selectedEvent->getId()) {
                $selectedCount = $count;
            }
            $tmp = [
                $event->getYear(),
                $count,
                0,
            ];
            if ($count != 0) {
                $countByCurrentDate[] = $tmp;
            }
        }

        for ($i = 0; $i < count($countByCurrentDate); $i++) {
            $countByCurrentDate[$i][2] = $selectedCount - (int)$countByCurrentDate[$i][1];
        }

        return $countByCurrentDate;
    }
}
