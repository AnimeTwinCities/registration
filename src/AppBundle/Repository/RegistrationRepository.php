<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Badge;
use AppBundle\Entity\BadgeType;
use AppBundle\Entity\Event;
use AppBundle\Entity\Group;
use AppBundle\Entity\Registration;
use AppBundle\Entity\RegistrationStatus;
use AppBundle\Entity\RegistrationType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\Expr\Join;

class RegistrationRepository extends EntityRepository
{
    /**
     * MyEntity repository.
     *
     * @method Registration|null find
     */

    /**
     * @param Registration $registration
     */
    public function generateConfirmationNumber(Registration $registration)
    {
        if ($registration->getConfirmationnumber()) {

            return; // Already have a number, so we return.
        }
        $unique = substr(md5(uniqid(rand(), true)), 16, 16);
        $confirmationNumber = substr($unique, 0, 8) . substr($registration->getNumber(), 1, 2)
            . substr($unique, 8, 2) . substr($registration->getNumber(), 3, 2)
            . substr($unique, 10, 2);
        $registration->setConfirmationnumber($confirmationNumber);

        try {
            $this->getEntityManager()->persist($registration);
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            // TODO: Handle Exception later
        } catch (ORMException $e) {
            // TODO: Handle Exception later
        }

        return;
    }

    public function generateNumber(Registration $registration)
    {
        $event = $registration->getEvent();
        if (!$event) {
            $event = $this->getEntityManager()->getRepository(Event::class)->getSelectedEvent();
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        try {
            $count = $queryBuilder->select('count(r.registrationId)')
                ->from(Registration::class, 'r')
                ->where('r.event = :event')
                ->setParameter('event', $event)
                ->getQuery()->getSingleScalarResult();
        } catch (NonUniqueResultException $e) {
            // TODO: Handle Exception later
        } catch (NoResultException $e) {
            // TODO: Handle Exception later
        }

        // Get count from only row returned.
        $number = ucwords(substr($registration->getLastname(), 0, 1))
            . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return $number;
    }

    /**
     * @param String $searchText
     * @param String $page
     * @param RegistrationType|null $registrationType
     * @param RegistrationStatus|null $registrationStatus
     * @param BadgeType|null $badgeType
     * @return Registration[]
     */
    public function searchFromManageRegistrations(
        $searchText,
        $page,
        ?RegistrationType $registrationType,
        ?RegistrationStatus $registrationStatus,
        ?BadgeType $badgeType
    )
    {
        $event = $this->getEntityManager()->getRepository(Event::class)->getSelectedEvent();
        $page = (int) $page;

        $badgeListQueryBuilder = $this->getEntityManager()->createQueryBuilder();
        $badgeListQueryBuilder->select('GROUP_CONCAT(DISTINCT IDENTITY(sb.badgeType) SEPARATOR \' \')')
            ->from(Badge::class, 'sb')
            ->where('r.id = sb.registration')
        ;

        $badgeListQuery = $badgeListQueryBuilder->getDQL();

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('r.id', 'r.number', 'r.email', 'r.firstName', 'r.lastName', 'r.badgeName'
            , 'r.confirmationNumber', 'rs.status', 'e.year', 'r.contactVolunteer', 'r.contactNewsletter'
            , 'COUNT(g.id) as group_count'
            , "($badgeListQuery)"
        )
            ->from(Registration::class, 'r')
            ->innerJoin('r.registrationStatus', 'rs')
            ->innerJoin('r.event', 'e')
            ->innerJoin(Badge::class, 'b', Join::WITH, 'r.id = b.registration')
            ->leftJoin('r.groups', 'g')

            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->like('r.number', ':searchText'),
                $queryBuilder->expr()->like('r.lastName', ':searchTextLast'),
                $queryBuilder->expr()->like('r.firstName', ':searchTextFirst'),
                $queryBuilder->expr()->like('r.badgeName', ':searchTextBadge'),
                $queryBuilder->expr()->like('r.email', ':searchTextEmail'),
                $queryBuilder->expr()->like('r.confirmationNumber', ':searchTextConfirmation')
            ))
            ->setParameter('searchText', $searchText)
            ->setParameter('searchTextLast', $searchText . '%')
            ->setParameter('searchTextFirst', $searchText . '%')
            ->setParameter('searchTextBadge', $searchText . '%')
            ->setParameter('searchTextEmail', $searchText . '%')
            ->setParameter('searchTextConfirmation', $searchText . '%')

            ->andWhere('r.event = :eventId')
            ->setParameter('eventId', $event->getId())
        ;

        $queryBuilder
            ->orderBy('r.lastName', 'ASC')
            ->addOrderBy('r.firstName' , 'ASC')
            ->groupBy('r.id')
        ;

        $results = $queryBuilder->getQuery()->getArrayResult();

        $total_results = count($results);
        $results = array_slice($results, ($page - 1) * 100, 100);
        $returnJson['count_total'] = $total_results;
        $returnJson['count_returned'] = count($results);

        $return_results = array();
        foreach ($results as $result) {
            $Badges = explode(' ', $result['1']);

            $tmp = array();
            $tmp['Registration_ID'] = $result['id'];
            $tmp['ConfirmationNumber'] = $result['confirmationNumber'];
            $tmp['Email'] = $result['email'];
            $tmp['Year'] = $result['year'];
            $tmp['Number'] = $result['number'];
            $tmp['FirstName'] = $result['firstName'];
            $tmp['LastName'] = $result['lastName'];
            $tmp['BadgeName'] = $result['badgeName'];
            $tmp['Reg_Status'] = $result['status'];
            $tmp['group'] = '';
            if ($result['group_count'] > 0) {
                $tmp['group'] = 'X';
            }
            $tmp['Volunteer'] = '';
            if ($result['contactVolunteer']) {
                $tmp['Volunteer'] = 'X';
            }
            $tmp['Newsletter'] = '';
            if ($result['contactNewsletter']) {
                $tmp['Newsletter'] = 'X';
            }

            $tmp['is_adult'] = 0;
            $tmp['is_minor'] = 0;
            $tmp['is_sponsor'] = 0;
            $tmp['is_comsponsor'] = 0;
            $tmp['is_guest'] = 0;
            $tmp['is_vendor'] = 0;
            $tmp['is_staff'] = 0;
            $tmp['is_exhibitor'] = 0;

            foreach ($Badges as $Badge) {
                switch ($Badge) {
                    case 1:
                        $tmp['is_adult'] = 1;
                        break;
                    case 2:
                        $tmp['is_minor'] = 1;
                        break;
                    case 3:
                        $tmp['is_sponsor'] = 1;
                        break;
                    case 4:
                        $tmp['is_comsponsor'] = 1;
                        break;
                    case 5:
                        $tmp['is_guest'] = 1;
                        break;
                    case 6:
                        $tmp['is_vendor'] = 1;
                        break;
                    case 7:
                        $tmp['is_staff'] = 1;
                        break;
                    case 8:
                        $tmp['is_exhibitor'] = 1;
                        break;
                }
            }
            $return_results[] = $tmp;
        }
        $returnJson['page'] = $page;
        $returnJson['results'] = $return_results;

        return $returnJson;
    }

    /**
     * @param String $firstName
     * @param String $lastName
     * @param String $birthYear
     * @param Event $event
     * @return Registration[]
     */
    public function getFromFirstLastBirthYear($firstName, $lastName, $birthYear, Event $event)
    {
        if (!$event) {
            $event = $this->getEntityManager()->getRepository(Event::class)->getSelectedEvent();
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Registration::class, 'r')
            ->where("r.firstName = :firstName")
            ->andWhere('r.lastName = :lastName')
            ->andWhere('YEAR(r.birthday) = :birthYear')
            ->andWhere('r.event = :event')
            ->setParameter('firstName', $firstName)
            ->setParameter('lastName', $lastName)
            ->setParameter('birthYear', $birthYear)
            ->setParameter('event', $event->getId());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param String $firstName
     * @param String $lastName
     * @param String $email
     * @param Event $event
     * @return Registration[]
     */
    public function getFromFirstLastEmail($firstName, $lastName, $email, Event $event)
    {
        if (!$event) {
            $event = $this->getEntityManager()->getRepository(Event::class)->getSelectedEvent();
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Registration::class, 'r')
            ->where("r.firstName = :firstName")
            ->andWhere('r.lastName = :lastName')
            ->andWhere('r.email = :email')
            ->andWhere('r.event = :event')
            ->setParameter('firstName', $firstName)
            ->setParameter('lastName', $lastName)
            ->setParameter('email', $email)
            ->setParameter('event', $event->getId());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Event $event
     * @return Registration[]
     */
    public function getRegistrationsLessThanAYearOld(Event $event)
    {
        if (!$event) {
            $event = $this->getEntityManager()->getRepository(Event::class)->getSelectedEvent();
        }

        $birthday = date("Y-m-d", strtotime($event->getStartdate()->format('Y-m-d') . " - 1 year"));

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Registration::class, 'r')
            ->where("r.birthday > :birthday")
            ->andWhere('r.event = :event')
            ->setParameter('birthday', $birthday)
            ->setParameter('event', $event->getId());

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Group $group
     * @param Event $event
     * @return Registration[]
     */
    public function getRegistrationsFromGroup(Group $group = null, Event $event = null)
    {
        if (!$event) {
            $event = $this->getEntityManager()->getRepository(Event::class)->getSelectedEvent();
        }
        if (!$group) {
            return [];
        }
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Registration::class, 'r')
            ->innerJoin('r.groups', 'g')
            ->where("g.id = :groupId")
            ->andWhere("r.event = :event")
            ->setParameter('groupId', $group)
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param bool $showStaff
     * @param Event|null $event
     * @return Registration[]
     */
    public function findRegistrationsWithShirts($showStaff = false, Event $event = null) : array
    {
        if (!$event) {
            $event = $this->getEntityManager()->getRepository(Event::class)->getSelectedEvent();
        }
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Registration::class, 'r')
            ->innerJoin('r.registrationShirts', 'rs')
            ->where("r.event = :event")
            ->setParameter('event', $event)
        ;

        if (!$showStaff) {
            $staffBadge = $this
                ->getEntityManager()
                ->getRepository(BadgeType::class)
                ->getBadgeTypeFromType('STAFF');
            $allStaffBadges = $this->getEntityManager()->createQueryBuilder()
                ->select('IDENTITY(b.registration)')
                ->from('AppBundle:Badge', 'b')
                ->where("b.badgeType = :staffType")
                ->getDQL();

            $queryBuilder
                ->andWhere($queryBuilder->expr()->notIn('r.id', $allStaffBadges))
                ->setParameter('staffType', $staffBadge->getBadgetypeId());
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param String $confirmation
     * @param Event $event
     * @return Registration|null
     */
    public function getFromConfirmation($confirmation, Event $event)
    {
        if (!$event) {
            $event = $this->getEntityManager()->getRepository(Event::class)->getCurrentEvent();
        }

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder->select('r')
            ->from('AppBundle:Registration', 'r')
            ->where("r.confirmationnumber = :confirmationnumber")
            ->andWhere('r.event = :event')
            ->setParameter('confirmationnumber', $confirmation)
            ->setParameter('event', $event->getId());

        try {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }
}