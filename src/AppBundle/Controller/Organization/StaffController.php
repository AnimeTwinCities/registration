<?php
/**
 * Copyright (c) 2019. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

declare(strict_types=1);

namespace AppBundle\Controller\Organization;

use AppBundle\Entity\Organization\Staff;
use AppBundle\Entity\Organization\StaffDepartment;
use AppBundle\Entity\Organization\StaffHistory;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StaffController extends Controller
{
    /**
     * @Route("/org/staff/edit/", name="org_staff_edit_new")
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     * @return Response
     */
    public function editStaff(Request $request)
    {
        $id = '';
        if ($request->query->has('id')) {
            $id = $request->query->get('id');
        }
        $parameters = [];

        $staff = $this->getDoctrine()->getRepository(Staff::class)->find($id);

        /** @var Staff */
        $parameters['staff'] = null;
        $parameters['departments'] = [];
        $parameters['staffHistory'] = [];
        if ($staff) {
            $parameters['staff'] = $staff;

            /** @var StaffDepartment[] $departments */
            $departments = $staff->getDepartments();
            $primaryDepartment = null;
            $otherDepartments = [];
            foreach ($departments as $department) {
                if ($department->isPrimary()) {
                    $primaryDepartment = $department;
                } else {
                    $departmentName = $department->getDepartment()->getName();
                    if ($department->getPosition()) {
                        $departmentName .= " ({$department->getPosition()})";
                    }
                    $otherDepartments[] = $departmentName;
                }
            }

            if ($primaryDepartment) {
                /** @var StaffDepartment[] $parameters ['departments'] */
                $parameters['departments'] = array_merge([$primaryDepartment], $otherDepartments);
            }

            $parameters['staffHistory'] = $staff->getHistory();
        }

        return $this->render('organization/staffEdit.html.twig', $parameters);
    }

    /**
     * @Route("/ajax/org/staff/edit/", name="org_staff_edit")
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     * @return Response
     */
    public function ajaxEditStaff(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $returnJson = array();
        $returnJson['success'] = false;


        $missingFields = [];
        $fields = [
            'lastName' => [
                'field' => 'last name',
                'required' => true,
            ],
            'firstName' => [
                'field' => 'first name',
                'required' => true,
            ],
            'nickName' => [
                'field' => 'nick name',
                'required' => false,
            ],
            'officialEmail' => [
                'field' => 'official email',
                'required' => false,
            ],
            'personalEmail' => [
                'field' => 'personal email',
                'required' => true,
            ],
            'phoneNumber' => [
                'field' => 'phone',
                'required' => true,
            ],
            'birthDate' => [
                'field' => 'birth date',
                'required' => true,
                'birthday' => true,
            ],
            'birthYear' => [
                'field' => 'birth year',
                'required' => true,
                'birthday' => true,
            ],
            'shirtType' => [
                'field' => 'shirt type',
                'required' => false,
            ],
            'shirtSize' => [
                'field' => 'shirt size',
                'required' => false,
            ],
            'isActiveStaff' => [
                'field' => 'is active staff',
                'required' => false,
                'checkbox' => true,
            ],
            'hasCompletedIntake' => [
                'field' => 'has completed intake',
                'required' => false,
                'checkbox' => true,
            ],
        ];

        foreach ($fields as $fieldName => $field) {
            if ($field['required'] && !$request->request->get($fieldName)) {
                $missingFields[] = ucfirst($field['field']) . ' was not set.';
            }
        }
        if (count($missingFields) > 0) {
            $returnJson['message'] = implode(' ', $missingFields);

            return $this->json($returnJson);
        }


        $staffId = $request->request->get('staff_id');
        if ($staffId) {
            $staff = $entityManager->getRepository(Staff::class)->find($staffId);
            $returnJson['staff_id'] = $staff->getId();
        } else {
            $staff = new Staff();
            $entityManager->persist($staff);
        }

        $changes = [];
        foreach ($fields as $fieldName => $field) {
            $getFunction = 'get' . ucfirst($fieldName);
            $setFunction = 'set' . ucfirst($fieldName);
            $value = $request->request->get($fieldName);
            if (array_key_exists('birthday', $field) && $field['birthday']) {
                if (!$fieldName == 'birthDate') {
                    continue;
                }
                $birthYear = $request->request->get('birthYear');
                $tmpField = "$value/$birthYear";
                if (!strtotime($tmpField)) {
                    $tmpField = str_replace('-', '/', $tmpField);
                }
                try {
                    $newDate = new \DateTime($tmpField);
                    $oldDate = $staff->getDateOfBirth();
                    if (!$oldDate) {
                        $changes[$fieldName] =
                            "{$field['field']} changed: N/A => {$newDate->format('m/d/y')}";
                    } elseif ($oldDate->format('m/d/y') != $newDate->format('m/d/y')) {
                        $changes[$fieldName] =
                            "{$field['field']} changed: "
                            . "{$oldDate->format('m/d/y')} => "
                            . "{$newDate->format('m/d/y')}";
                    }
                    $staff->setDateOfBirth($newDate);
                }
                catch (\Exception $e) {}

                continue;
            }
            if (array_key_exists('checkbox', $field) && $field['checkbox']) {
                $value = ($value == 'on') ? true : false;
                $valueString = $value ? 'Yes' : 'No';
                if ($fieldName == 'isActiveStaff') {
                    $oldValueString = $staff->isActive() ? 'Yes' : 'No';
                    if ($staff->isActive() != $value) {
                        $changes[$fieldName] = "{$field['field']} changed: $oldValueString => $valueString";
                    }
                    $staff->setActive($value);
                } elseif ($fieldName == 'hasCompletedIntake') {
                    $oldValueString = $staff->isCompletedIntake() ? 'Yes' : 'No';
                    if ($staff->isCompletedIntake() != $value) {
                        $changes[$fieldName] = "{$field['field']} changed: $oldValueString => $valueString";
                    }
                    $staff->setCompletedIntake($value);
                }
                continue;
            }
            $oldValue = $staff->$getFunction($value);

            if ($oldValue != $value) {
                $changes[$fieldName] = "{$field['field']} changed: $oldValue => $value";
            }
            $staff->$setFunction($value);
        }

        $comments = $request->request->get('comments');

        $returnJson['success'] = true;
        $returnJson['message'] = 'No changes detected.';

        if (count($changes) > 0 || $comments) {
            $staffHistory = new StaffHistory();
            $staffHistory->setStaff($staff);
            $changeText = implode('<br>', $changes);
            if ($changeText && $comments) {
                $changeText .= '<br><br>';
            }
            if ($comments) {
                $changeText .= "Comments: $comments";
            }
            $staffHistory->setChangeText($changeText);
            $entityManager->persist($staffHistory);

            $entityManager->flush();

            $returnJson['staff_id'] = $staff->getId();
            $returnJson['message'] = 'Staff member successfully saved.';
        }

        return $this->json($returnJson);
    }

    /**
     * @Route("/org/staff/view/", name="org_staff_view_noId")
     * @Route("/org/staff/view/{id}", name="org_staff_view")
     * @Security("has_role('ROLE_USER')")
     *
     * @param string $id
     * @return Response
     */
    public function viewStaff($id)
    {
        $parameters = [];

        $staff = $this->getDoctrine()->getRepository(Staff::class)->find($id);

        if (!$staff) {
            $this->createNotFoundException('Invalid Staff Member');
        }

        $parameters['staff'] = $staff;

        /** @var StaffDepartment[] $departments */
        $departments = $staff->getDepartments();
        $primaryDepartment = null;
        $otherDepartments = [];
        foreach ($departments as $department) {
            if ($department->isPrimary()) {
                $primaryDepartment = $department;
            } else {
                $departmentName = $department->getDepartment()->getName();
                if ($department->getPosition()) {
                    $departmentName .= " ({$department->getPosition()})";
                }
                $otherDepartments[] = $departmentName;
            }
        }

        /** @var StaffDepartment[] $parameters['departments'] */
        $parameters['departments'] = array_merge([$primaryDepartment], $otherDepartments);

        return $this->render('organization/staffView.html.twig', $parameters);
    }
}
