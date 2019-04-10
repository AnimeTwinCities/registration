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
        $returnArray = [
            "data" => [
                [
                    "Tiger Nixon",
                    "System Architect",
                    "Edinburgh",
                    "5421",
                    "2011/04/25",
                    "$320,800"
                ],
                [
                    "Garrett Winters",
                    "Accountant",
                    "Tokyo",
                    "8422",
                    "2011/07/25",
                    "$170,750"
                ],
                [
                    "Ashton Cox",
                    "Junior Technical Author",
                    "San Francisco",
                    "1562",
                    "2009/01/12",
                    "$86,000"
                ],
            ]
        ];
        return $this->json($returnArray);
    }
}
