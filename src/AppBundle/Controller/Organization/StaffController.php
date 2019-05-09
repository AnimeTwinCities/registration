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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StaffController extends Controller
{
    /**
     * @Route("/org/staff/edit/", name="org_staff_edit_new")
     * @Route("/org/staff/edit/{id}", name="org_staff_edit")
     * @Security("has_role('ROLE_USER')")
     *
     * @param string $id
     * @return Response
     */
    public function editStaff($id = '')
    {
        return $this->json([$id]);
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
