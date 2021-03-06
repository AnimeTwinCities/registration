<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Controller\Utils;

use AppBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;

class YearChangeController extends Controller
{
    /**
     * @Route("/change/year/{eventYear}", name="changeYear")
     * @Security("has_role('ROLE_USER')")
     *
     * @param String $eventYear Event to set as selected year
     *
     * @return RedirectResponse
     */
    public function changeYear($eventYear) {
        $event = $this->getDoctrine()->getRepository(Event::class)->getEventFromYear($eventYear);

        if ($event) {
            $session = new Session();
            $session->set('selectedEvent', $eventYear);
        }

        return $this->redirectToRoute('listRegistrations');
    }
}
