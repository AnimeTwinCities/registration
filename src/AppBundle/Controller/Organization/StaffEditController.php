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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StaffEditController extends Controller
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
}
