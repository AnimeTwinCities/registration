<?php

namespace AppBundle\Controller\Utils;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class YearChangeController extends Controller
{
    /**
     * @Route("/changeyear/{eventYear}")
     * @Security("has_role('ROLE_USER')")
     *
     * @param String $eventYear Event to set as selected year
     */
    public function changeYear($eventYear) {
        $event = $this->get('repository_event')->getEventFromYear($eventYear);

        if ($event) {
            $session = new Session();
            $session->set('selectedEvent', $eventYear);
        }

        return $this->redirectToRoute('app_manage_manage_listregistrationspage');
    }
}