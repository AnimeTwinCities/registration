<?php
/**
 * Copyright (c) 2019. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

declare(strict_types=1);


namespace AppBundle\Controller;

use AppBundle\Entity\Organization\Staff;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StaffListController extends Controller
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
     * @return Response
     */
    public function ajaxStaffList()
    {
        $staffList = $this->getDoctrine()
            ->getRepository(Staff::class)
            ->findAll();

        $returnArray = ['data' => []];
        foreach ($staffList as $staff) {
            $departments = $staff->getDepartments();
            $primaryDepartment = null;
            foreach ($departments as $department) {
                if ($department->isPrimary()) {
                    $primaryDepartment = $department;
                }
            }

            $returnArray['data'][] = [
                'id' => $staff->getId(),
                'first_name' => $staff->getFirstName(),
                'last_name' => $staff->getLastName(),
                'nickname' => $staff->getNickName(),
                'department' => $primaryDepartment ?
                    $primaryDepartment->getDepartment()->getName() : 'No Primary Department',
                'description' => $staff->getDescription(),
                'official_email' => $staff->getOfficialEmail(),
            ];
        }

        return $this->json($returnArray);
    }
}
