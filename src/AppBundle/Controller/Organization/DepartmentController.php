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

use AppBundle\Entity\Organization\Department;
use AppBundle\Entity\Organization\StaffDepartment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DepartmentController extends Controller
{
    /**
     * @Route("/org/department/view/", name="org_department_view_noId")
     * @Route("/org/department/view/{id}", name="org_department_view")
     * @Security("has_role('ROLE_USER')")
     *
     * @param string $id
     * @return Response
     */
    public function viewDepartment($id)
    {
        $department = $this->getDoctrine()
            ->getRepository(Department::class)
            ->find($id);

        if (!$department) {
            $this->createNotFoundException('Invalid Department');
        }

        $staffDepartments = $department->getStaffDepartments();
        $head = null;
        $subHeads = [];
        $others = [];
        foreach ($staffDepartments as $staffDepartment) {
            /** @var StaffDepartment $staffDepartment */
            if ($staffDepartment->isHead()) {
                $head = $staffDepartment;
            } elseif ($staffDepartment->isSubHead()) {
                $subHeads[] = $staffDepartment;
            } else {
                $others[] = $staffDepartment;
            }
        }

        $members = [];
        if ($head) {
            $members[] = $head;
        }
        $members = array_merge($members, $subHeads, $others);

        $childDepartments = $department->getChildDepartments();

        $parameters = [
            'department' => $department,
            'childDepartments' => $childDepartments,
            'members' => $members,
        ];

        return $this->render('organization/department.html.twig', $parameters);
    }
}