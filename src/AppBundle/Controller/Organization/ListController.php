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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends Controller
{
    /**
     * @Route("/staff", name="staff_list")
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function index()
    {
        return $this->render('organization/staffList.html.twig');
    }

    /**
     * @Route("/ajax/staff-list", name="ajax_staff_list")
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     * @return Response
     */
    public function ajaxStaffList(Request $request)
    {
        $findAll = false;
        if ($request->query->has('showInactive')) {
            $showInactive = trim($request->query->get('showInactive'));
            $findAll = $showInactive == 'true';
        }

        if ($findAll) {
            $staffList = $this->getDoctrine()
                ->getRepository(Staff::class)
                ->findAll();
        } else {
            $staffList = $this->getDoctrine()
                ->getRepository(Staff::class)
                ->findAllActive();
        }

        $returnArray = ['data' => []];
        foreach ($staffList as $staff) {
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

            $returnArray['data'][] = [
                'id' => $staff->getId(),
                'first_name' => $staff->getFirstName(),
                'last_name' => $staff->getLastName(),
                'nickname' => $staff->getNickName(),
                'department' => [
                    'id' => $primaryDepartment ?
                        $primaryDepartment->getDepartment()->getId() : '',
                    'name' => $primaryDepartment ?
                        $primaryDepartment->getDepartment()->getName() : 'No Primary Department',
                ],
                'position' => $primaryDepartment ?
                    $primaryDepartment->getPosition() : '',
                'description' => $staff->getDescription(),
                'official_email' => $staff->getOfficialEmail(),
                'personal_email' => $staff->getPersonalEmail(),
                'phone' => $staff->getPhoneNumber(),
                'dob' => $staff->getDateOfBirth()->format('F j Y'),
                'shirt' => "{$staff->getShirtType()} {$staff->getShirtSize()}",
                'other_departments' => implode(', ', $otherDepartments),
                'is_registered' => $staff->getActiveRegistration() ? true : false,
                'is_active' => $staff->isActive() ? true : false,
            ];
        }

        return $this->json($returnArray);
    }
}
