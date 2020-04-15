<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Controller\Registration;

use AppBundle\Entity\Badge;
use AppBundle\Entity\BadgeStatus;
use AppBundle\Entity\BadgeType;
use AppBundle\Entity\Event;
use AppBundle\Entity\Extra;
use AppBundle\Entity\Group;
use AppBundle\Entity\Registration;
use AppBundle\Entity\History;
use AppBundle\Entity\RegistrationShirt;
use AppBundle\Entity\RegistrationStatus;
use AppBundle\Entity\RegistrationType;
use AppBundle\Entity\Shirt;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class EditRegistrationController extends Controller
{

    /**
     * @Route("/registration/transfer/{transferredFrom}", name="registrationTransfer")
     * @Security("has_role('ROLE_REGSTAFF')")
     *
     * @param String $transferredFrom
     * @return Response
     */
    public function registrationTransfer($transferredFrom)
    {
        $registration = $this->getDoctrine()->getRepository(Registration::class)->find($transferredFrom);

        if (!$registration instanceof Registration) {
            return $this->redirectToRoute('listRegistrations');
        }

        if (!$registration->getRegistrationStatus()->getActive()) {
            $params = ['registrationId' => $registration->getRegistrationId()];
            return $this->redirectToRoute('app_registration_editregistration_editregistrationpage', $params);
        }

        return $this->editRegistrationPage(null, null, $transferredFrom);
    }

    /**
     * @Route("/registration/group/add/{groupId}", name="registrationAddToGroup")
     * @Security("has_role('ROLE_REGSTAFF')")
     *
     * @param String $groupId
     * @return Response
     */
    public function registrationGroup($groupId)
    {
        $group = $this->getDoctrine()->getRepository(Group::class)->find($groupId);

        if (!$group) {
            return $this->redirectToRoute('listRegistrations');
        }

        return $this->editRegistrationPage(null, $groupId);
    }

    /**
     * @Route("/registration/edit/", name="newRegistration")
     * @Route("/registration/edit/{registrationId}", name="actionEditRegistration")
     * @Route("/registration/edit/{registrationId}/{regGroupId}")
     * @Route("/registration/edit/{registrationId}/{regGroupId}/{transferredFrom}")
     * @Security("has_role('ROLE_REGSTAFF')")
     *
     * @param String $registrationId
     * @param String $groupId
     * @param String $transferredFrom
     * @return Response
     */
    public function editRegistrationPage($registrationId = null, $groupId = null, $transferredFrom = null)
    {
        $event = $this->getDoctrine()->getRepository(Event::class)->getSelectedEvent();
        $registration = null;
        if ($registrationId) {
            $registration = $this->getDoctrine()->getRepository(Registration::class)->find($registrationId);
        }

        $group = null;
        if ($groupId) {
            $group = $this->getDoctrine()->getRepository(Group::class)->find($groupId);
        }
        if ($registration && !$group) {
            // If its a valid registration, look for an existing group
            $group = $this->getDoctrine()->getRepository(Group::class)->getGroupFromRegistration($registration);
        }

        $transferredRegistration = null;
        if ($transferredFrom) {
            $transferredRegistration = $this->getDoctrine()->getRepository(Registration::class)->find($transferredFrom);
        }
        $transferredBadges = null;
        if ($transferredRegistration) {
            $transferredBadges = $transferredRegistration->getBadges();
        }

        $badges = [];
        if ($registration) {
            $badges = $registration->getBadges();
        } elseif ($transferredRegistration) {
            $badges = $transferredRegistration->getBadges();
        }
        $currentBadgeTypes = [];
        foreach ($badges as $badge) {
            /** @var Badge $badge */
            $currentBadgeTypes[] = $badge->getBadgeType()->getName();
        }

        $registrationTypeRepository = $this->getDoctrine()->getRepository(RegistrationType::class);
        /** @var RegistrationType[] $registrationTypes */
        $registrationTypes = $registrationTypeRepository->findAll();
        $selectedType = $registrationTypeRepository->getRegistrationTypeFromType('Mail');
        if ($group) {
            $selectedType = $registrationTypeRepository->getRegistrationTypeFromType('Group');
        } elseif ($registration) {
            $selectedType = $registration->getRegistrationtype();
        }

        /** @var Extra[] $registrationTypes */
        $extras = $this->getDoctrine()->getRepository(Extra::class)->findAll();
        /** @var Group[] $registrationTypes */
        $groups = $this->getDoctrine()->getRepository(Group::class)->findAll();
        $registrationStatuses = $this->getDoctrine()->getRepository(RegistrationStatus::class)->findAll();

        $vars = [
            'event' => $event,
            'registrationTypes' => $registrationTypes,
            'selectedType' => $selectedType,
            'group' => $group,
            'groups' => $groups,
            'registrationStatuses' => $registrationStatuses,
            'registration' => $registration,
            'isStaff' => $this->getDoctrine()->getRepository(Badge::class)->isStaff($registration),
            'currentBadgeTypes' => $currentBadgeTypes,
            'badges' => $badges,
            'transferredRegistration' => $transferredRegistration,
            'transferredBadges' => $transferredBadges,
            'extras' => $extras,
        ];

        return $this->render('registration/editRegistration.html.twig', $vars);
    }

    /**
     * @Route("/getregshirtlist/{registrationId}")
     * @Security("has_role('ROLE_REGSTAFF')")
     *
     * @param String $registrationId
     * @return JsonResponse
     */
    public function getRegistrationShirts($registrationId)
    {
        $arrayShirts = [];
        $registration = null;
        if ($registrationId) {
            $registration = $this->getDoctrine()->getRepository(Registration::class)->find($registrationId);
        }
        if ($registration instanceof Registration) {
            $registrationShirts = $registration->getRegistrationShirts();

            foreach ($registrationShirts as $registrationShirt) {
                $shirt = $registrationShirt->getShirt();

                $tmp = array();
                $tmp['registrationShirtId'] = $registrationShirt->getRegistrationShirtId();
                $tmp['shirtType'] = $shirt->getType();
                $tmp['shirtSize'] = $shirt->getSize();
                $arrayShirts[] = $tmp;
            }
        }

        return new JsonResponse($arrayShirts);
    }

    /**
     * @Route("/ajaxstaffmodify/{registrationId}/{action}")
     * @Security("has_role('ROLE_REGSTAFF')")
     *
     * @param String $registrationId
     * @param String $action
     * @return JsonResponse
     */
    public function modifyStaffStatus($registrationId, $action)
    {
        $returnData = array();
        $returnData['success'] = false;
        $returnData['message'] = 'Invalid Registration ID. Save your badge first.';

        $entityManager = $this->getDoctrine()->getManager();

        $registration = $entityManager->getRepository(Registration::class)->find($registrationId);
        if ($registration && ($action == 'add' || $action == 'remove')) {
            $badgeType = $entityManager->getRepository(BadgeType::class)->getBadgeTypeFromType('STAFF');
            if ($action == 'remove') {
                $badges = $registration->getBadges();
                foreach ($badges as $badge) {
                    /** @var Badge $badge */
                    if ($badge->getBadgeType()->getBadgeTypeId() == $badgeType->getBadgeTypeId()) {
                        $entityManager->remove($badge);
                        $returnData['success'] = true;
                        $returnData['message'] = 'Staff badge deleted';
                    }
                }
            } elseif ($action == 'add') {
                $badgeStatus = $entityManager->getRepository(BadgeStatus::class)->getBadgeStatusFromStatus('NEW');
                $badge = new Badge();
                $badge->setRegistration($registration);
                $badge->setBadgeType($badgeType);
                $badge->setBadgeStatus($badgeStatus);
                $badge->setNumber($entityManager->getRepository(Badge::class)->generateNumber());
                $entityManager->persist($badge);
                $returnData['success'] = true;
                $returnData['message'] = 'Staff badge created';
            }
        }

        if ($registration->getRegistrationId() && $returnData['success']) {
            $registrationHistory = new History();
            $registrationHistory->setRegistration($registration);
            $registrationHistory->setChangetext($returnData['message']);
            $entityManager->persist($registrationHistory);

            $entityManager->flush();
        }

        return new JsonResponse($returnData);
    }

    /**
     * @Route("/editregshirtlist/{registrationId}/{action}")
     * @Security("has_role('ROLE_REGSTAFF')")
     *
     * @param Request $request
     * @param String $registrationId
     * @param String $action
     * @return JsonResponse
     */
    public function modifyTShirts(Request $request, $registrationId, $action)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $returnData = array();
        $returnData['success'] = false;
        $returnData['message'] = '';

        $registration = $entityManager->getRepository(Registration::class)->find($registrationId);
        if (!$registration) {
            $returnData['message'] = 'Invalid Registration ID';

            return new JsonResponse($returnData);
        }

        if ($action == 'add') {
            if ($request->query->has('shirtType')
                && $request->query->has('shirtSize')
            ) {
                $shirtType = $request->query->get('shirtType');
                $shirtSize = $request->query->get('shirtSize');

                $shirt = $entityManager->getRepository(Shirt::class)->getShirtFromTypeAndSize($shirtType, $shirtSize);
                if (!$shirt) {
                    $returnData['message'] = 'Error: Invalid Type/Size';

                    return new JsonResponse($returnData);
                }

                $registrationShirt = new RegistrationShirt();
                $registrationShirt->setRegistration($registration);
                $registrationShirt->setShirt($shirt);
                $entityManager->persist($registrationShirt);
                $entityManager->flush();

                if ($registrationShirt->getRegistrationshirtId()) {
                    $returnData['success'] = true;
                    $returnData['message'] = 'Added shirt size ' . $shirtType . ' ' . $shirtSize . ' to registration';
                    $returnData['registrationShirtId'] = $registrationShirt->getRegistrationshirtId();
                }
            }
        } else if ($action == 'delete') {
            if ($request->query->has('registrationShirtId')) {
                $registrationShirtId = $request->query->get('registrationShirtId');
                $registrationShirt = $this->getDoctrine()
                    ->getRepository(RegistrationShirt::class)
                    ->find($registrationShirtId);
                /** @var Shirt $shirt */
                $shirt = $registrationShirt->getShirt();

                $entityManager->remove($registrationShirt);
                $entityManager->flush();

                $returnData['message'] = 'Deleted shirt size ' . $shirt->getType() . ' ' . $shirt->getSize() . ' from registration';
                $returnData['success'] = true;
            }
        }

        if ($registration->getRegistrationId() && $returnData['success']) {
            $registrationHistory = new History();
            $registrationHistory->setRegistration($registration);
            $registrationHistory->setChangetext($returnData['message']);
            $entityManager->persist($registrationHistory);

            $entityManager->flush();
        }

        return new JsonResponse($returnData);
    }

    /**
     * @Route("/registration/list/extra/{registrationId}")
     * @Security("has_role('ROLE_REGSTAFF')")
     *
     * @param String $registrationId
     * @return JsonResponse
     */
    public function getRegistrationExtras($registrationId)
    {
        $arrayExtras = [];
        $registration = $this->getDoctrine()->getRepository(Registration::class)->find($registrationId);
        if ($registration) {
            /** @var Extra[] $extras */
            $extras = $registration->getExtras();

            foreach ($extras as $extra) {
                $tmp = array();
                $tmp['extraId'] = $extra->getExtraId();
                $tmp['extra'] = $extra->getName();
                $arrayExtras[] = $tmp;
            }
        }

        return new JsonResponse($arrayExtras);
    }

    /**
     * @Route("/registration/ajax/extra/{registrationId}/{action}")
     * @Security("has_role('ROLE_REGSTAFF')")
     *
     * @param Request $request
     * @param String $registrationId
     * @param String $action
     * @return JsonResponse
     */
    public function modifyExtras(Request $request, $registrationId, $action)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $returnData = array();
        $returnData['success'] = false;
        $returnData['message'] = '';

        $registration = $this->getDoctrine()->getRepository(Registration::class)->find($registrationId);
        if (!$registration) {
            $returnData['message'] = 'Invalid Registration ID';

            return new JsonResponse($returnData);
        }

        if ($action == 'add') {
            if ($request->query->has('extra')) {
                $extraName = $request->query->get('extra');

                $extra = $this->getDoctrine()->getRepository(Extra::class)->getExtraFromName($extraName);
                if (!$extra) {
                    $returnData['message'] = 'Error: Invalid Extra';

                    return new JsonResponse($returnData);
                }

                /** @var Extra[] $extras */
                $extras = $registration->getExtras();
                foreach ($extras as $foundExtra) {
                    if ($foundExtra->getExtraId() == $extra->getExtraId()) {
                        $returnData['message'] = 'Extra already added to Registration!';

                        return new JsonResponse($returnData);
                    }
                }

                $registration->addExtra($extra);
                $entityManager->flush();

                $returnData['success'] = true;
                $returnData['message'] = "Added extra $extraName to registration";
                $returnData['extraId'] = $extra->getExtraId();
            }
        } elseif ($action == 'delete') {
            if ($request->query->has('extraId')) {
                $extraId = $request->query->get('extraId');
                $extra = $this->getDoctrine()->getRepository(Extra::class)->find($extraId);

                $registration->removeExtra($extra);

                $entityManager->flush();

                $returnData['message'] = "Deleted extra {$extra->getName()} from registration";
                $returnData['success'] = true;
            }
        }

        if ($registration->getRegistrationId() && $returnData['success']) {
            $registrationHistory = new History();
            $registrationHistory->setRegistration($registration);
            $registrationHistory->setChangetext($returnData['message']);
            $entityManager->persist($registrationHistory);

            $entityManager->flush();
        }

        return new JsonResponse($returnData);
    }

    /**
     * @Route("/registration/ajax/edit")
     * @Security("has_role('ROLE_REGSTAFF')")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxEditRegistration(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $event = $entityManager->getRepository(Event::class)->getSelectedEvent();

        $returnJson = array();
        $returnJson['success'] = false;
        $returnJson['Year'] = $event->getYear();

        $badgeTypeName = $request->request->get('badgeTypeName');

        $all_fields_sent = true;
        $fields = array(
            'lastName' => 'last name',
            'firstName' => 'first name',
            'middleName' => 'middle name',
            'address' => 'address',
            'address2' => 'address2',
            'city' => 'city',
            'state' => 'state',
            'zip' => 'zip',
            'phone' => 'phone number',
            'email' => 'e-mail address',
            'badgeName' => 'badge name',
            'birthDate' => 'birthday',
            'birthYear' => 'birth year',
        );
        if (!$request->request->has('registrationId')) {
            $all_fields_sent = false;
            $returnJson['message'] = 'registrationId was not set.';
        }
        if (!$request->request->has('badgeTypeName') || !$request->request->get('badgeTypeName')) {
            $all_fields_sent = false;
            $returnJson['message'] = 'Badge Type was not set.';
        }
        if (!$request->request->has('RegistrationType') || !$request->request->get('RegistrationType')) {
            $all_fields_sent = false;
            $returnJson['message'] = 'RegistrationType was not set.';
        }
        if (!$request->request->has('RegistrationStatus') || !$request->request->get('RegistrationStatus')) {
            $all_fields_sent = false;
            $returnJson['message'] = 'RegistrationStatus was not set.';
        }

        if (!$request->request->has('lastName') || !$request->request->get('lastName')) {
            $all_fields_sent = false;
            $returnJson['message'] = 'Last name was not set.';
        }

        $registrationType = $this->getDoctrine()->getRepository(RegistrationType::class)
            ->getRegistrationTypeFromType($request->request->get('RegistrationType'));
        if (!$registrationType) {
            $all_fields_sent = false;
            $returnJson['message'] = "RegistrationType '{$request->request->get('RegistrationType')}' didn't exist. Configuration Error.";
        }

        $tmpBadgeTypeName = 'ADREGSTANDARD';
        if ($request->request->has('badgeTypeName')) {
            $tmpBadgeTypeName = $request->request->get('badgeTypeName');
        }

        $attendeeBadgeTypes = [
            'ADREGSTANDARD',
            'MINOR',
            'ADREGSPONSOR',
            'ADREGCOMMSPONSOR',
        ];
        if (in_array($tmpBadgeTypeName, $attendeeBadgeTypes)){
            if (!$request->request->has('birthDate') || $request->request->get('birthDate') == '') {
                $all_fields_sent = false;
                $returnJson['message'] = 'Birthday was not set.';
            }
            if (!$request->request->has('birthYear') || $request->request->get('birthYear') == '') {
                $all_fields_sent = false;
                $returnJson['message'] = 'Birth year was not set.';
            }
        }

        $registrationStatus = $this->getDoctrine()->getRepository(RegistrationStatus::class)
            ->getRegistrationStatusFromStatus($request->request->get('RegistrationStatus'));
        if (strpos($badgeTypeName, 'ATCMEMBERSHIP') !== false) {
            $registrationStatus = $this->getDoctrine()->getRepository(RegistrationStatus::class)
                ->getRegistrationStatusFromStatus('ATC');
            if (!$registrationStatus) {
                $all_fields_sent = false;
                $returnJson['message'] = "RegistrationStatus 'ATC' didn't exist. Configuration Error.";
            }
        }
        if (!$registrationStatus) {
            $all_fields_sent = false;
            $returnJson['message'] = "RegistrationStatus '{$request->request->get('RegistrationStatus')}' didn't exist. Configuration Error.";
        }
        foreach ($fields as $field => $fieldName) {
            if (!$request->request->has($field)) {
                $all_fields_sent = false;
                $returnJson['message'] = $fieldName . ' was not set.';
                break;
            }
        }

        $transferredFrom = null;
        if ($request->request->has('TransferredFrom')) {
            $transferredFrom = $this->getDoctrine()->getRepository(Registration::class)
                ->find($request->request->get('TransferredFrom'));
        }

        $history = '';
        if ($all_fields_sent) {
            $registration = $this->getDoctrine()->getRepository(Registration::class)
                ->find($request->request->get('registrationId'));

            if (!$registration) {
                $registration = new Registration();

                $registration->setEvent($event);
                $history .= 'Reg Type: ' . $registrationType->getName() . '<br>';
                $history .= 'Reg Status: ' . $registrationStatus->getStatus() . '<br>';
            } else {
                /** @var RegistrationType $oldRegType */
                $oldRegType = $registration->getRegistrationType();
                if ($oldRegType->getRegistrationTypeId() != $registrationType->getRegistrationTypeId()) {
                    $history .= 'Reg Type: ' . $oldRegType->getName() . ' => ' . $registrationType->getName() . '<br>';
                }
                /** @var RegistrationStatus $oldRegStatus */
                $oldRegStatus = $registration->getRegistrationstatus();
                if ($oldRegStatus->getRegistrationStatusId() != $registrationStatus->getRegistrationStatusId()) {
                    $history .= 'Reg Status: ' . $oldRegStatus->getStatus() . ' => ' . $registrationStatus->getStatus() . '<br>';
                }
            }
            $registration->setRegistrationstatus($registrationStatus);
            $registration->setRegistrationtype($registrationType);

            if ($registrationType->getName() != 'Group') {
                $groups = $registration->getGroups();
                foreach ($groups as $group) {
                    $history .= "Group Removed: {$group->getName()}<br>";
                    $registration->removeGroup($group);
                }
                $entityManager->flush();
            }

            $regGroup = null;
            if ($registrationType->getName() == 'Group'
                && $request->request->has('groupId')
            ) {
                $regGroup = $this->getDoctrine()->getRepository(Group::class)
                    ->find($request->request->get('groupId'));
            }

            foreach ($fields as $field => $fieldName) {
                if ($field == 'birthYear') {

                    continue;
                }
                if ($field == 'birthDate') {
                    $tmpfield = $request->request->get('birthDate') . '/' . $request->request->get('birthYear');
                    if (!strtotime($tmpfield)) {
                        $tmpfield = str_replace('-', '/', $tmpfield);
                    }
                    try {
                        $newDate = new \DateTime($tmpfield);
                        if ($registration->getRegistrationId()) {
                            $setBirthDate = $registration->getBirthday();
                            if ($setBirthDate) {
                                $oldDate = $setBirthDate->format('m/d/y');
                                if ($oldDate != $newDate->format('m/d/y')) {
                                    $history .= "$field: $oldDate => {$newDate->format('m/d/y')}<br>";
                                }
                            } else {
                                $newDate = $setBirthDate;
                            }
                        }
                        $registration->setBirthday($newDate);
                    }
                    catch (\Exception $e) {

                    }

                    continue;
                }
                $value = $request->request->get($field);
                $fieldLowerSet = 'set'.ucfirst(strtolower($field));
                $fieldLowerGet = 'get'.ucfirst(strtolower($field));
                if ($registration->$fieldLowerGet() != $_REQUEST[$field]) {
                    $history .= "$field: {$registration->$fieldLowerGet()} => $value<br>";
                }
                $registration->$fieldLowerSet($value);
            }

            if ($registration->getBadgeName() == '') {
                $registration->setBadgeName($registration->getFirstName());
                $history .= "(blank) badge name: {$registration->getBadgeName()} => {$registration->getBadgeName()}<br>";
            }

            $registration->setContactVolunteer(false);
            if ($request->request->has('volunteer') && $request->request->get('volunteer')) {
                $registration->setContactVolunteer(true);
            }
            $registration->setContactNewsletter(false);
            if ($request->request->has('newsletter') && $request->request->get('newsletter')) {
                $registration->setContactNewsletter(true);
            }

            $allow_one_badge_types = array(
                'ADREGSTANDARD',
                'MINOR',
                'CHILD',
                'ADREGSPONSOR',
                'ADREGCOMMSPONSOR',
                'GUEST',
                'VENDOR',
                'EXHIBITOR',
                'ATCMEMBERSHIP',
            );

            $toDelete = [];
            $badgetypeFound = false;

            $badges = $registration->getBadges();
            foreach ($badges as $badge) {
                $badgeType = $badge->getBadgeType();
                if (in_array($badgeType->getName(), $allow_one_badge_types) && $badgeType->getName() != $badgeTypeName) {
                    $toDelete[] = $badge;
                    $history .= "BadgeType: {$badgeType->getName()} => $badgeTypeName<br>";
                }
                if ($badgeType->getName() == $badgeTypeName) {
                    $badgetypeFound = true;
                }
            }
            if (!$registration->getRegistrationId()) {
                $regNumber = $this->getDoctrine()
                    ->getRepository(Registration::class)
                    ->generateNumber($registration);
                $registration->setNumber($regNumber);
            } else {
                $regNumber = $registration->getNumber();
                // If the first letter of the last name changes, change the badge number
                if (substr($regNumber, 0, 1) !== substr($registration->getLastName(), 0, 1)) {
                    $regNumber = substr($registration->getLastName(), 0, 1)
                        . substr($regNumber, -4, 4);
                    $registration->setNumber($regNumber);
                }

            }

            if ($transferredFrom) {
                $transferredFrom->setTransferredTo($registration);
                $url = $this->generateUrl('viewRegistration', ['registrationId' => $transferredFrom->getRegistrationId()]);
                $history .= " Transferred From <a href='$url'>" . $transferredFrom->getFirstName()
                    . ' ' . $transferredFrom->getLastName() . '</a>. <br>';
            }

            $entityManager->persist($registration);
            $entityManager->flush();

            if ($transferredFrom) {
                $transferredRegistrationStatus = $this->getDoctrine()->getRepository(RegistrationStatus::class)
                    ->getRegistrationStatusFromStatus('Transferred');
                $transferredFrom->setRegistrationstatus($transferredRegistrationStatus);
                $entityManager->persist($transferredFrom);

                $transferredFromHistory = '';
                $groups = $transferredFrom->getGroups();
                foreach ($groups as $group) {
                    /** @var $group Group */
                    $transferredFromHistory .= "Group Removed: {$group->getName()}<br>";
                    $transferredFrom->removeGroup($group);
                }

                $registrationHistory = new History();
                $registrationHistory->setRegistration($transferredFrom);
                $url = $this->generateUrl('viewRegistration', ['registrationId' => $registration->getRegistrationId()]);
                $transferredToText= "<br>Registration Transferred to "
                    ."<a href='$url'>"
                    ."{$registration->getFirstName()} {$registration->getLastName()}</a>";
                $registrationHistory->setChangeText($transferredFromHistory . $transferredToText);
                $entityManager->persist($registrationHistory);
                $entityManager->flush();
            }

            if ($regGroup) {
                $oldGroups = $registration->getGroups();
                $groupFound = false;
                foreach ($oldGroups as $oldGroup) {
                    if ($oldGroup->getGroupId() == $regGroup->getGroupId()) {
                        $groupFound = true;
                    }
                }
                if (!$groupFound) {
                    foreach ($oldGroups as $oldGroup) {
                        $history .= "Group Removed: {$oldGroup->getName()}<br>";
                        $registration->removeGroup($oldGroup);
                    }
                    $registration->addGroup($regGroup);

                    $history .= "Group Added: {$regGroup->getName()}<br>";
                    $entityManager->flush();
                }
            }

            if ($registration->getRegistrationId() && (count($toDelete) > 0 || !$badgetypeFound)) {
                foreach ($toDelete as $badge) {
                    $entityManager->remove($badge);
                }

                $badgeStatus = $this->getDoctrine()->getRepository(BadgeStatus::class)
                    ->getBadgeStatusFromStatus('NEW');
                if (strpos($badgeTypeName, 'ATCMEMBERSHIP') !== false) {
                    $badgeStatus = $this->getDoctrine()->getRepository(BadgeStatus::class)
                        ->getBadgeStatusFromStatus('ATC');
                }
                $badgeType = $this->getDoctrine()->getRepository(BadgeType::class)
                    ->getBadgeTypeFromType($badgeTypeName);
                $badge = new Badge();
                $badge->setRegistration($registration);
                $badge->setBadgeType($badgeType);
                $badge->setBadgeStatus($badgeStatus);
                $badgeNumber = $this->getDoctrine()->getRepository(Badge::class)->generateNumber();
                $badge->setNumber($badgeNumber);
                $history .= "BadgeType: Added Badge Type: {$badgeType->getName()}<br>";
                $entityManager->persist($badge);
                $entityManager->flush();
            }

            if (strpos($badgeTypeName, 'ATCMEMBERSHIP') === false) {
                // Do not send for ATC Memberships
                $this->get('util_email')->generateAndSendConfirmationEmail($registration);
            }

            $registrationHistory = new History();
            $registrationHistory->setRegistration($registration);

            if ($request->request->has('comments') && $request->request->get('comments')) {
                if ($history) {
                    $history .= '<br><br>';
                }
                $history .= '<b>Comment:</b> ' . nl2br($request->request->get('comments'));
            }

            $registrationHistory->setChangetext($history);

            if ($history) {
                $entityManager->persist($registrationHistory);
                $entityManager->flush();
            }

            $returnJson['success'] = true;
            $returnJson['message'] = 'Registration Updated!';

            $returnJson['registrationId'] = $registration->getRegistrationId();
            $returnJson['Number'] = $registration->getNumber();
        }

        return new JsonResponse($returnJson);
    }
}
